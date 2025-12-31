# Atualização V5.0 - Sistema de Autenticação e Usuários

## 📋 Visão Geral

Esta atualização implementa um sistema completo de autenticação e gerenciamento de usuários no ERP INLAUDO, adicionando segurança e controle de acesso a todas as funcionalidades do sistema.

**Data**: 22 de Dezembro de 2025  
**Versão**: 5.0

---

## 🎯 O Que Foi Implementado

### 1. Sistema de Login 🔐
- Tela de login moderna e responsiva
- Validação de credenciais com senha criptografada (password_hash)
- Mensagens de erro amigáveis
- Redirecionamento automático após login
- Design profissional com logo da INLAUDO

### 2. Controle de Sessão 🕐
- Sessões seguras com PHP
- Timeout de inatividade (30 minutos)
- Verificação automática de autenticação
- Logout seguro com limpeza de sessão
- Registro de login/logout em logs

### 3. Gerenciamento de Usuários 👥
- Cadastro completo de usuários
- Dois níveis de acesso:
  - **Administrador**: Acesso total ao sistema
  - **Usuário**: Acesso limitado (sem gerenciamento de usuários)
- Ativação/desativação de usuários
- Edição de dados e senha
- Proteção contra auto-exclusão
- Logs de acesso e tentativas de login

### 4. Usuário Master Pré-Cadastrado 👑
- **E-mail**: financeiro@inlaudo.com.br
- **Senha**: Admin259087@
- **Nível**: Administrador
- **Status**: Ativo

### 5. Proteção de Páginas 🛡️
- Todas as páginas protegidas com autenticação
- Verificação automática via header.php
- Redirecionamento para login se não autenticado
- Páginas administrativas restritas a administradores

### 6. Interface Atualizada 🎨
- Menu de usuários (apenas para administradores)
- Informações do usuário logado no menu
- Botão de logout visível
- Rodapé com versão do sistema (5.0)
- Tempo de sessão exibido no rodapé
- Design responsivo e moderno

---

## 📦 Arquivos Criados/Atualizados

### Novos Arquivos (9)
1. **database_update_usuarios.sql** - Estrutura do banco de dados
2. **login.php** - Página de login
3. **logout.php** - Página de logout
4. **auth.php** - Sistema de autenticação e controle de sessão
5. **usuarios.php** - Listagem de usuários
6. **usuario_form.php** - Formulário de cadastro/edição
7. **usuario_delete.php** - Exclusão de usuário
8. **gerar_hash_senha.php** - Utilitário para gerar hash de senhas
9. **ATUALIZACAO_V5_USUARIOS.md** - Esta documentação

### Arquivos Atualizados (3)
1. **header.php** - Adicionado verificação de autenticação, menu de usuários e info do usuário
2. **footer.php** - Adicionado rodapé com versão e tempo de sessão
3. **style.css** - Adicionado estilos para login, usuário e rodapé

---

## 🗄️ Estrutura do Banco de Dados

### Tabela: usuarios
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- nome (VARCHAR(255), NOT NULL)
- email (VARCHAR(255), NOT NULL, UNIQUE)
- senha (VARCHAR(255), NOT NULL) -- Hash da senha
- nivel (ENUM('admin', 'usuario'), DEFAULT 'usuario')
- ativo (TINYINT(1), DEFAULT 1)
- ultimo_acesso (DATETIME, NULL)
- data_criacao (DATETIME, DEFAULT CURRENT_TIMESTAMP)
- data_atualizacao (DATETIME, ON UPDATE CURRENT_TIMESTAMP)
```

### Tabela: logs_acesso
```sql
- id (INT, AUTO_INCREMENT, PRIMARY KEY)
- usuario_id (INT, FOREIGN KEY)
- email (VARCHAR(255), NOT NULL)
- acao (ENUM('login', 'logout', 'tentativa_falha'))
- ip (VARCHAR(45))
- user_agent (TEXT)
- data_hora (DATETIME, DEFAULT CURRENT_TIMESTAMP)
```

### Tabela: sessoes
```sql
- id (VARCHAR(128), PRIMARY KEY)
- usuario_id (INT, FOREIGN KEY)
- ip (VARCHAR(45))
- user_agent (TEXT)
- data_inicio (DATETIME, DEFAULT CURRENT_TIMESTAMP)
- data_expiracao (DATETIME)
- ativo (TINYINT(1), DEFAULT 1)
```

---

## 🚀 Instalação

### Passo 1: Backup
```bash
mysqldump -u inlaud99_admin -p inlaud99_erpinlaudo > backup_antes_v5.sql
```

### Passo 2: Atualizar Banco de Dados
```sql
-- No phpMyAdmin ou MySQL:
SOURCE database_update_usuarios.sql;
```

**IMPORTANTE**: Antes de executar o SQL, você precisa gerar o hash da senha:

```bash
# No servidor, execute:
php gerar_hash_senha.php

