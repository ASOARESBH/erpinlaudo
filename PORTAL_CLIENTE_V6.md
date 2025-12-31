# Portal do Cliente - Versão 6.0

## 📋 Resumo

O **Portal do Cliente** é uma área exclusiva onde os clientes da INLAUDO podem acessar informações sobre seus contratos, financeiro e abrir solicitações de suporte através do helpdesk.

---

## 🎯 Funcionalidades

### 1. **Login com CNPJ**
- Clientes fazem login usando o **CNPJ** (apenas números) como usuário
- Senha padrão: **123**
- Após primeiro acesso, o cliente pode alterar a senha

### 2. **Dashboard**
- Visão geral com cards de resumo:
  - Contratos ativos
  - Contas pendentes e valor total
  - Total de interações
  - Acesso rápido aos dados
- Informações da conta (razão social, CNPJ, e-mail, telefone)

### 3. **Meus Contratos**
- Visualização de todos os contratos (ativos e inativos)
- Informações detalhadas:
  - Descrição do contrato
  - Tipo (produto/serviço)
  - Valor total
  - Forma de pagamento
  - Recorrência
  - Data de criação
  - Download do contrato (se disponível)
- Análise financeira (CMV):
  - Valor bruto
  - Total de custos
  - Valor líquido
  - Margem percentual

### 4. **Meu Financeiro**
- Visualização de contas a receber
- Resumo financeiro:
  - Total pendente
  - Contas vencidas
  - Total pago
- Filtros por status e mês
- Tabela completa com:
  - Descrição
  - Data de vencimento
  - Valor
  - Forma de pagamento
  - Status (pendente/pago/cancelado)
  - Link para boleto (se disponível)
- Destaque visual para contas vencidas

### 5. **Helpdesk**
- Abertura de novas solicitações:
  - Data e hora
  - Forma de contato (telefone, e-mail, presencial, WhatsApp)
  - Descrição detalhada do problema/solicitação
  - Agendamento de próximo contato (opcional)
- Histórico completo de atendimentos:
  - Todas as interações anteriores
  - Data e hora de cada atendimento
  - Forma de contato utilizada
  - Descrição completa
  - Próximo contato agendado (se houver)

### 6. **Meus Dados**
- Visualização de informações da empresa:
  - Razão social
  - Nome fantasia
  - CNPJ/CPF
  - E-mail
  - Telefone
  - Tipo (lead/cliente)
  - Endereço
- **Alterar senha**:
  - Senha atual
  - Nova senha (mínimo 6 caracteres)
  - Confirmação de senha
- Dicas de segurança

---

## 🔧 Instalação

### Passo 1: Atualizar Banco de Dados

Execute o script SQL no phpMyAdmin:

```bash
database_update_portal_cliente.sql
```

Este script irá:
- Adicionar campo `tipo_usuario` na tabela `usuarios` (admin, usuario, cliente)
- Adicionar campo `cliente_id` na tabela `usuarios`
- Criar índices e foreign keys

### Passo 2: Fazer Upload dos Arquivos

Faça upload dos seguintes arquivos para o servidor:

**Arquivos do Portal do Cliente:**
- `auth_cliente.php` - Autenticação do cliente
- `header_cliente.php` - Header do portal
- `footer_cliente.php` - Footer do portal
- `portal_cliente.php` - Dashboard
- `cliente_contratos.php` - Página de contratos
- `cliente_financeiro.php` - Página financeira
- `cliente_helpdesk.php` - Página de helpdesk
- `cliente_dados.php` - Página de dados e alteração de senha

**Arquivos Atualizados:**
- `login.php` - Login com suporte a clientes
- `header.php` - Menu com link para criar usuário cliente

**Arquivos Administrativos:**
- `criar_usuario_cliente.php` - Criar usuários para clientes

### Passo 3: Criar Usuários para Clientes

1. Acesse como administrador
2. Vá em **Usuários > Criar Usuário Cliente**
3. Selecione os clientes que deseja criar usuário
4. Clique em "Criar Usuário"
5. O sistema criará automaticamente:
   - Login: CNPJ do cliente (apenas números)
   - Senha: 123

---

## 👤 Como Usar (Cliente)

### 1. Primeiro Acesso

1. Acesse a página de login do sistema
2. Digite o **CNPJ** (apenas números, sem pontos ou traços)
   - Exemplo: 12345678000190
3. Digite a senha: **123**
4. Clique em "Entrar"

### 2. Alterar Senha (Recomendado)

1. Após fazer login, vá em **Meus Dados**
2. Preencha:
   - Senha atual: 123
   - Nova senha: (escolha uma senha forte)
   - Confirmar nova senha
3. Clique em "Alterar Senha"

### 3. Navegar pelo Portal

Use o menu superior para acessar:
- **Início**: Dashboard com resumo
- **Meus Contratos**: Ver contratos ativos
- **Meu Financeiro**: Ver contas a pagar
- **Helpdesk**: Abrir solicitações
- **Meus Dados**: Alterar senha e ver informações

---

## 👨‍💼 Como Usar (Administrador)

### Criar Usuário para Cliente

1. Acesse **Usuários > Criar Usuário Cliente**
2. Veja a lista de clientes sem usuário
3. Clique em "Criar Usuário" no cliente desejado
4. Confirme a criação
5. Anote o login (CNPJ) e senha (123)
6. Informe o cliente das credenciais

### Gerenciar Contratos

1. Cadastre contratos em **Produtos > Contratos**
2. Selecione o cliente
3. Preencha os dados do contrato
4. Anexe o arquivo do contrato (opcional)
5. O contrato aparecerá automaticamente no portal do cliente

### Gerenciar Financeiro

