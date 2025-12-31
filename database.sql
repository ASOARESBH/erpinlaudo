-- Banco de Dados: inlaud99_erpinlaudo
-- Usuário: inlaud99_admin
-- Senha: Admin259087@

-- Tabela de Clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_pessoa ENUM('CNPJ', 'CPF') NOT NULL,
    tipo_cliente ENUM('LEAD', 'CLIENTE') NOT NULL DEFAULT 'LEAD',
    cnpj_cpf VARCHAR(18) NOT NULL UNIQUE,
    razao_social VARCHAR(255),
    nome_fantasia VARCHAR(255),
    nome VARCHAR(255),
    email VARCHAR(255),
    telefone VARCHAR(20),
    celular VARCHAR(20),
    cep VARCHAR(10),
    logradouro VARCHAR(255),
    numero VARCHAR(10),
    complemento VARCHAR(100),
    bairro VARCHAR(100),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    data_abertura DATE,
    situacao VARCHAR(50),
    atividade_principal TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tipo_cliente (tipo_cliente),
    INDEX idx_cnpj_cpf (cnpj_cpf)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Interações
CREATE TABLE IF NOT EXISTS interacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    data_hora_interacao DATETIME NOT NULL,
    forma_contato ENUM('telefone', 'e-mail', 'presencial', 'whatsapp') NOT NULL,
    historico TEXT NOT NULL,
    proximo_contato_data DATETIME,
    proximo_contato_forma ENUM('telefone', 'e-mail', 'presencial', 'whatsapp'),
    alerta_enviado TINYINT(1) DEFAULT 0,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_proximo_contato (proximo_contato_data)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Plano de Contas
CREATE TABLE IF NOT EXISTS plano_contas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    tipo ENUM('RECEITA', 'DESPESA') NOT NULL,
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir planos de contas padrão
INSERT INTO plano_contas (nome, tipo, descricao) VALUES
('Serviços Prestados', 'RECEITA', 'Receita de serviços prestados'),
('Venda de Produtos', 'RECEITA', 'Receita de venda de produtos'),
('Outras Receitas', 'RECEITA', 'Outras receitas diversas'),
('Salários e Encargos', 'DESPESA', 'Pagamento de salários e encargos'),
('Aluguel', 'DESPESA', 'Pagamento de aluguel'),
('Fornecedores', 'DESPESA', 'Pagamento a fornecedores'),
('Impostos e Taxas', 'DESPESA', 'Pagamento de impostos e taxas'),
('Outras Despesas', 'DESPESA', 'Outras despesas diversas');

-- Tabela de Contas a Receber
CREATE TABLE IF NOT EXISTS contas_receber (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    plano_contas_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    forma_pagamento ENUM('boleto', 'cartao_credito', 'cartao_debito', 'pix', 'dinheiro', 'transferencia') NOT NULL,
    status ENUM('pendente', 'pago', 'vencido', 'cancelado') DEFAULT 'pendente',
    recorrencia INT DEFAULT 1 COMMENT 'Número de vezes que se repete',
    parcela_atual INT DEFAULT 1,
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (plano_contas_id) REFERENCES plano_contas(id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Contas a Pagar
CREATE TABLE IF NOT EXISTS contas_pagar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fornecedor VARCHAR(255) NOT NULL,
    plano_contas_id INT NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    forma_pagamento ENUM('boleto', 'cartao_credito', 'cartao_debito', 'pix', 'dinheiro', 'transferencia') NOT NULL,
    status ENUM('pendente', 'pago', 'vencido', 'cancelado') DEFAULT 'pendente',
    recorrencia INT DEFAULT 1 COMMENT 'Número de vezes que se repete',
    parcela_atual INT DEFAULT 1,
    observacoes TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (plano_contas_id) REFERENCES plano_contas(id),
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Configurações de Integração
CREATE TABLE IF NOT EXISTS integracoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('cora', 'stripe') NOT NULL,
    api_key VARCHAR(255),
    api_secret VARCHAR(255),
    webhook_url VARCHAR(255),
    ativo TINYINT(1) DEFAULT 0,
    configuracoes JSON,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configurações padrão de integração
INSERT INTO integracoes (tipo, ativo) VALUES
('cora', 0),
('stripe', 0);