# Copie o hash gerado e substitua no arquivo database_update_usuarios.sql
# Na linha que contém: '$2y$10$YourHashWillBeGeneratedHere'
```

### Passo 3: Fazer Upload dos Arquivos
- Faça upload de todos os novos arquivos
- Sobrescreva os arquivos atualizados (header.php, footer.php, style.css)

### Passo 4: Testar
1. Acesse: `http://seudominio.com/`
2. Você será redirecionado para `login.php`
3. Faça login com:
   - **E-mail**: financeiro@inlaudo.com.br
   - **Senha**: Admin259087@
4. Verifique se o sistema carrega corretamente

---

## 🔐 Como Usar

### Fazer Login
1. Acesse o sistema
2. Digite e-mail e senha
3. Clique em "Entrar no Sistema"
4. Você será redirecionado para o dashboard

### Gerenciar Usuários (Apenas Administradores)
1. Clique em **Usuários** no menu
2. Clique em **Gerenciar Usuários**
3. Para adicionar: Clique em "Novo Usuário"
4. Para editar: Clique no ícone de edição
5. Para excluir: Clique no ícone de lixeira

### Criar Novo Usuário
1. Vá em Usuários > Gerenciar Usuários
2. Clique em "Novo Usuário"
3. Preencha:
   - Nome completo
   - E-mail (será o login)
   - Senha (mínimo 6 caracteres)
   - Nível de acesso (Administrador ou Usuário)
   - Marque "Usuário ativo"
4. Clique em "Salvar"

### Alterar Senha
1. Vá em Usuários > Gerenciar Usuários
2. Clique em editar no usuário desejado
3. Digite a nova senha
4. Clique em "Salvar"

### Fazer Logout
1. Clique em "Sair" no menu superior direito
2. Você será redirecionado para a tela de login

---

## 🛡️ Segurança Implementada

### Autenticação
- ✅ Senhas criptografadas com `password_hash()` (bcrypt)
- ✅ Verificação com `password_verify()`
- ✅ Proteção contra SQL Injection (prepared statements)
- ✅ Proteção contra XSS (htmlspecialchars)

### Sessões
- ✅ Timeout de inatividade (30 minutos)
- ✅ Verificação automática em todas as páginas
- ✅ Limpeza completa ao fazer logout
- ✅ Registro de IP e User-Agent

### Controle de Acesso
- ✅ Páginas protegidas com `auth.php`
- ✅ Verificação de nível de acesso
- ✅ Redirecionamento automático se não autorizado
- ✅ Proteção contra auto-exclusão

### Logs
- ✅ Registro de todos os logins
- ✅ Registro de todos os logouts
- ✅ Registro de tentativas falhas
- ✅ Armazenamento de IP e navegador

---

## 📊 Níveis de Acesso

### Administrador
**Permissões**:
- ✅ Acesso total a todos os módulos
- ✅ Gerenciar usuários (criar, editar, excluir)
- ✅ Ver logs de acesso
- ✅ Configurar integrações
- ✅ Acessar todas as funcionalidades

**Menu Exclusivo**:
- Usuários > Gerenciar Usuários

### Usuário
**Permissões**:
- ✅ Acesso aos módulos operacionais:
  - CRM (Clientes e Interações)
  - Financeiro (Contas a Pagar e Receber)
  - Produtos (Contratos)
  - Faturamento
- ❌ Não pode gerenciar usuários
- ❌ Não pode acessar configurações sensíveis

---

## 🔧 Configurações

### Timeout de Sessão
Padrão: **30 minutos de inatividade**