1. Cadastre contas a receber em **Financeiro > Contas a Receber**
2. Selecione o cliente
3. Preencha os dados da conta
4. Se for boleto, gere automaticamente
5. A conta aparecerá automaticamente no portal do cliente

### Ver Solicitações do Helpdesk

1. Acesse **CRM > Interações**
2. Veja todas as interações, incluindo as criadas pelos clientes
3. Responda e agende próximos contatos
4. O histórico fica disponível para o cliente

---

## 🔐 Segurança

### Autenticação

- Senhas criptografadas com **bcrypt**
- Sessões com timeout de 30 minutos
- Verificação automática em todas as páginas
- Logout seguro com limpeza de sessão

### Isolamento de Dados

- Clientes veem **apenas seus próprios dados**
- Queries SQL com filtro por `cliente_id`
- Não é possível acessar dados de outros clientes
- Administradores veem todos os dados

### Validações

- Verificação de tipo de usuário (cliente vs admin)
- Proteção contra SQL injection (prepared statements)
- Sanitização de inputs
- Validação de formulários

---

## 🎨 Design

### Cores

- **Verde**: #10b981 (cor principal do portal do cliente)
- **Azul**: #3b82f6 (helpdesk)
- **Amarelo**: #f59e0b (financeiro)
- **Vermelho**: #dc2626 (vencidas/canceladas)
- **Cinza**: #64748b (textos secundários)

### Responsividade

- **Desktop**: Layout em grid com múltiplas colunas
- **Tablet**: Grid adaptativo
- **Mobile**: Layout em coluna única, menu vertical

### Componentes

- Cards com sombra e bordas arredondadas
- Tabelas responsivas com scroll horizontal
- Badges coloridos para status
- Formulários com validação visual
- Alertas coloridos para mensagens

---

## 📊 Estrutura do Banco de Dados

### Tabela: usuarios

```sql
- id (INT, PRIMARY KEY)
- nome (VARCHAR)
- email (VARCHAR) -- Para clientes, armazena o CNPJ
- senha (VARCHAR) -- Hash bcrypt
- nivel (ENUM: 'admin', 'usuario')
- tipo_usuario (ENUM: 'admin', 'usuario', 'cliente') -- NOVO
- cliente_id (INT, FOREIGN KEY) -- NOVO
- ativo (TINYINT)
- data_criacao (DATETIME)
- data_atualizacao (DATETIME)
- ultimo_acesso (DATETIME)
```

### Relacionamentos

- `usuarios.cliente_id` → `clientes.id`
- Um cliente pode ter apenas um usuário do tipo 'cliente'
- Administradores e usuários normais não têm `cliente_id`

---

## 🚀 Fluxo Completo

### 1. Cadastro de Cliente

```
Admin cadastra cliente em CRM > Clientes
↓
Admin cria usuário em Usuários > Criar Usuário Cliente
↓
Sistema gera login (CNPJ) e senha (123)
↓
Admin informa cliente das credenciais
```

### 2. Primeiro Acesso do Cliente

```
Cliente acessa login.php
↓
Digite CNPJ e senha 123
↓
Sistema redireciona para portal_cliente.php
↓
Cliente vê dashboard com resumo
↓
Cliente altera senha em Meus Dados
```

### 3. Uso Diário

```
Cliente faz login
↓
Navega entre as páginas:
  - Ver contratos
  - Consultar financeiro
  - Abrir solicitações
  - Baixar boletos
↓
Cliente faz logout
```

---

## ⚙️ Configurações

### Timeout de Sessão

Padrão: **30 minutos** (1800 segundos)

Para alterar, edite `auth_cliente.php`:

```php
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    // Altere 1800 para o valor desejado em segundos
}
```

### Senha Padrão

Padrão: **123**

Para alterar, edite `criar_usuario_cliente.php`:

```php
$senhaHash = password_hash('123', PASSWORD_BCRYPT);
// Altere '123' para a senha desejada
```

---

## 🐛 Solução de Problemas

### Cliente não consegue fazer login

**Verificações**:
1. ✅ Usuário foi criado? (Usuários > Criar Usuário Cliente)
2. ✅ CNPJ está correto? (apenas números)
3. ✅ Senha é 123?
4. ✅ Usuário está ativo no banco?
5. ✅ Campo `tipo_usuario` = 'cliente'?
6. ✅ Campo `cliente_id` está preenchido?

### Cliente não vê seus dados

**Verificações**:
1. ✅ Campo `cliente_id` está correto no usuário?
2. ✅ Cliente existe na tabela `clientes`?
3. ✅ Contratos/contas têm `cliente_id` correto?

### Erro ao criar usuário

**Verificações**:
1. ✅ Cliente já tem usuário? (não pode ter duplicado)
2. ✅ CNPJ está preenchido no cadastro do cliente?
3. ✅ Banco de dados foi atualizado com o script SQL?

---

## 📈 Estatísticas

**Arquivos Criados**: 9 novos arquivos  
**Arquivos Atualizados**: 2 arquivos  
**Linhas de Código**: ~1.800 linhas PHP  
**Páginas do Portal**: 5 páginas  
**Funcionalidades**: 6 principais  

---

## 🎉 Conclusão

O Portal do Cliente está **100% funcional** e pronto para uso!

**Benefícios**:
- ✅ Acesso self-service para clientes
- ✅ Redução de chamados telefônicos
- ✅ Transparência financeira
- ✅ Histórico completo de atendimentos
- ✅ Interface moderna e responsiva
- ✅ Segurança robusta

---

**Versão**: 6.0 (Portal do Cliente)  
**Data**: 22/12/2025  
**Sistema**: ERP INLAUDO  
**Status**: ✅ Pronto para Produção
