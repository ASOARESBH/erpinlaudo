-- Script SQL para adicionar campo webhook_secret
-- ERP INLAUDO - Versão 7.3

-- Adicionar campo webhook_secret na tabela integracoes_pagamento
ALTER TABLE integracoes_pagamento 
ADD COLUMN IF NOT EXISTS webhook_secret VARCHAR(500) NULL AFTER mp_webhook_url;

-- Adicionar campo status_processamento na tabela webhooks_pagamento
ALTER TABLE webhooks_pagamento 
ADD COLUMN IF NOT EXISTS status_processamento VARCHAR(50) NULL AFTER processado;

-- Criar tabela configuracoes_gateway se não existir (compatibilidade)
CREATE TABLE IF NOT EXISTS configuracoes_gateway (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gateway VARCHAR(50) NOT NULL,
    access_token TEXT,
    public_key TEXT,
    webhook_url VARCHAR(500),
    webhook_secret VARCHAR(500),
    ambiente VARCHAR(20) DEFAULT 'producao',
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_gateway (gateway)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrar dados de integracoes_pagamento para configuracoes_gateway
INSERT INTO configuracoes_gateway (gateway, access_token, public_key, webhook_url, webhook_secret, ambiente, ativo)
SELECT 
    'mercadopago',
    mp_access_token,
    mp_public_key,
    mp_webhook_url,
    webhook_secret,
    ambiente,
    ativo
FROM integracoes_pagamento
WHERE gateway = 'mercadopago'
ON DUPLICATE KEY UPDATE
    access_token = VALUES(access_token),
    public_key = VALUES(public_key),
    webhook_url = VALUES(webhook_url),
    webhook_secret = VALUES(webhook_secret),
    ambiente = VALUES(ambiente),
    ativo = VALUES(ativo);

-- Criar pasta de logs se não existir (via PHP)
-- Este comando será executado via PHP no webhook

SELECT 'Script executado com sucesso!' as resultado;
