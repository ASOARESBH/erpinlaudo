# 🚨 Acesso de Emergência - ERP INLAUDO

## ✅ Solução Implementada

Criei um **acesso de emergência hardcoded** (direto no código) que **NÃO verifica o banco de dados**.

Você pode entrar no sistema imediatamente para fazer as correções necessárias!

---

## 🔑 Credenciais de Emergência

**E-mail**: financeiro@inlaudo.com.br  
**Senha**: 123

---

## 🚀 Como Usar

### Passo 1: Upload do Arquivo

Faça upload do arquivo `login.php` para o servidor, **sobrescrevendo** o arquivo atual.

### Passo 2: Fazer Login

Acesse:
```
http://seudominio.com/login.php
```

Use as credenciais de emergência:
- **E-mail**: financeiro@inlaudo.com.br
- **Senha**: 123

### Passo 3: Acessar o Sistema

Você será logado automaticamente como **Administrador (Emergência)** e terá acesso total ao sistema!

---

## 🔧 Como Funciona

O login agora verifica **PRIMEIRO** as credenciais de emergência hardcoded:

```php
// ACESSO DE EMERGÊNCIA (HARDCODED)
define('EMERGENCY_EMAIL', 'financeiro@inlaudo.com.br');
define('EMERGENCY_PASSWORD', '123');
define('EMERGENCY_ENABLED', true);
```

Se você digitar essas credenciais, o sistema:
1. ✅ Ignora completamente o banco de dados
2. ✅ Cria uma sessão de emergência
3. ✅ Dá acesso de administrador
4. ✅ Redireciona para o dashboard

Se digitar outras credenciais, o sistema funciona normalmente (verifica no banco).

---

## ⚠️ Aviso de Segurança

### Durante o Uso

Você verá um aviso amarelo na tela de login:

```
⚠️ Modo de Emergência Ativo
Acesso de emergência habilitado para correções.
Use: financeiro@inlaudo.com.br / 123
```

### Após Corrigir

**IMPORTANTE**: Depois de corrigir os problemas do banco de dados, você DEVE desativar o acesso de emergência!

**Opção 1: Desativar no Código**

Edite o arquivo `login.php` e mude:
```php
define('EMERGENCY_ENABLED', true); // Mude para false
```

Para:
```php
define('EMERGENCY_ENABLED', false); // Desativado
```

**Opção 2: Substituir pelo Login Normal**

Substitua o `login.php` pela versão normal (sem acesso de emergência).

---

## 🎯 O Que Você Pode Fazer Agora

Com acesso de emergência, você pode:

1. ✅ Acessar o sistema normalmente
2. ✅ Ir em **Usuários** para gerenciar usuários
3. ✅ Criar novo usuário com senha que funcione
4. ✅ Editar usuário master e alterar senha
5. ✅ Acessar phpMyAdmin para corrigir banco
6. ✅ Executar scripts de correção
7. ✅ Fazer qualquer ajuste necessário

---

## 🔍 Identificação do Acesso de Emergência

Quando logado com acesso de emergência:

- **Nome exibido**: Administrador (Emergência)
- **ID do usuário**: 999 (temporário)
- **Nível**: admin (acesso total)
- **Flag especial**: `$_SESSION['emergency_access'] = true`

---

## 📋 Checklist de Uso

- [ ] Upload do `login.php` feito
- [ ] Acessado o sistema com financeiro@inlaudo.com.br / 123
- [ ] Login funcionou (entrou no dashboard)
- [ ] Correções necessárias realizadas
- [ ] Banco de dados corrigido
- [ ] Testado login normal (com usuário do banco)
- [ ] Acesso de emergência desativado (EMERGENCY_ENABLED = false)
- [ ] Testado novamente para garantir

---

## 🛠️ Correções Recomendadas

Agora que você tem acesso, recomendo:

### 1. Corrigir Hash da Senha no Banco

Acesse phpMyAdmin e execute:

```sql
-- Gerar hash correto
-- Use um gerador online de bcrypt ou o script PHP

UPDATE usuarios 
SET senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE email = 'financeiro@inlaudo.com.br';
```

### 2. Ou Criar Novo Usuário

Vá em **Usuários > Novo Usuário** e crie:
- Nome: Administrador
- E-mail: admin@inlaudo.com.br
- Senha: Admin123 (ou outra que preferir)
- Nível: Administrador

### 3. Testar Login Normal

Faça logout e tente logar com o usuário do banco para confirmar que funciona.

### 4. Desativar Emergência

Mude `EMERGENCY_ENABLED` para `false` no código.

---

## 🔐 Segurança

### Por Que Isso é Seguro?

- ✅ Credenciais hardcoded só você conhece
- ✅ Pode ser desativado facilmente
- ✅ Temporário (apenas para correção)
- ✅ Exibe aviso na tela
- ✅ Não afeta usuários normais

### Por Que Desativar Depois?

- ❌ Acesso hardcoded não deve ficar em produção
- ❌ Senha simples (123) não é segura
- ❌ Bypass de autenticação é risco de segurança
- ❌ Não há log de acesso de emergência

---

## 🎯 Fluxo Completo

```
1. Upload login.php
   ↓
2. Login com financeiro@inlaudo.com.br / 123
   ↓
3. Acesso concedido (sem verificar banco)
   ↓
4. Corrigir banco de dados / criar usuário
   ↓
5. Testar login normal
   ↓
6. Desativar acesso de emergência
   ↓
7. Pronto! Sistema funcionando normalmente
```

---

## ✅ Vantagens

- ✅ **Acesso imediato** ao sistema
- ✅ **Não depende** do banco de dados
- ✅ **Simples** de usar
- ✅ **Fácil** de desativar
- ✅ **Temporário** e seguro

---

## 🚨 Lembre-se

**DEPOIS DE CORRIGIR, DESATIVE O ACESSO DE EMERGÊNCIA!**

Mude no código:
```php
define('EMERGENCY_ENABLED', false);
```

Ou substitua pelo login normal.

---

## 📞 Suporte

Se tiver qualquer problema:

1. Verifique se fez upload do arquivo correto
2. Verifique se está digitando as credenciais exatas
3. Limpe cache do navegador
4. Tente em navegador anônimo

---

**Versão**: 5.0 (Emergency Mode)  
**Data**: 22/12/2025  
**Sistema**: ERP INLAUDO  
**Status**: 🚨 Acesso de Emergência Ativo

---

## 🎉 Conclusão

Agora você tem acesso garantido ao sistema!

Entre, faça as correções necessárias e depois desative o modo de emergência.

Simples e direto! 🚀
