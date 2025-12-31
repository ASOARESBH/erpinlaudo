<?php
/**
 * Biblioteca de Integração com Mercado Pago
 * ERP INLAUDO - Versão 7.0
 */

require_once 'config.php';
require_once 'lib_logs.php';

class MercadoPago {
    private $accessToken;
    private $publicKey;
    private $ambiente; // 'teste' ou 'producao'
    private $baseUrl;
    
    public function __construct() {
        $this->carregarConfiguracao();
    }
    
    /**
     * Carregar configuração do banco de dados
     */
    private function carregarConfiguracao() {
        try {
            $conn = getConnection();
            $stmt = $conn->prepare("SELECT * FROM integracoes_pagamento WHERE gateway = 'mercadopago' LIMIT 1");
            $stmt->execute();
            $config = $stmt->fetch();
            
            if ($config) {
                $this->accessToken = $config['mp_access_token'] ?? '';
                $this->publicKey = $config['mp_public_key'] ?? '';
                $this->ambiente = $config['ambiente'] ?? 'producao';
            }
            
            // URL base da API
            $this->baseUrl = 'https://api.mercadopago.com';
            
        } catch (Exception $e) {
            registrarLog('mercadopago', 'erro', 'Erro ao carregar configuração', [
                'erro' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Criar preferência de pagamento
     */
    public function criarPreferencia($dados) {
        try {
            $url = $this->baseUrl . '/checkout/preferences';
            
            // Preparar dados da preferência
            $preferencia = [
                'items' => [
                    [
                        'title' => $dados['titulo'] ?? 'Pagamento',
                        'description' => $dados['descricao'] ?? '',
                        'quantity' => 1,
                        'currency_id' => 'BRL',
                        'unit_price' => (float)$dados['valor']
                    ]
                ],
                'payer' => [
                    'name' => $dados['pagador_nome'] ?? '',
                    'email' => $dados['pagador_email'] ?? '',
                    'identification' => [
                        'type' => strlen(preg_replace('/[^0-9]/', '', $dados['pagador_documento'] ?? '')) == 11 ? 'CPF' : 'CNPJ',
                        'number' => preg_replace('/[^0-9]/', '', $dados['pagador_documento'] ?? '')
                    ]
                ],
                'back_urls' => [
                    'success' => $dados['url_sucesso'] ?? '',
                    'failure' => $dados['url_falha'] ?? '',
                    'pending' => $dados['url_pendente'] ?? ''
                ],
                'auto_return' => 'approved',
                'external_reference' => $dados['referencia_externa'] ?? '',
                'notification_url' => $dados['webhook_url'] ?? '',
                'expires' => true,
                'expiration_date_from' => date('c'),
                'expiration_date_to' => date('c', strtotime('+30 days'))
            ];
            
            // Se tiver data de vencimento específica
            if (isset($dados['data_vencimento'])) {
                $preferencia['date_of_expiration'] = date('c', strtotime($dados['data_vencimento'] . ' 23:59:59'));
            }
            
            // Fazer requisição
            $response = $this->fazerRequisicao('POST', $url, $preferencia);
            
            // Registrar log
            registrarLog('mercadopago', 'info', 'Preferência criada', [
                'preference_id' => $response['id'] ?? null,
                'init_point' => $response['init_point'] ?? null
            ]);
            
            return [
                'sucesso' => true,
                'preference_id' => $response['id'] ?? null,
                'init_point' => $response['init_point'] ?? null,
                'sandbox_init_point' => $response['sandbox_init_point'] ?? null,
                'response' => $response
            ];
            
        } catch (Exception $e) {
            registrarLog('mercadopago', 'erro', 'Erro ao criar preferência', [
                'erro' => $e->getMessage(),
                'dados' => $dados
            ]);
            
            return [
                'sucesso' => false,
                'erro' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Consultar pagamento
     */
    public function consultarPagamento($paymentId) {
        try {
            $url = $this->baseUrl . '/v1/payments/' . $paymentId;
            $response = $this->fazerRequisicao('GET', $url);
            
            return [
                'sucesso' => true,
                'pagamento' => $response
            ];
            
        } catch (Exception $e) {
            registrarLog('mercadopago', 'erro', 'Erro ao consultar pagamento', [
                'payment_id' => $paymentId,
                'erro' => $e->getMessage()
            ]);
            
            return [
                'sucesso' => false,
                'erro' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Processar webhook
     */
    public function processarWebhook($dados) {
        try {
            // Registrar webhook recebido
            $conn = getConnection();
            $stmt = $conn->prepare("
                INSERT INTO webhooks_pagamento (gateway, evento, transaction_id, payload, ip_origem)
                VALUES ('mercadopago', ?, ?, ?, ?)
            ");
            $stmt->execute([
                $dados['type'] ?? 'unknown',
                $dados['data']['id'] ?? null,
                json_encode($dados),
                $_SERVER['REMOTE_ADDR'] ?? ''
            ]);
            $webhookId = $conn->lastInsertId();
            
            // Se for notificação de pagamento
            if (isset($dados['type']) && $dados['type'] == 'payment') {
                $paymentId = $dados['data']['id'] ?? null;
                
                if ($paymentId) {
                    // Consultar detalhes do pagamento
                    $resultado = $this->consultarPagamento($paymentId);
                    
                    if ($resultado['sucesso']) {
                        $pagamento = $resultado['pagamento'];
                        
                        // Atualizar transação no banco
                        $this->atualizarTransacao($pagamento);
                        
                        // Marcar webhook como processado
                        $stmtUpdate = $conn->prepare("
                            UPDATE webhooks_pagamento 
                            SET processado = 1, data_processamento = NOW() 
                            WHERE id = ?
                        ");
                        $stmtUpdate->execute([$webhookId]);
                        
                        return ['sucesso' => true, 'mensagem' => 'Webhook processado'];
                    }
                }
            }
            
            return ['sucesso' => true, 'mensagem' => 'Webhook recebido'];
            
        } catch (Exception $e) {
            registrarLog('mercadopago', 'erro', 'Erro ao processar webhook', [
                'erro' => $e->getMessage(),
                'dados' => $dados
            ]);
            
            return ['sucesso' => false, 'erro' => $e->getMessage()];
        }
    }
    
    /**
     * Atualizar transação no banco
     */
    private function atualizarTransacao($pagamento) {
        try {
            $conn = getConnection();
            
            // Buscar transação
            $stmt = $conn->prepare("
                SELECT * FROM transacoes_pagamento 
                WHERE payment_id = ? AND gateway = 'mercadopago'
            ");
            $stmt->execute([$pagamento['id']]);
            $transacao = $stmt->fetch();
            
            if ($transacao) {
                // Mapear status do Mercado Pago
                $statusMap = [
                    'approved' => 'approved',
                    'pending' => 'pending',
                    'in_process' => 'pending',
                    'rejected' => 'rejected',
                    'cancelled' => 'cancelled',
                    'refunded' => 'refunded'
                ];
                
                $novoStatus = $statusMap[$pagamento['status']] ?? 'pending';
                
                // Atualizar transação
                $stmtUpdate = $conn->prepare("
                    UPDATE transacoes_pagamento 
                    SET status = ?,
                        data_pagamento = ?,
                        response_json = ?,
                        data_atualizacao = NOW()
                    WHERE id = ?
                ");
                $stmtUpdate->execute([
                    $novoStatus,
                    $novoStatus == 'approved' ? date('Y-m-d H:i:s') : null,
                    json_encode($pagamento),
                    $transacao['id']
                ]);
                
                // Se foi aprovado, atualizar conta a receber
                if ($novoStatus == 'approved' && $transacao['conta_receber_id']) {
                    $stmtConta = $conn->prepare("
                        UPDATE contas_receber 
                        SET status = 'pago', data_pagamento = NOW()
                        WHERE id = ?
                    ");
                    $stmtConta->execute([$transacao['conta_receber_id']]);
                }
                
                // Se foi aprovado, atualizar contrato
                if ($novoStatus == 'approved' && $transacao['contrato_id']) {
                    $stmtContrato = $conn->prepare("
                        UPDATE contratos 
                        SET status_pagamento = 'pago'
                        WHERE id = ?
                    ");
                    $stmtContrato->execute([$transacao['contrato_id']]);
                }
                
                registrarLog('mercadopago', 'info', 'Transação atualizada', [
                    'transaction_id' => $transacao['id'],
                    'status' => $novoStatus
                ]);
            }
            
        } catch (Exception $e) {
            registrarLog('mercadopago', 'erro', 'Erro ao atualizar transação', [
                'erro' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Fazer requisição HTTP
     */
    private function fazerRequisicao($metodo, $url, $dados = null) {
        $ch = curl_init();
        
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        if ($metodo == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
        } elseif ($metodo == 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $erro = curl_error($ch);
        curl_close($ch);
        
        if ($erro) {
            throw new Exception('Erro na requisição: ' . $erro);
        }
        
        $responseData = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $mensagemErro = $responseData['message'] ?? 'Erro desconhecido';
            throw new Exception('Erro HTTP ' . $httpCode . ': ' . $mensagemErro);
        }
        
        return $responseData;
    }
    
    /**
     * Verificar se está configurado
     */
    public function estaConfigurado() {
        return !empty($this->accessToken);
    }
    
    /**
     * Obter Public Key
     */
    public function getPublicKey() {
        return $this->publicKey;
    }
}
