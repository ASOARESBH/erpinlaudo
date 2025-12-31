# 🌐 Portal do Cliente - Versão 7.3

## 📋 Visão Geral

Sistema completo de portal do cliente com login via CNPJ validado em contratos, visualização de contratos e contas a pagar com integração de pagamento online.

---

## 🎯 Funcionalidades Implementadas

### 1. Login Simplificado via CNPJ

**Página**: `login_cliente.php`

**Características**:
- ✅ Login apenas com CNPJ (sem senha)
- ✅ Validação automática em contratos
- ✅ Cliente deve ter pelo menos 1 contrato cadastrado
- ✅ Formatação automática do CNPJ
- ✅ Mensagens de erro claras
- ✅ Design moderno e responsivo

**Lógica de Validação**:
```sql
SELECT DISTINCT c.*, 
       COUNT(ct.id) as total_contratos,
       SUM(CASE WHEN ct.status = 'ativo' THEN 1 ELSE 0 END) as contratos_ativos
FROM clientes c
INNER JOIN contratos ct ON c.id = ct.cliente_id
WHERE c.cnpj_cpf = ?
GROUP BY c.id
HAVING total_contratos > 0
```

### 2. Portal Principal

**Página**: `portal_cliente.php`

**Seções**:
- 📊 **Dashboard**: Estatísticas em tempo real
  - Contratos ativos
  - Total de contratos
  - Contas pendentes
  - Contas vencidas
- 🎯 **Menu Principal**: 2 opções
  - Meus Contratos
  - Contas a Pagar

### 3. Meus Contratos

**Página**: `cliente_contratos.php`

**Funcionalidades**:
- ✅ Lista todos os contratos (ativos e inativos)
- ✅ Informações completas:
  - Descrição
  - Valor Total
  - Forma de Pagamento
  - Parcelas
  - Status (Ativo, Suspenso, Cancelado, Finalizado)
  - Período (data início e fim)
  - Recorrência
  - Observações
- ✅ Visualização de contrato anexo (PDF)
- ✅ Download de contrato
- ✅ Design com cores por status

### 4. Contas a Pagar

**Página**: `cliente_contas_pagar.php`

**Funcionalidades**:
- ✅ Lista todas as contas a receber (da INLAUDO)
- ✅ Estatísticas:
  - Total pendente
  - Total vencido
  - Total pago
- ✅ Filtros por status
- ✅ Informações da conta:
  - Descrição
  - Valor
  - Vencimento
  - Forma de Pagamento
  - Status
  - Parcela
- ✅ Botão "Pagar" para contas pendentes
- ✅ Botão "NF" para contas pagas (futuro)
- ✅ Destaque visual para contas vencidas

### 5. Realizar Pagamento

**Página**: `cliente_pagar.php`

**Funcionalidades**:
- ✅ Informações detalhadas da conta
- ✅ Seleção de gateway de pagamento:
  - **Mercado Pago**: PIX, Boleto, Cartão
  - **CORA Banking**: Boleto
- ✅ Design moderno com cards selecionáveis
- ✅ Informações de segurança
- ✅ Redirecionamento para gateway

### 6. Integração com Gateways

**Funcionalidades**:
- ✅ Geração automática de link de pagamento
- ✅ Webhook do Mercado Pago já configurado
- ✅ Atualização automática de status:
  - Pagamento aprovado → Status "pago"
  - Data de pagamento registrada
  - Logs completos
- ✅ Botão NF habilitado após pagamento

---

## 🗄️ Estrutura de Arquivos

### Arquivos Novos/Atualizados

1. **login_cliente.php** (ATUALIZADO)
   - Login via CNPJ
   - Validação em contratos
   
2. **verifica_sessao_cliente.php** (NOVO)
   - Verificação de sessão
   - Timeout de 30 minutos
   
3. **portal_cliente.php** (ATUALIZADO)
   - Dashboard simplificado
   - Menu com 2 opções
   
4. **cliente_contratos.php** (ATUALIZADO)
   - Lista completa de contratos
   - Visualização e download
   
5. **cliente_contas_pagar.php** (NOVO)
   - Lista de contas a pagar
   - Botão de pagamento
   
6. **cliente_pagar.php** (NOVO)
   - Seleção de gateway
   - Informações de pagamento
   
7. **logout_cliente.php** (NOVO)
   - Logout seguro

---

## 🔐 Segurança

### Controle de Acesso

**Validação de Login**:
- CNPJ deve existir na tabela `clientes`
- CNPJ deve ter pelo menos 1 contrato
- Sessão criada com dados do cliente

