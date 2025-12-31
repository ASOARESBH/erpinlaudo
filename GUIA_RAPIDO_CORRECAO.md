# 🔧 Guia Rápido - Correção de Autenticação

## ⚡ Solução em 3 Passos

### Passo 1: Fazer Upload dos Arquivos
Faça upload de todos os arquivos do ZIP para o servidor.

### Passo 2: Executar Script de Correção
Acesse no navegador:
```
http://seudominio.com/corrigir_senha_master.php
```

Aguarde a mensagem: **"✓ SENHA ATUALIZADA COM SUCESSO!"**

### Passo 3: Fazer Login
Acesse:
```
http://seudominio.com/login.php
```

Faça login com:
- **E-mail**: financeiro@inlaudo.com.br
- **Senha**: Admin259087@

---

## 🐛 O Que Estava Errado?

O hash da senha no banco de dados estava como:
```
$2y$10$YourHashWillBeGeneratedHere
```

Este é um **placeholder** que não foi substituído pelo hash real.

---

## ✅ O Que Foi Feito?

1. **Criado sistema de debug completo** (`lib_debug.php`)
   - Logs detalhados de autenticação
   - Logs de senha (para debug)
   - Logs de erros
   - Logs de SQL

2. **Criado script de correção automática** (`corrigir_senha_master.php`)
   - Gera hash correto da senha
   - Atualiza banco de dados automaticamente
   - Testa se funcionou
   - Exibe resultado visual

3. **Criada página de diagnóstico** (`diagnostico.php`)
   - Informações do PHP
   - Lista de usuários
   - Verificação de hash
   - Visualização de logs

4. **Atualizado login com debug** (`login.php`)
   - Logs detalhados
   - Informações de debug na tela
   - Links para correção

---

## 📦 Arquivos no Pacote

1. **lib_debug.php** - Biblioteca de debug e logs
2. **corrigir_senha_master.php** - Script de correção
3. **diagnostico.php** - Página de diagnóstico
4. **login.php** - Login atualizado com debug
5. **CORRECAO_AUTENTICACAO.md** - Documentação completa
6. **logs/** - Diretório para logs (vazio)

---

## 🔍 Como Verificar se Funcionou?

### Opção 1: Testar Login
Tente fazer login. Se funcionar, está resolvido!

### Opção 2: Ver Diagnóstico
Acesse `diagnostico.php` e verifique:
- ✅ Hash do master tem 60+ caracteres
- ✅ Teste de senha mostra "FUNCIONA"

### Opção 3: Ver Logs
Verifique `/logs/auth_YYYY-MM-DD.log` para ver se login foi bem-sucedido.

---

## 🚨 Se Ainda Não Funcionar

1. **Verifique se executou o script de correção**
   - Acesse `corrigir_senha_master.php`
   - Confirme mensagem de sucesso

2. **Verifique o diagnóstico**
   - Acesse `diagnostico.php`
   - Veja se hash está válido

3. **Verifique os logs**
   - Vá em `/logs/`
   - Abra `senha_debug_YYYY-MM-DD.log`
   - Veja o que está dando errado

4. **Verifique a senha**
   - Confirme que está digitando: `Admin259087@`
   - Case-sensitive (maiúsculas e minúsculas importam)
   - Sem espaços antes ou depois

---

## 📊 Informações do Sistema

O script de correção verifica automaticamente:

- ✅ Versão do PHP (precisa ser 7.4+)
- ✅ Função `password_hash()` disponível
- ✅ Função `password_verify()` disponível
- ✅ Algoritmo bcrypt disponível

Se algum desses não estiver OK, o problema pode ser no servidor.

---

## 🔐 Segurança

### Durante o Debug
- ✅ Pode deixar DEBUG_MODE ativo
- ✅ Pode acessar corrigir_senha_master.php
- ✅ Pode acessar diagnostico.php

### Depois de Resolver
- ❌ Desative DEBUG_MODE em lib_debug.php
- ❌ Delete ou proteja corrigir_senha_master.php
- ❌ Delete ou proteja diagnostico.php
- ❌ Delete logs de senha

---

## 📞 Precisa de Ajuda?

### Informações Úteis

1. **Versão do PHP**: Veja em `diagnostico.php`
2. **Hash no banco**: Veja em `diagnostico.php`
3. **Logs de erro**: Veja em `/logs/error_*.log`
4. **Logs de autenticação**: Veja em `/logs/auth_*.log`

### Comandos SQL Úteis

Ver usuário master:
```sql
SELECT * FROM usuarios WHERE email = 'financeiro@inlaudo.com.br';
```

Ver tamanho do hash:
```sql
SELECT LENGTH(senha) FROM usuarios WHERE email = 'financeiro@inlaudo.com.br';
```

Atualizar hash manualmente (se necessário):
```sql
UPDATE usuarios 
SET senha = '$2y$10$...' -- Cole o hash gerado
WHERE email = 'financeiro@inlaudo.com.br';
```

---

## ✅ Checklist

- [ ] Upload dos arquivos feito
- [ ] Pasta /logs/ criada
- [ ] Executado corrigir_senha_master.php
- [ ] Mensagem de sucesso apareceu
- [ ] Diagnóstico mostra hash válido
- [ ] Login testado com sucesso

---

## 🎯 Resultado Esperado

Após a correção:

1. Hash no banco: ~60 caracteres
2. Começa com `$2y$10$`
3. Login funciona com:
   - E-mail: financeiro@inlaudo.com.br
   - Senha: Admin259087@
4. Redirecionamento para dashboard

---

**Versão**: 5.0.1 (Debug)  
**Data**: 22/12/2025  
**Sistema**: ERP INLAUDO  
**Status**: ✅ Pronto para Correção

---

## 📚 Documentação Completa

Para mais detalhes, consulte `CORRECAO_AUTENTICACAO.md`