Para alterar, edite o arquivo `auth.php`:
```php
define('SESSION_TIMEOUT', 1800); // Altere o valor (em segundos)
```

### Requisitos de Senha
Padrão: **Mínimo 6 caracteres**

Para alterar, edite o arquivo `usuario_form.php`:
```html
<input type="password" ... minlength="6">
```

---

## 📈 Logs e Monitoramento

### Visualizar Logs de Acesso
1. Vá em Usuários > Gerenciar Usuários
2. Role até a seção "Logs de Acesso Recentes"
3. Veja os últimos 20 acessos

### Informações nos Logs
- Data e hora do acesso
- Nome do usuário
- E-mail
- Ação (Login, Logout, Falha)
- Endereço IP

---

## 🐛 Solução de Problemas

### Não Consigo Fazer Login
**Problema**: E-mail ou senha incorretos  
**Solução**:
1. Verifique se digitou corretamente
2. Verifique se o usuário está ativo
3. Se esqueceu a senha, peça a um administrador para resetá-la

### Sessão Expira Muito Rápido
**Problema**: Timeout muito curto  
**Solução**: Aumente o valor em `auth.php` (linha `SESSION_TIMEOUT`)

### Erro ao Criar Usuário
**Problema**: E-mail já cadastrado  
**Solução**: Use outro e-mail ou edite o usuário existente

### Não Vejo o Menu "Usuários"
**Problema**: Você não é administrador  
**Solução**: Apenas administradores podem gerenciar usuários

### Hash de Senha Não Funciona
**Problema**: Hash não foi gerado corretamente  
**Solução**:
1. Execute: `php gerar_hash_senha.php`
2. Copie o hash gerado
3. Atualize no SQL ou diretamente no banco

---

## ✅ Checklist de Instalação

- [ ] Backup do banco de dados realizado
- [ ] Hash da senha gerado com `gerar_hash_senha.php`
- [ ] Hash substituído no `database_update_usuarios.sql`
- [ ] Banco de dados atualizado
- [ ] Arquivos novos enviados
- [ ] Arquivos atualizados sobrescritos
- [ ] Teste de login realizado
- [ ] Usuário master funciona
- [ ] Menu de usuários aparece para admin
- [ ] Logout funciona
- [ ] Tempo de sessão aparece no rodapé
- [ ] Versão 5.0 aparece no rodapé

---

## 🎯 Próximas Melhorias

### Sugestões para Versões Futuras
- [ ] Recuperação de senha por e-mail
- [ ] Autenticação de dois fatores (2FA)
- [ ] Histórico de alterações de usuários
- [ ] Permissões granulares por módulo
- [ ] Expiração de senha após X dias
- [ ] Política de senha forte obrigatória
- [ ] Bloqueio após X tentativas falhas
- [ ] Notificação de novo login por e-mail

---

## 📞 Suporte

### Problemas com Login
1. Verifique os logs de acesso
2. Confirme que o usuário está ativo
3. Teste com o usuário master

### Problemas com Permissões
1. Verifique o nível do usuário no banco
2. Confirme que está logado como administrador
3. Limpe o cache do navegador

### Problemas com Sessão
1. Verifique as configurações de sessão do PHP
2. Confirme que cookies estão habilitados
3. Teste em modo anônimo do navegador

---

## 📚 Referências

- [PHP password_hash()](https://www.php.net/manual/pt_BR/function.password-hash.php)
- [PHP Sessions](https://www.php.net/manual/pt_BR/book.session.php)
- [Segurança em PHP](https://www.php.net/manual/pt_BR/security.php)

---

**Versão**: 5.0  
**Data**: 22/12/2025  
**Sistema**: ERP INLAUDO  
**Desenvolvido para**: INLAUDO - Conectando Saúde e Tecnologia

---

## 🎉 Conclusão

O sistema de autenticação está **100% funcional** e pronto para uso em produção! Todas as páginas agora estão protegidas e apenas usuários autorizados podem acessar o sistema.

**Principais Ganhos**:
- 🔐 Segurança completa com autenticação
- 👥 Gerenciamento de usuários
- 📊 Logs de acesso
- ⏱️ Controle de sessão
- 🎨 Interface moderna
- 🛡️ Proteção de todas as páginas
