-- Atualização do Banco de Dados - ERP INLAUDO
-- Novas funcionalidades: Produtos/Serviços, Contratos e CMV

-- Tabela de Produtos/Serviços (Contratos)
CREATE TABLE IF NOT EXISTS contratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tipo ENUM('produto', 'servico') NOT NULL,
    descricao TEXT NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    forma_pagamento ENUM('boleto', 'cartao_credito', 'cartao_debito', 'pix', 'dinheiro', 'transferencia') NOT NULL,
    recorrencia INT DEFAULT 1 COMMENT 'Número de parcelas',
    status ENUM('ativo', 'inativo') DEFAULT 'ativo',
    arquivo_contrato VARCHAR(255) COMMENT 'Caminho do arquivo anexado',
    data_inicio DATE,
    data_fim DATE,
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_status (status),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de CMV (Custo de Mercadoria Vendida)
CREATE TABLE IF NOT EXISTS cmv (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL COMMENT 'Ex: Mão de obra, Material, etc',
    valor_unitario DECIMAL(10, 2) NOT NULL,
    quantidade DECIMAL(10, 2) NOT NULL DEFAULT 1,
    valor_total DECIMAL(10, 2) NOT NULL,
    recorrente TINYINT(1) DEFAULT 0 COMMENT 'Se é custo recorrente',
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
    INDEX idx_contrato (contrato_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Boletos Gerados
CREATE TABLE IF NOT EXISTS boletos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conta_receber_id INT NOT NULL,
    plataforma ENUM('cora', 'stripe') NOT NULL,
    boleto_id VARCHAR(255) COMMENT 'ID do boleto na plataforma',
    codigo_barras VARCHAR(255),
    linha_digitavel VARCHAR(255),
    url_boleto VARCHAR(500),
    url_pdf VARCHAR(500),
    status ENUM('pendente', 'pago', 'cancelado', 'vencido') DEFAULT 'pendente',
    data_vencimento DATE NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    resposta_api TEXT COMMENT 'JSON da resposta da API',
    data_geracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (conta_receber_id) REFERENCES contas_receber(id) ON DELETE CASCADE,
    INDEX idx_conta_receber (conta_receber_id),
    INDEX idx_boleto_id (boleto_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Adicionar campo para armazenar ID do boleto em contas_receber
ALTER TABLE contas_receber 
ADD COLUMN boleto_id INT NULL AFTER observacoes,
ADD FOREIGN KEY (boleto_id) REFERENCES boletos(id) ON DELETE SET NULL;

-- Criar diretório virtual para uploads (será criado via PHP)
-- Os contratos serão salvos em: /uploads/contratos/
