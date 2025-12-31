# 🚀 Guia Rápido - Portal do Cliente V6.0

## ✅ Instalação em 3 Passos

### **Passo 1: Upload dos Arquivos** 📤

Faça upload de todos os arquivos do ZIP para o servidor (sobrescrever os existentes).

### **Passo 2: Atualizar Banco de Dados** 🗄️

Acesse o **phpMyAdmin**, selecione o banco `inlaud99_erpinlaudo` e execute:

```bash
database_update_portal_cliente.sql
```

### **Passo 3: Criar Usuários para Clientes** 👤

1. Faça login como **administrador**
2. Acesse **Usuários > Criar Usuário Cliente**
3. Clique em "Criar Usuário" para cada cliente
4. Anote as credenciais:
   - **Login**: CNPJ (apenas números)
   - **Senha**: 123

**Pronto!** 🎉

---

## 🔑 Credenciais de Acesso

### Para Clientes:
- **Login**: CNPJ sem formatação (ex: 12345678000190)
- **Senha**: 123 (pode ser alterada pelo cliente)

### Para Administradores:
- **Login**: financeiro@inlaudo.com.br
- **Senha**: Admin259087@

---

## 📱 Como o Cliente Acessa

1. Acesse a página de login
2. Digite o **CNPJ** (apenas números)
3. Digite a senha: **123**
4. Clique em "Entrar"
5. Será redirecionado para o **Portal do Cliente**

---

## 🎯 O Que o Cliente Pode Fazer

### ✅ **Início (Dashboard)**
- Ver resumo de contratos ativos
- Ver total de contas pendentes
- Ver histórico de interações
- Informações da conta

### ✅ **Meus Contratos**
- Ver todos os contratos (ativos/inativos)
- Detalhes completos de cada contrato
- Análise financeira (CMV)
- Baixar contratos em PDF

### ✅ **Meu Financeiro**
- Ver contas a receber
- Filtrar por status e mês
- Ver contas vencidas destacadas
- Baixar boletos

### ✅ **Helpdesk**
- Abrir novas solicitações
- Ver histórico de atendimentos
- Agendar próximos contatos

### ✅ **Meus Dados**
- Ver informações da empresa
- **Alterar senha**

---

## 🔐 Segurança

- ✅ Senhas criptografadas com bcrypt
- ✅ Sessão com timeout de 30 minutos
- ✅ Cliente vê apenas seus próprios dados
- ✅ Isolamento total entre clientes
- ✅ Logout seguro

---

## 📊 Arquivos do Portal

**Novos Arquivos (9)**:
1. `auth_cliente.php` - Autenticação
2. `header_cliente.php` - Header
3. `footer_cliente.php` - Footer
4. `portal_cliente.php` - Dashboard
5. `cliente_contratos.php` - Contratos
6. `cliente_financeiro.php` - Financeiro
7. `cliente_helpdesk.php` - Helpdesk
8. `cliente_dados.php` - Dados/Senha
9. `criar_usuario_cliente.php` - Admin

**Atualizados (2)**:
1. `login.php` - Login com suporte a clientes
2. `header.php` - Menu atualizado

**SQL (1)**:
1. `database_update_portal_cliente.sql` - Atualização do banco

---

## 🎨 Design

- **Cor Principal**: Verde (#10b981)
- **Layout**: Moderno e responsivo
- **Mobile**: 100% adaptado
- **Navegação**: Menu fixo no topo

---

## 🐛 Problemas Comuns

### Cliente não consegue fazer login

**Solução**:
1. Verifique se o usuário foi criado em "Criar Usuário Cliente"
2. Use apenas números do CNPJ (sem pontos/traços)
3. Senha é 123 (case-sensitive)

### Cliente não vê seus dados

**Solução**:
1. Verifique se o `cliente_id` está correto no usuário
2. Verifique se contratos/contas têm o `cliente_id` correto

### Erro ao criar usuário

**Solução**:
1. Verifique se o cliente já tem usuário
2. Verifique se o CNPJ está preenchido no cadastro do cliente
3. Execute o SQL de atualização do banco

---

## 📞 Suporte

Para problemas técnicos, consulte a documentação completa em:
- `PORTAL_CLIENTE_V6.md` (300+ linhas de documentação)

---

## ✅ Checklist de Instalação

- [ ] Upload dos arquivos feito
- [ ] SQL executado no phpMyAdmin
- [ ] Usuário cliente criado
- [ ] Login testado com CNPJ
- [ ] Dashboard carregou corretamente
- [ ] Contratos aparecem (se houver)
- [ ] Financeiro aparece (se houver)
- [ ] Helpdesk funciona
- [ ] Alteração de senha funciona

---

## 🎉 Pronto!

O Portal do Cliente está **100% funcional**!

**Benefícios**:
- ✅ Clientes acessam informações 24/7
- ✅ Redução de chamados telefônicos
- ✅ Transparência total
- ✅ Profissionalismo

---

**Versão**: 6.0  
**Data**: 22/12/2025  
**Sistema**: ERP INLAUDO  
**Status**: ✅ Pronto para Uso
