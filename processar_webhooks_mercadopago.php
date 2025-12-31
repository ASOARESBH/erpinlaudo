<?php
/**
 * Processador de Webhooks - Mercado Pago
 * 
 * Script desacoplado para conciliação financeira
 * Processa webhooks recebidos e atualiza contas a receber
 * 
 * Execução: Via CRON (recomendado: a cada 1-5 minutos)
 * Exemplo CRON: * /1 * * * * /usr/bin/php /caminho/processar_webhooks_mercadopago.php
 * 
 * @author ERP INLAUDO
 * @version 1.0
 */

// Desabilitar output de erros para execução via CRON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Definir timezone
date_default_timezone_set('America/Sao_Paulo');

// ===============================
// CONFIGURAÇÃO
// ===============================
define('BATCH_SIZE', 20); // Processar 20 webhooks por vez
define('LOG_FILE', __DIR__ . '/logs/processar_webhooks.log');
define('MAX_RETRIES', 3); // Máximo de tentativas antes de marcar como erro

// ===============================
// FUNÇÕES AUXILIARES
// ===============================

/**
 * Registrar log de processamento
 */
function logProcessamento($mensagem, $nivel = 'INFO') {
    $timestamp = date('Y-m-d H:i:s');
    $logLine = "[{$timestamp}] [{$nivel}] {$mensagem}" . PHP_EOL;
    
    // Criar pasta de logs se não existir
    $logDir = dirname(LOG_FILE);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    @file_put_contents(LOG_FILE, $logLine, FILE_APPEND);
    
    // Também enviar para syslog em produção
    if ($nivel === 'ERROR') {
        error_log($mensagem);
    }
}

/**
 * Obter conexão com banco de dados
 */
function getConnection() {
    try {
        require_once __DIR__ . '/config.php';
        return getConnection();
    } catch (Exception $e) {
        logProcessamento('ERRO ao conectar ao banco: ' . $e->getMessage(), 'ERROR');
        throw $e;
    }
}

/**
 * Obter access token do Mercado Pago
 */
function getAccessToken($conn) {
    try {
        $stmt = $conn->prepare("
            SELECT access_token
            FROM configuracoes_gateway
            WHERE gateway = 'mercadopago'
            AND ativo = 1
            LIMIT 1
        ");
        
        $stmt->execute();
        $config = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$config || empty($config['access_token'])) {
            throw new Exception('Access token não configurado');
        }
        
        return $config['access_token'];
        
    } catch (Exception $e) {
        logProcessamento('ERRO ao obter access token: ' . $e->getMessage(), 'ERROR');
        throw $e;
    }
}

/**
 * Consultar pagamento na API do Mercado Pago
 */
function consultarPagamentoMP($transactionId, $accessToken) {
    $url = "https://api.mercadopago.com/v1/payments/{$transactionId}";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($curlError) {
        throw new Exception("Erro cURL: {$curlError}");
    }
    
    if ($httpCode !== 200) {
        throw new Exception("API retornou HTTP {$httpCode}: {$response}");
    }
    
    $payment = json_decode($response, true);
    
    if (!$payment) {
        throw new Exception("Resposta inválida da API");
    }
    
    return $payment;
}

/**
 * Extrair ID da conta do external_reference
 */
function extrairContaId($externalReference) {
    if (empty($externalReference)) {
        return null;
    }
    
    // Formato esperado: conta_123
    if (preg_match('/conta_(\d+)/', $externalReference, $matches)) {
        return (int)$matches[1];
    }
    
    return null;
}

/**
 * Atualizar conta a receber como paga
 */