**Proteção de Páginas**:
- Todas as páginas incluem `verifica_sessao_cliente.php`
- Verificação de sessão ativa
- Timeout de 30 minutos de inatividade
- Redirecionamento automático para login

**Validação de Dados**:
- Cliente só acessa seus próprios contratos
- Cliente só acessa suas próprias contas
- Verificação de propriedade em todas as queries

### Variáveis de Sessão

```php
$_SESSION['cliente_logado'] = true;
$_SESSION['cliente_id'] = $cliente['id'];
$_SESSION['cliente_nome'] = $cliente['nome'];
$_SESSION['cliente_cnpj'] = $cliente['cnpj_cpf'];
$_SESSION['cliente_email'] = $cliente['email'];
$_SESSION['cliente_tipo_pessoa'] = $cliente['tipo_pessoa'];
$_SESSION['login_time'] = time();
$_SESSION['ultimo_acesso'] = time();
```

---

## 🎨 Interface

### Design System

**Cores Principais**:
- **Verde**: #10b981 (Primário, Ativo, Sucesso)
- **Azul**: #3b82f6 (Informação, Links)
- **Amarelo**: #f59e0b (Atenção, Pendente)
- **Vermelho**: #ef4444 (Erro, Vencido)
- **Cinza**: #64748b (Secundário, Texto)

**Componentes**:
- Cards com sombra e hover
- Badges coloridos por status
- Botões com transição suave
- Grid responsivo
- Tabelas estilizadas

### Responsividade

**Breakpoints**:
- Desktop: > 768px (grid completo)
- Tablet: 768px (grid adaptado)
- Mobile: < 768px (coluna única)

---

## 📊 Fluxo Completo

### Fluxo de Login

```
1. Cliente acessa login_cliente.php
   ↓
2. Cliente digita CNPJ
   ↓
3. Sistema valida em contratos
   ↓
4. Se válido: Cria sessão e redireciona para portal
   ↓
5. Se inválido: Mostra mensagem de erro
```

### Fluxo de Pagamento

```
1. Cliente acessa "Contas a Pagar"
   ↓
2. Cliente clica em "Pagar" na conta
   ↓
3. Sistema mostra página de pagamento
   ↓
4. Cliente seleciona gateway (Mercado Pago ou CORA)
   ↓
5. Sistema gera link de pagamento
   ↓
6. Cliente é redirecionado para gateway
   ↓
7. Cliente realiza pagamento
   ↓
8. Gateway envia webhook
   ↓
9. Sistema atualiza status para "pago"
   ↓
10. Botão "NF" é habilitado
   ↓
✅ Pagamento concluído
```

---

## 🔄 Integração com Gateways

### Mercado Pago

**Arquivo**: `gerar_link_pagamento.php`

**Processo**:
1. Recebe `conta_id` e `gateway=mercadopago`
2. Cria preferência de pagamento
3. Registra transação
4. Retorna link de checkout

**Webhook**: `webhook_mercadopago.php`
- Recebe notificação de pagamento
- Consulta API do Mercado Pago
- Atualiza status da transação
- Atualiza status da conta para "pago"
- Registra data de pagamento

### CORA Banking

**Arquivo**: `gerar_link_pagamento.php`

**Processo**:
1. Recebe `conta_id` e `gateway=cora`
2. Gera boleto via API CORA
3. Registra transação
4. Retorna dados do boleto

---

## 📝 Exemplos de Uso

### Exemplo 1: Cliente Fazendo Login

**Cenário**: Cliente com CNPJ 12.345.678/0001-90

**Passo a Passo**:
1. Cliente acessa `login_cliente.php`
2. Digite CNPJ: `12.345.678/0001-90`
3. Sistema valida:
   - CNPJ existe? ✅
   - Tem contrato? ✅
4. Login realizado com sucesso
5. Redirecionado para `portal_cliente.php`

### Exemplo 2: Visualizando Contratos

**Cenário**: Cliente quer ver seus contratos

**Passo a Passo**:
1. No portal, clicar em "Meus Contratos"
2. Sistema lista todos os contratos:
   - Contrato #1: Ativo (verde)
   - Contrato #2: Finalizado (cinza)
3. Cliente clica em "Visualizar Contrato"
4. PDF abre em nova aba
5. Cliente pode baixar o PDF

### Exemplo 3: Pagando uma Conta

**Cenário**: Cliente tem conta de R$ 1.500,00 vencendo em 5 dias

**Passo a Passo**:
1. No portal, clicar em "Contas a Pagar"
2. Localizar conta de R$ 1.500,00
3. Clicar em "💳 Pagar"
4. Selecionar "Mercado Pago"
5. Clicar em "Prosseguir para Pagamento"
6. Sistema gera link e redireciona
7. Cliente escolhe PIX
8. Cliente paga via PIX
9. Mercado Pago envia webhook
10. Sistema atualiza status para "pago"
11. Cliente vê status "✓ Pago" na lista

