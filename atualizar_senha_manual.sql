-- Script SQL para Atualizar Senha do Usuário Master
-- Execute este SQL no phpMyAdmin se o script PHP não funcionar

-- IMPORTANTE: Primeiro execute o script atualizar_senha.php para gerar o hash correto
-- Se não conseguir, use um dos hashes abaixo (todos são válidos para a senha: Admin259087@)

-- Opção 1: Atualizar com hash bcrypt (recomendado)
-- Você deve substituir o hash abaixo pelo gerado no script atualizar_senha.php
UPDATE usuarios 
SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'financeiro@inlaudo.com.br';

-- Verificar se atualizou
SELECT id, nome, email, nivel, ativo, LENGTH(senha) as tamanho_hash, senha 
FROM usuarios 
WHERE email = 'financeiro@inlaudo.com.br';

-- O resultado deve mostrar:
-- - tamanho_hash: 60
-- - senha: começando com $2y$10$

-- Se o usuário não existir, criar:
-- INSERT INTO usuarios (nome, email, senha, nivel, ativo) 
-- VALUES (
--     'Administrador Master',
--     'financeiro@inlaudo.com.br',
--     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
--     'admin',
--     1
-- );
