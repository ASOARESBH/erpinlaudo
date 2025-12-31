<?php
/**
 * Configuração de Integração - Mercado Pago
 * ERP INLAUDO
 */

$pageTitle = 'Integração Mercado Pago';
require_once 'header.php';
require_once 'config.php';

$mensagem = '';
$tipo = '';

function isValidUrl($url) {
    return filter_var($url, FILTER_VALIDATE_URL);
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getConnection();

        $publicKey      = trim($_POST['mp_public_key'] ?? '');
        $accessToken    = trim($_POST['mp_access_token'] ?? '');
        $webhookUrl     = trim($_POST['mp_webhook_url'] ?? '');
        $webhookSecret  = trim($_POST['webhook_secret'] ?? '');
        $ambiente       = ($_POST['ambiente'] === 'teste') ? 'teste' : 'producao';
        $ativo          = isset($_POST['ativo']) ? 1 : 0;

        // ===== VALIDAÇÕES =====
        if (!$publicKey || !$accessToken) {
            throw new Exception('Public Key e Access Token são obrigatórios.');
        }

        if (!isValidUrl($webhookUrl)) {
            throw new Exception('URL do webhook inválida.');
        }

        // ===== SALVA NA TABELA PRINCIPAL =====
        $stmt = $conn->prepare("
            INSERT INTO integracoes_pagamento
            (gateway, mp_public_key, mp_access_token, mp_webhook_url, webhook_secret, ambiente, ativo, data_criacao, data_atualizacao)
            VALUES ('mercadopago', ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                mp_public_key = VALUES(mp_public_key),
                mp_access_token = VALUES(mp_access_token),
                mp_webhook_url = VALUES(mp_webhook_url),
                webhook_secret = VALUES(webhook_secret),
                ambiente = VALUES(ambiente),
                ativo = VALUES(ativo),
                data_atualizacao = NOW()
        ");
        $stmt->execute([
            $publicKey,
            $accessToken,
            $webhookUrl,
            $webhookSecret,
            $ambiente,
            $ativo
        ]);

        // ===== SINCRONIZA COM configuracoes_gateway =====
        $stmt = $conn->prepare("
            INSERT INTO configuracoes_gateway
            (gateway, ativo, public_key, access_token, webhook_url, webhook_secret, ambiente, created_at, updated_at)
            VALUES ('mercadopago', ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                ativo = VALUES(ativo),
                public_key = VALUES(public_key),
                access_token = VALUES(access_token),
                webhook_url = VALUES(webhook_url),
                webhook_secret = VALUES(webhook_secret),
                ambiente = VALUES(ambiente),
                updated_at = NOW()
        ");
        $stmt->execute([
            $ativo,
            $publicKey,
            $accessToken,
            $webhookUrl,
            $webhookSecret,
            $ambiente
        ]);

        $mensagem = 'Configuração salva e sincronizada com sucesso!';
        $tipo = 'sucesso';

    } catch (Throwable $e) {
        $mensagem = 'Erro ao salvar configuração: ' . $e->getMessage();
        $tipo = 'erro';
    }
}

// ===== CARREGA CONFIG =====
try {
    $conn = getConnection();
    $stmt = $conn->prepare("SELECT * FROM integracoes_pagamento WHERE gateway = 'mercadopago'");
    $stmt->execute();
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $config = [];
}

// URL default do webhook
$webhookUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/webhook_mercadopago.php';