function marcarContaPaga($conn, $contaId, $transactionId) {
    try {
        // Verificar se conta existe e não está paga
        $stmt = $conn->prepare("
            SELECT id, status
            FROM contas_receber
            WHERE id = ?
            LIMIT 1
        ");
        
        $stmt->execute([$contaId]);
        $conta = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$conta) {
            throw new Exception("Conta {$contaId} não encontrada");
        }
        
        // Idempotência: se já está paga, não fazer nada
        if ($conta['status'] === 'pago') {
            logProcessamento("Conta {$contaId} já estava marcada como paga (idempotência)", 'INFO');
            return true;
        }
        
        // Atualizar status
        $stmt = $conn->prepare("
            UPDATE contas_receber
            SET status = 'pago',
                data_pagamento = NOW(),
                gateway = 'mercadopago',
                payment_id = ?
            WHERE id = ?
            AND status <> 'pago'
        ");
        
        $stmt->execute([$transactionId, $contaId]);
        $rowsAffected = $stmt->rowCount();
        
        if ($rowsAffected > 0) {
            logProcessamento("Conta {$contaId} marcada como PAGA (transaction: {$transactionId})", 'SUCCESS');
            return true;
        } else {
            logProcessamento("Conta {$contaId} não foi atualizada (possível concorrência)", 'WARNING');
            return true; // Não é erro, apenas já foi processada
        }
        
    } catch (Exception $e) {
        logProcessamento("ERRO ao marcar conta {$contaId} como paga: " . $e->getMessage(), 'ERROR');
        throw $e;
    }
}

/**
 * Marcar webhook como processado
 */
function marcarWebhookProcessado($conn, $webhookId, $sucesso = true, $erro = null) {
    try {
        if ($sucesso) {
            $stmt = $conn->prepare("
                UPDATE webhooks_pagamento
                SET processado = 1,
                    data_processamento = NOW(),
                    erro = NULL
                WHERE id = ?
            ");
            
            $stmt->execute([$webhookId]);
            
        } else {
            $stmt = $conn->prepare("
                UPDATE webhooks_pagamento
                SET processado = 0,
                    data_processamento = NOW(),
                    erro = ?
                WHERE id = ?
            ");
            
            $stmt->execute([$erro, $webhookId]);
        }
        
    } catch (Exception $e) {
        logProcessamento("ERRO ao marcar webhook {$webhookId}: " . $e->getMessage(), 'ERROR');
    }
}

/**
 * Processar um webhook individual
 */
function processarWebhook($conn, $webhook, $accessToken) {
    $webhookId = $webhook['id'];
    $transactionId = $webhook['transaction_id'];
    
    logProcessamento("Processando webhook #{$webhookId} (transaction: {$transactionId})", 'INFO');
    
    try {
        // 1. Consultar pagamento na API do Mercado Pago
        $payment = consultarPagamentoMP($transactionId, $accessToken);
        
        $status = $payment['status'] ?? '';
        $externalReference = $payment['external_reference'] ?? '';
        
        logProcessamento("Transaction {$transactionId}: status={$status}, external_ref={$externalReference}", 'INFO');
        
        // 2. Processar apenas pagamentos aprovados
        if ($status !== 'approved') {
            logProcessamento("Transaction {$transactionId} não está aprovada (status: {$status}), marcando como processado", 'INFO');
            marcarWebhookProcessado($conn, $webhookId, true);
            return;
        }
        
        // 3. Extrair ID da conta
        $contaId = extrairContaId($externalReference);
        
        if (!$contaId) {
            throw new Exception("Não foi possível extrair conta_id do external_reference: {$externalReference}");
        }
        
        // 4. Marcar conta como paga
        marcarContaPaga($conn, $contaId, $transactionId);
        
        // 5. Marcar webhook como processado com sucesso
        marcarWebhookProcessado($conn, $webhookId, true);
        
        logProcessamento("Webhook #{$webhookId} processado com SUCESSO", 'SUCCESS');
        
    } catch (Exception $e) {
        $erroMsg = $e->getMessage();
        logProcessamento("ERRO ao processar webhook #{$webhookId}: {$erroMsg}", 'ERROR');
        
        // Marcar webhook com erro
        marcarWebhookProcessado($conn, $webhookId, false, $erroMsg);
    }
}

// ===============================
// SCRIPT PRINCIPAL
// ===============================

try {
    logProcessamento('========== INICIANDO PROCESSAMENTO DE WEBHOOKS ==========', 'INFO');
    
    // 1. Conectar ao banco
    $conn = getConnection();
    logProcessamento('Conexão com banco estabelecida', 'INFO');
    
    // 2. Obter access token
    $accessToken = getAccessToken($conn);
    logProcessamento('Access token obtido', 'INFO');
    
    // 3. Buscar webhooks pendentes
    $stmt = $conn->prepare("
        SELECT id, transaction_id, payload, data_recebimento
        FROM webhooks_pagamento
        WHERE gateway = 'mercadopago'
        AND processado = 0
        AND transaction_id IS NOT NULL
        AND transaction_id <> ''
        ORDER BY data_recebimento ASC
        LIMIT ?
    ");
    
    $stmt->bindValue(1, BATCH_SIZE, PDO::PARAM_INT);
    $stmt->execute();
    
    $webhooks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $total = count($webhooks);
    
    logProcessamento("Encontrados {$total} webhook(s) pendente(s)", 'INFO');
    
    if ($total === 0) {
        logProcessamento('Nenhum webhook para processar', 'INFO');
        logProcessamento('========== PROCESSAMENTO FINALIZADO ==========', 'INFO');
        exit(0);
    }
    
    // 4. Processar cada webhook
    $sucessos = 0;
    $erros = 0;
    
    foreach ($webhooks as $webhook) {
        try {
            processarWebhook($conn, $webhook, $accessToken);
            $sucessos++;
        } catch (Exception $e) {
            $erros++;
            logProcessamento("Erro não tratado no webhook #{$webhook['id']}: " . $e->getMessage(), 'ERROR');
        }
        
        // Pequeno delay para não sobrecarregar a API
        usleep(100000); // 100ms
    }
    
    // 5. Resumo final
    logProcessamento("Processamento concluído: {$sucessos} sucesso(s), {$erros} erro(s)", 'INFO');
    logProcessamento('========== PROCESSAMENTO FINALIZADO ==========', 'INFO');
    
    exit(0);
    
} catch (Exception $e) {
    logProcessamento('ERRO CRÍTICO: ' . $e->getMessage(), 'ERROR');
    logProcessamento('========== PROCESSAMENTO ABORTADO ==========', 'ERROR');
    exit(1);
}
?>
