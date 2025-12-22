-- Atualização do Banco de Dados para CORA API v2 com mTLS
-- Data: 2025-12-22

-- Adicionar campo config na tabela integracoes (se não existir)
ALTER TABLE integracoes 
ADD COLUMN IF NOT EXISTS config TEXT COMMENT 'Configurações adicionais em JSON';

-- Atualizar registro da integração CORA
UPDATE integracoes 
SET config = JSON_OBJECT(
    'client_id', 'int-6f2u3vpjglGsZ8nev37Wm7',
    'ambiente', 'production'
),
api_key = '/caminho/completo/para/erp-inlaudo/certs/certificate.pem',
api_secret = '/caminho/completo/para/erp-inlaudo/certs/private-key.key'
WHERE tipo = 'cora';

-- Se o registro não existir, criar
INSERT IGNORE INTO integracoes (tipo, nome, config, api_key, api_secret, ativo, data_criacao)
VALUES (
    'cora',
    'CORA - Boletos Registrados',
    JSON_OBJECT(
        'client_id', 'int-6f2u3vpjglGsZ8nev37Wm7',
        'ambiente', 'production'
    ),
    '/caminho/completo/para/erp-inlaudo/certs/certificate.pem',
    '/caminho/completo/para/erp-inlaudo/certs/private-key.key',
    0,
    NOW()
);

-- Adicionar campos adicionais na tabela de boletos (se necessário)
-- Criar tabela de boletos se não existir
CREATE TABLE IF NOT EXISTS boletos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conta_receber_id INT,
    plataforma ENUM('cora', 'stripe') NOT NULL DEFAULT 'cora',
    id_externo VARCHAR(255) COMMENT 'ID do boleto na plataforma externa',
    codigo_unico VARCHAR(255) UNIQUE COMMENT 'Código único do boleto no sistema',
    linha_digitavel VARCHAR(255),
    codigo_barras VARCHAR(255),
    url_pdf TEXT,
    url_boleto TEXT,
    qr_code_pix TEXT COMMENT 'QR Code Pix',
    pix_copia_cola TEXT COMMENT 'Código Pix copia e cola',
    status VARCHAR(50) DEFAULT 'pendente' COMMENT 'pendente, pago, cancelado, vencido',
    valor DECIMAL(10,2),
    data_vencimento DATE,
    data_pagamento DATETIME,
    resposta_api TEXT COMMENT 'Resposta completa da API em JSON',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_receber_id) REFERENCES contas_receber(id) ON DELETE CASCADE,
    INDEX idx_plataforma (plataforma),
    INDEX idx_status (status),
    INDEX idx_codigo_unico (codigo_unico),
    INDEX idx_id_externo (id_externo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentários nas colunas
ALTER TABLE boletos 
MODIFY COLUMN plataforma ENUM('cora', 'stripe') NOT NULL DEFAULT 'cora' COMMENT 'Plataforma de geração do boleto',
MODIFY COLUMN status VARCHAR(50) DEFAULT 'pendente' COMMENT 'Status do boleto: pendente, pago, cancelado, vencido';

-- Inserir dados de exemplo (comentado - descomentar se necessário)
-- INSERT INTO boletos (conta_receber_id, plataforma, codigo_unico, valor, data_vencimento)
-- VALUES (1, 'cora', 'BOL-' . UNIX_TIMESTAMP(), 150.00, DATE_ADD(CURDATE(), INTERVAL 7 DAY));