---

## 🐛 Solução de Problemas

### Erro: "CNPJ não encontrado"

**Causa**: CNPJ não existe na tabela `clientes`

**Solução**:
1. Verificar se CNPJ está cadastrado
2. Verificar formatação (com ou sem pontos)
3. Cadastrar cliente se necessário

### Erro: "Não há contratos cadastrados"

**Causa**: CNPJ existe mas não tem contrato

**Solução**:
1. Verificar tabela `contratos`
2. Criar contrato para o cliente
3. Tentar login novamente

### Erro: "Sessão expirada"

**Causa**: 30 minutos de inatividade

**Solução**:
1. Fazer login novamente
2. Sessão será renovada

### Conta não atualiza após pagamento

**Possíveis Causas**:
- Webhook não configurado
- Webhook com erro
- Transação não registrada

**Solução**:
1. Verificar configuração do webhook no Mercado Pago
2. Verificar logs em `webhooks_pagamento`
3. Verificar logs em `logs_sistema`
4. Atualizar manualmente se necessário

---

## ✅ Checklist de Instalação

### Banco de Dados
- [ ] Tabela `clientes` existe
- [ ] Tabela `contratos` existe
- [ ] Tabela `contas_receber` existe
- [ ] Tabela `transacoes_pagamento` existe
- [ ] Tabela `webhooks_pagamento` existe

### Arquivos
- [ ] Upload de `login_cliente.php`
- [ ] Upload de `verifica_sessao_cliente.php`
- [ ] Upload de `portal_cliente.php`
- [ ] Upload de `cliente_contratos.php`
- [ ] Upload de `cliente_contas_pagar.php`
- [ ] Upload de `cliente_pagar.php`
- [ ] Upload de `logout_cliente.php`

### Configurações
- [ ] Webhook Mercado Pago configurado
- [ ] Credenciais Mercado Pago válidas
- [ ] Credenciais CORA válidas
- [ ] Logo LOGOBRANCA.png no lugar

### Testes
- [ ] Teste de login com CNPJ válido
- [ ] Teste de login com CNPJ inválido
- [ ] Teste de visualização de contratos
- [ ] Teste de download de contrato
- [ ] Teste de listagem de contas
- [ ] Teste de seleção de gateway
- [ ] Teste de pagamento Mercado Pago
- [ ] Teste de webhook
- [ ] Teste de atualização de status
- [ ] Teste de logout

---

## 📈 Benefícios

### Para a Empresa

✅ **Automação**: Pagamentos processados automaticamente  
✅ **Redução de Custos**: Menos trabalho manual  
✅ **Profissionalismo**: Portal moderno e funcional  
✅ **Rastreabilidade**: Logs completos de todas as ações  
✅ **Escalabilidade**: Suporta múltiplos clientes  

### Para os Clientes

✅ **Facilidade**: Login apenas com CNPJ  
✅ **Transparência**: Acesso a contratos e contas  
✅ **Conveniência**: Pagamento online 24/7  
✅ **Segurança**: Gateways certificados  
✅ **Praticidade**: Tudo em um só lugar  

---

## 🔄 Diferenças da Versão Anterior

| Característica | Versão Antiga | Versão 7.3 |
|----------------|---------------|------------|
| Login | E-mail + Senha | Apenas CNPJ |
| Validação | Tabela `usuarios` | Tabela `contratos` |
| Menu | 4 opções | 2 opções (simplificado) |
| Contratos | Básico | Completo com anexos |
| Contas | Visualização | Visualização + Pagamento |
| Pagamento | Não tinha | Integrado com gateways |
| Status | Manual | Automático via webhook |
| Design | Básico | Moderno e responsivo |

---

## 🎯 Próximos Passos Sugeridos

### Curto Prazo
1. Testar em produção
2. Treinar clientes
3. Monitorar webhooks

### Médio Prazo
4. Implementar geração de NF automática
5. Adicionar histórico de pagamentos
6. Enviar e-mail de confirmação

### Longo Prazo
7. App mobile
8. Chat de suporte
9. Notificações push

---

## 📞 Suporte

**E-mail**: financeiro@inlaudo.com.br  
**Documentação**: Este arquivo  
**Logs**: Verificar tabelas `logs_sistema` e `webhooks_pagamento`

---

**Versão**: 7.3  
**Data**: 22/12/2025  
**Status**: ✅ Pronto para Produção  
**Arquivos**: 7 (4 novos + 3 atualizados)
