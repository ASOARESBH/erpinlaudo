-- ========================================
-- Atualização do Banco de Dados
-- Integração Mercado Pago + NF
-- Versão 7.0
-- ========================================

-- 1. Adicionar campos de forma de pagamento em contratos
ALTER TABLE contratos 
ADD COLUMN IF NOT EXISTS gateway_pagamento VARCHAR(50) DEFAULT 'cora' COMMENT 'Gateway de pagamento: cora, mercadopago',
ADD COLUMN IF NOT EXISTS link_pagamento TEXT COMMENT 'Link de pagamento gerado (Mercado Pago)',
ADD COLUMN IF NOT EXISTS payment_id VARCHAR(255) COMMENT 'ID do pagamento no gateway',
ADD COLUMN IF NOT EXISTS status_pagamento VARCHAR(50) DEFAULT 'pendente' COMMENT 'Status: pendente, pago, cancelado, expirado';

-- 2. Adicionar campos de NF em contas a receber
ALTER TABLE contas_receber 
ADD COLUMN IF NOT EXISTS nf_numero VARCHAR(100) COMMENT 'Número da Nota Fiscal',
ADD COLUMN IF NOT EXISTS nf_arquivo VARCHAR(255) COMMENT 'Caminho do arquivo da NF',
ADD COLUMN IF NOT EXISTS nf_data_emissao DATE COMMENT 'Data de emissão da NF',
ADD COLUMN IF NOT EXISTS nf_valor DECIMAL(10,2) COMMENT 'Valor da NF';

-- 3. Criar tabela de integrações de pagamento
CREATE TABLE IF NOT EXISTS integracoes_pagamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gateway VARCHAR(50) NOT NULL COMMENT 'cora, mercadopago, stripe',
    ativo TINYINT(1) DEFAULT 0,
    
    -- Mercado Pago
    mp_public_key TEXT COMMENT 'Public Key do Mercado Pago',
    mp_access_token TEXT COMMENT 'Access Token do Mercado Pago',
    mp_webhook_url VARCHAR(255) COMMENT 'URL do webhook',
    
    -- CORA (já existe em integracoes, mas mantemos referência)
    cora_client_id VARCHAR(255),
    cora_certificado TEXT,
    
    -- Stripe (já existe em integracoes)
    stripe_secret_key TEXT,
    stripe_publishable_key TEXT,
    
    -- Configurações gerais
    ambiente VARCHAR(20) DEFAULT 'producao' COMMENT 'teste ou producao',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_gateway (gateway)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Criar tabela de transações de pagamento
CREATE TABLE IF NOT EXISTS transacoes_pagamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT,
    conta_receber_id INT,
    gateway VARCHAR(50) NOT NULL COMMENT 'cora, mercadopago, stripe',
    
    -- Identificadores do gateway
    transaction_id VARCHAR(255) COMMENT 'ID da transação no gateway',
    payment_id VARCHAR(255) COMMENT 'ID do pagamento no gateway',
    
    -- Dados da transação
    valor DECIMAL(10,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, approved, rejected, cancelled, refunded',
    metodo_pagamento VARCHAR(50) COMMENT 'boleto, pix, credit_card, debit_card',
    
    -- Links e dados adicionais
    payment_url TEXT COMMENT 'URL de pagamento',
    boleto_url TEXT COMMENT 'URL do boleto',
    qr_code TEXT COMMENT 'QR Code Pix',
    qr_code_base64 LONGTEXT COMMENT 'QR Code em base64',
    
    -- Dados do pagador
    pagador_nome VARCHAR(255),
    pagador_email VARCHAR(255),
    pagador_documento VARCHAR(20),
    
    -- Datas
    data_vencimento DATE,
    data_pagamento DATETIME,
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Dados brutos da resposta
    response_json LONGTEXT COMMENT 'JSON completo da resposta do gateway',
    
    -- Índices
    INDEX idx_contrato (contrato_id),
    INDEX idx_conta_receber (conta_receber_id),
    INDEX idx_transaction (transaction_id),
    INDEX idx_status (status),
    INDEX idx_gateway (gateway),
    
    -- Foreign keys
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE SET NULL,
    FOREIGN KEY (conta_receber_id) REFERENCES contas_receber(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Criar tabela de webhooks recebidos
CREATE TABLE IF NOT EXISTS webhooks_pagamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gateway VARCHAR(50) NOT NULL,
    evento VARCHAR(100) COMMENT 'Tipo de evento: payment.created, payment.updated, etc',
    transaction_id VARCHAR(255),
    
    -- Dados do webhook
    payload LONGTEXT COMMENT 'JSON completo do webhook',
    headers TEXT COMMENT 'Headers HTTP do webhook',
    
    -- Processamento
    processado TINYINT(1) DEFAULT 0,
    data_processamento DATETIME,
    erro TEXT COMMENT 'Mensagem de erro se houver',
    
    -- Auditoria
    ip_origem VARCHAR(50),
    data_recebimento DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_gateway (gateway),
    INDEX idx_transaction (transaction_id),
    INDEX idx_processado (processado),
    INDEX idx_data (data_recebimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Criar diretório para upload de NFs
-- (Será criado via PHP)

-- 7. Inserir configuração padrão do Mercado Pago
INSERT INTO integracoes_pagamento (gateway, ativo, ambiente) 
VALUES ('mercadopago', 0, 'producao')
ON DUPLICATE KEY UPDATE gateway = gateway;

-- 8. Inserir configuração padrão do CORA
INSERT INTO integracoes_pagamento (gateway, ativo, ambiente) 
VALUES ('cora', 0, 'producao')
ON DUPLICATE KEY UPDATE gateway = gateway;

-- 9. Adicionar índices para performance
ALTER TABLE contratos ADD INDEX IF NOT EXISTS idx_gateway (gateway_pagamento);
ALTER TABLE contratos ADD INDEX IF NOT EXISTS idx_status_pag (status_pagamento);
ALTER TABLE contas_receber ADD INDEX IF NOT EXISTS idx_nf_numero (nf_numero);

-- ========================================
-- Atualização concluída!
-- ========================================

SELECT 'Banco de dados atualizado com sucesso!' AS status;
