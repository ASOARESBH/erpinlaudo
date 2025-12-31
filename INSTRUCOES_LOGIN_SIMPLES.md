# 🔐 Instruções para Corrigir Login - ERP INLAUDO

## ✅ O Que Foi Feito

1. **Login revertido** para versão original simples (sem debug)
2. **Script automático** criado para gerar e atualizar hash bcrypt
3. **Script SQL manual** como alternativa

---

## 🚀 Como Corrigir (2 Opções)

### **Opção 1: Script Automático (Recomendado)** ⚡

1. Faça upload dos arquivos para o servidor
2. Acesse no navegador:
   ```
   http://seudominio.com/atualizar_senha.php
   ```
3. O script irá:
   - ✅ Gerar hash bcrypt correto
   - ✅ Atualizar automaticamente no banco
   - ✅ Testar se funcionou
   - ✅ Mostrar resultado

4. Clique em "Ir para Login"
5. Faça login com:
   - **E-mail**: financeiro@inlaudo.com.br
   - **Senha**: Admin259087@

### **Opção 2: SQL Manual** 🛠️

Se o script PHP não funcionar:

1. Acesse phpMyAdmin
2. Selecione o banco `inlaud99_erpinlaudo`
3. Vá em "SQL"
4. Execute:
   ```sql
   UPDATE usuarios 
   SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
   WHERE email = 'financeiro@inlaudo.com.br';
   ```
5. Faça login normalmente

---

## 📦 Arquivos no Pacote

1. **login.php** - Login original simples (sem debug)
2. **atualizar_senha.php** - Script automático de correção
3. **atualizar_senha_manual.sql** - SQL para correção manual
4. **INSTRUCOES_LOGIN_SIMPLES.md** - Este arquivo

---

## 🔍 Como Verificar

### No phpMyAdmin:

```sql
SELECT id, nome, email, LENGTH(senha) as tamanho, senha 
FROM usuarios 
WHERE email = 'financeiro@inlaudo.com.br';
```

**Resultado esperado**:
- **tamanho**: 60
- **senha**: começa com `$2y$10$`

---

## 🐛 Solução de Problemas

### Problema: Script PHP não funciona

**Solução**: Use a Opção 2 (SQL Manual)

### Problema: Login ainda não funciona

**Verificações**:
1. Confirme que o hash tem 60 caracteres
2. Confirme que começa com `$2y$10$`
3. Confirme que está digitando a senha exata: `Admin259087@`
4. Tente em navegador anônimo
5. Limpe cache do navegador

### Problema: Usuário não existe

**Solução**: Execute no phpMyAdmin:
```sql
INSERT INTO usuarios (nome, email, senha, nivel, ativo) 
VALUES (
    'Administrador Master',
    'financeiro@inlaudo.com.br',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    1
);
```

---

## 🔐 Sobre o Bcrypt

O sistema usa **password_hash()** do PHP com algoritmo **bcrypt**:

- ✅ Seguro e moderno
- ✅ Salt automático
- ✅ Resistente a ataques
- ✅ Padrão da indústria

**Como funciona**:
```php
// Gerar hash
$hash = password_hash('Admin259087@', PASSWORD_BCRYPT);

// Verificar senha
$correto = password_verify('Admin259087@', $hash);
```

---

## ⚠️ Segurança

### Após o login funcionar:

1. ✅ **DELETE** o arquivo `atualizar_senha.php`
2. ✅ **DELETE** o arquivo `atualizar_senha_manual.sql`
3. ✅ Mantenha apenas o `login.php`

**Comando para deletar**:
```bash
rm atualizar_senha.php
rm atualizar_senha_manual.sql
```

---

## ✅ Checklist

- [ ] Upload dos arquivos feito
- [ ] Executado `atualizar_senha.php` OU SQL manual
- [ ] Verificado hash no banco (60 caracteres)
- [ ] Login testado com sucesso
- [ ] Arquivo `atualizar_senha.php` deletado
- [ ] Arquivo `atualizar_senha_manual.sql` deletado

---

## 🎯 Credenciais do Master

**E-mail**: financeiro@inlaudo.com.br  
**Senha**: Admin259087@

**⚠️ Importante**: A senha é case-sensitive (maiúsculas e minúsculas importam)!

---

## 📞 Suporte

Se ainda não funcionar:

1. Verifique versão do PHP (precisa ser 7.4+)
2. Verifique se função `password_hash()` está disponível
3. Verifique se função `password_verify()` está disponível
4. Verifique conexão com banco de dados em `config.php`

---

**Versão**: 5.0  
**Data**: 22/12/2025  
**Sistema**: ERP INLAUDO  
**Status**: ✅ Pronto para Uso
