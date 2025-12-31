-- ========================================
-- ATUALIZAÇÃO: PORTAL DO CLIENTE
-- ========================================

-- Adicionar coluna tipo_usuario na tabela usuarios
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS tipo_usuario ENUM('admin', 'usuario', 'cliente') DEFAULT 'usuario' AFTER nivel;

-- Adicionar coluna cliente_id para vincular usuário ao cliente
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS cliente_id INT NULL AFTER tipo_usuario,
ADD CONSTRAINT fk_usuario_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE;

-- Atualizar usuários existentes para tipo admin ou usuario
UPDATE usuarios SET tipo_usuario = 'admin' WHERE nivel = 'admin';
UPDATE usuarios SET tipo_usuario = 'usuario' WHERE nivel = 'usuario';

-- Criar índice para melhor performance
CREATE INDEX IF NOT EXISTS idx_usuarios_tipo ON usuarios(tipo_usuario);
CREATE INDEX IF NOT EXISTS idx_usuarios_cliente ON usuarios(cliente_id);

-- ========================================
-- EXEMPLO: Criar usuário cliente
-- ========================================
-- Para criar um usuário cliente, primeiro pegue o ID do cliente:
-- SELECT id, nome, cnpj FROM clientes WHERE cnpj = '12345678000190';

-- Depois insira o usuário:
-- INSERT INTO usuarios (nome, email, senha, nivel, tipo_usuario, cliente_id, ativo)
-- VALUES (
--     'Nome do Cliente',
--     '12345678000190', -- CNPJ como login
--     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- Senha: 123
--     'usuario',
--     'cliente',
--     1, -- ID do cliente
--     1
-- );

-- ========================================
-- HASH DA SENHA PADRÃO "123"
-- ========================================
-- $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

-- ========================================
-- VERIFICAR ESTRUTURA
-- ========================================
SELECT 
    u.id,
    u.nome,
    u.email,
    u.tipo_usuario,
    u.cliente_id,
    c.nome as nome_cliente,
    c.cnpj
FROM usuarios u
LEFT JOIN clientes c ON u.cliente_id = c.id
WHERE u.tipo_usuario = 'cliente';
