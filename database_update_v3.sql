-- Atualização do Banco de Dados - ERP INLAUDO V3
-- Novas funcionalidades: Logs de Integração e Faturamento Stripe

-- Tabela de Logs de Integração
CREATE TABLE IF NOT EXISTS logs_integracao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo VARCHAR(50) NOT NULL COMMENT 'stripe, cora, api_cnpj, etc',
    acao VARCHAR(100) NOT NULL COMMENT 'gerar_boleto, consultar, cancelar, etc',
    status ENUM('sucesso', 'erro', 'aviso') NOT NULL,
    mensagem TEXT,
    request_data TEXT COMMENT 'Dados enviados para API (JSON)',
    response_data TEXT COMMENT 'Resposta da API (JSON)',
    codigo_http INT COMMENT 'Código HTTP da resposta',
    tempo_resposta DECIMAL(10,3) COMMENT 'Tempo de resposta em segundos',
    ip_origem VARCHAR(45),
    usuario_id INT COMMENT 'ID do usuário que executou (futuro)',
    referencia_id INT COMMENT 'ID da entidade relacionada (conta, boleto, etc)',
    referencia_tipo VARCHAR(50) COMMENT 'Tipo da entidade (conta_receber, boleto, etc)',
    data_log TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo),
    INDEX idx_status (status),
    INDEX idx_data (data_log),
    INDEX idx_referencia (referencia_tipo, referencia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Faturamento (Invoices Stripe)
CREATE TABLE IF NOT EXISTS faturamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conta_receber_id INT NOT NULL,
    cliente_id INT NOT NULL,
    stripe_invoice_id VARCHAR(255) UNIQUE COMMENT 'ID da invoice no Stripe',
    stripe_customer_id VARCHAR(255) COMMENT 'ID do customer no Stripe',
    numero_fatura VARCHAR(100) COMMENT 'Número da fatura',
    descricao TEXT,
    valor_total DECIMAL(10, 2) NOT NULL,
    valor_pago DECIMAL(10, 2) DEFAULT 0,
    status ENUM('draft', 'open', 'paid', 'uncollectible', 'void') DEFAULT 'draft',
    data_emissao DATE,
    data_vencimento DATE,
    data_pagamento DATE,
    url_fatura VARCHAR(500) COMMENT 'URL da fatura no Stripe',
    url_pdf VARCHAR(500) COMMENT 'URL do PDF da fatura',
    hosted_invoice_url VARCHAR(500) COMMENT 'URL pública da fatura',
    payment_intent_id VARCHAR(255) COMMENT 'ID do payment intent',
    boleto_url VARCHAR(500) COMMENT 'URL do boleto se forma de pagamento for boleto',
    forma_pagamento VARCHAR(50),
    observacoes TEXT,
    resposta_api TEXT COMMENT 'JSON completo da resposta Stripe',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_receber_id) REFERENCES contas_receber(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_stripe_invoice (stripe_invoice_id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_status (status),
    INDEX idx_data_vencimento (data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar campo para armazenar ID do customer Stripe em clientes
ALTER TABLE clientes 
ADD COLUMN stripe_customer_id VARCHAR(255) NULL AFTER email,
ADD INDEX idx_stripe_customer (stripe_customer_id);

-- Adicionar campo para vincular fatura em contas_receber
ALTER TABLE contas_receber 
ADD COLUMN fatura_id INT NULL AFTER boleto_id,
ADD FOREIGN KEY (fatura_id) REFERENCES faturamento(id) ON DELETE SET NULL;
