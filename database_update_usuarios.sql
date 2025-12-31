-- Atualização do Banco de Dados - Sistema de Usuários
-- Data: 2025-12-22

-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL COMMENT 'Hash da senha',
    nivel ENUM('admin', 'usuario') NOT NULL DEFAULT 'usuario' COMMENT 'admin = acesso total, usuario = acesso limitado',
    ativo TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = ativo, 0 = inativo',
    ultimo_acesso DATETIME NULL COMMENT 'Data/hora do último login',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_ativo (ativo),
    INDEX idx_nivel (nivel)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuário master
INSERT INTO usuarios (nome, email, senha, nivel, ativo, data_criacao) 
VALUES (
    'Administrador Master',
    'financeiro@inlaudo.com.br',
    '$2y$10$YourHashWillBeGeneratedHere', -- Hash será gerado ao executar gerar_hash_senha.php
    'admin',
    1,
    NOW()
) ON DUPLICATE KEY UPDATE 
    nome = 'Administrador Master',
    nivel = 'admin',
    ativo = 1;

-- Criar tabela de logs de acesso
CREATE TABLE IF NOT EXISTS logs_acesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    email VARCHAR(255) NOT NULL,
    acao ENUM('login', 'logout', 'tentativa_falha') NOT NULL,
    ip VARCHAR(45) COMMENT 'IP do usuário',
    user_agent TEXT COMMENT 'Navegador/dispositivo',
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_email (email),
    INDEX idx_acao (acao),
    INDEX idx_data_hora (data_hora)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Criar tabela de sessões (opcional, para controle avançado)
CREATE TABLE IF NOT EXISTS sessoes (
    id VARCHAR(128) PRIMARY KEY,
    usuario_id INT NOT NULL,
    ip VARCHAR(45),
    user_agent TEXT,
    data_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_expiracao DATETIME,
    ativo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_ativo (ativo),
    INDEX idx_data_expiracao (data_expiracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentários nas tabelas
ALTER TABLE usuarios COMMENT = 'Tabela de usuários do sistema';
ALTER TABLE logs_acesso COMMENT = 'Logs de acesso e tentativas de login';
ALTER TABLE sessoes COMMENT = 'Controle de sessões ativas';

-- Verificar se o usuário master foi criado
SELECT 'Usuário master criado com sucesso!' as mensagem, email, nivel, ativo 
FROM usuarios 
WHERE email = 'financeiro@inlaudo.com.br';
