# 🚀 Integração Mercado Pago + NF - Versão 7.0

## 📋 Resumo

Sistema completo de pagamentos online integrado com **Mercado Pago** e **CORA**, com geração automática de boletos, redirecionamento para checkout, webhooks para atualização de status e sistema de upload de Notas Fiscais.

---

## 🎯 Funcionalidades Implementadas

### 1. **Integração Mercado Pago**
- Configuração de credenciais (Public Key e Access Token)
- Criação de preferências de pagamento
- Checkout transparente com redirecionamento
- Suporte a múltiplos métodos: boleto, PIX, cartão
- Webhooks para atualização automática de status

### 2. **Seleção de Gateway em Contratos**
- Campo "Gateway de Pagamento" no cadastro de contratos
- Opções: CORA, Mercado Pago, Stripe
- Seleção automática baseada na forma de pagamento:
  - **Boleto** → CORA (padrão)
  - **Cartão/PIX** → Mercado Pago (padrão)

### 3. **Geração de Pagamento no Portal do Cliente**
- Botão "Pagar" em contratos e contas a receber
- **CORA**: Gera boleto registrado automaticamente
- **Mercado Pago**: Redireciona para checkout

### 4. **Sistema de Webhooks**
- Recebimento automático de notificações
- Atualização de status em tempo real
- Logs detalhados de todas as transações
- Processamento assíncrono

### 5. **Upload de Notas Fiscais**
- Aba "NF" em contas a receber
- Upload de arquivos PDF, XML, JPG, PNG
- Campos: número, data de emissão, valor
- Download disponível para clientes no portal

---

## 📊 Estrutura do Banco de Dados

### Novas Tabelas

#### `integracoes_pagamento`
```sql
- id (PK)
- gateway (cora, mercadopago, stripe)
- ativo (0/1)
- mp_public_key
- mp_access_token
- mp_webhook_url
- ambiente (teste/producao)
```

#### `transacoes_pagamento`
```sql
- id (PK)
- contrato_id (FK)
- conta_receber_id (FK)
- gateway
- transaction_id
- payment_id
- valor
- status (pending, approved, rejected, cancelled, refunded)
- metodo_pagamento
- payment_url
- boleto_url
- qr_code
- pagador_nome/email/documento
- data_vencimento
- data_pagamento
- response_json
```

#### `webhooks_pagamento`
```sql
- id (PK)
- gateway
- evento
- transaction_id
- payload (JSON)
- headers
- processado (0/1)
- data_processamento
- erro
- ip_origem
- data_recebimento
```

### Campos Adicionados

#### `contratos`
```sql
- gateway_pagamento (cora, mercadopago, stripe)
- link_pagamento
- payment_id
- status_pagamento (pendente, pago, cancelado, expirado)
```

#### `contas_receber`
```sql
- nf_numero
- nf_arquivo
- nf_data_emissao
- nf_valor
```

---

## 🔧 Arquivos Criados

### Backend
1. **lib_mercadopago.php** - Biblioteca de integração
2. **integracao_mercadopago.php** - Página de configuração
3. **gerar_pagamento.php** - Geração de pagamentos
4. **webhook_mercadopago.php** - Receptor de webhooks
5. **conta_receber_nf.php** - Upload de NF

### Banco de Dados
6. **database_update_mercadopago.sql** - Script de atualização

### Documentação
7. **INTEGRACAO_MERCADOPAGO_V7.md** - Este arquivo

---

## 🚀 Instalação

### Passo 1: Atualizar Banco de Dados

```bash
# Acessar phpMyAdmin
# Selecionar banco: inlaud99_erpinlaudo
# Importar arquivo: database_update_mercadopago.sql
```

### Passo 2: Upload dos Arquivos

Fazer upload de todos os arquivos para o servidor via FTP/cPanel.

### Passo 3: Configurar Permissões

```bash
chmod 755 uploads/nf
```

### Passo 4: Configurar Mercado Pago

1. Acessar: **Integrações > Mercado Pago**
2. Obter credenciais em: https://www.mercadopago.com.br/developers/panel
3. Colar Public Key e Access Token
4. Configurar webhook no painel do Mercado Pago
5. Marcar "Integração Ativa"
6. Salvar

---

## 💳 Como Usar

### Para Administradores

#### 1. Criar Contrato com Gateway

1. Acessar **Produtos > Contratos**
2. Clicar em "Novo Contrato"
3. Preencher dados do contrato
4. Selecionar **Forma de Pagamento**:
   - Boleto → Gateway CORA (automático)
   - Cartão/PIX → Gateway Mercado Pago (automático)
5. Salvar

#### 2. Upload de Nota Fiscal

1. Acessar **Financeiro > Contas a Receber**
2. Clicar no ícone "📄 NF" na conta desejada
3. Preencher número, data e valor da NF
4. Fazer upload do arquivo (PDF, XML, JPG, PNG)
5. Salvar

### Para Clientes (Portal)

#### 1. Visualizar Contratos

1. Fazer login no Portal do Cliente
2. Acessar **Meus Contratos**
3. Ver detalhes de cada contrato

#### 2. Efetuar Pagamento

**Se gateway = CORA (Boleto)**:
1. Clicar em "💳 Pagar"
2. Boleto é gerado automaticamente
3. Visualizar/baixar boleto
4. Copiar linha digitável
5. Pagar no banco

**Se gateway = Mercado Pago**:
1. Clicar em "💳 Pagar"
2. Redireciona para checkout do Mercado Pago
3. Escolher método: boleto, PIX ou cartão
4. Completar pagamento
5. Retorna automaticamente ao portal

#### 3. Baixar Nota Fiscal

1. Acessar **Meu Financeiro**
2. Localizar conta com NF anexada
3. Clicar em "📄 Baixar NF"

---

## 🔄 Fluxo de Pagamento

### Mercado Pago

```
1. Cliente clica em "Pagar"
   ↓
2. Sistema cria preferência no Mercado Pago
   ↓
3. Cliente é redirecionado para checkout
   ↓
4. Cliente escolhe método e paga
   ↓
5. Mercado Pago envia webhook
   ↓
6. Sistema atualiza status automaticamente
   ↓
7. Conta marcada como "Paga"
```

### CORA (Boleto)

```
1. Cliente clica em "Pagar"
   ↓
2. Sistema gera boleto via API CORA
   ↓
3. Boleto exibido na tela
   ↓
4. Cliente paga no banco
   ↓
5. CORA envia webhook (quando configurado)
   ↓
6. Sistema atualiza status
   ↓
7. Conta marcada como "Paga"
```

---

## 🔐 Segurança

### Validações
- ✅ Cliente só acessa seus próprios dados
- ✅ Webhooks validados por IP e assinatura
- ✅ Transações registradas com logs completos
- ✅ Arquivos NF validados por extensão
- ✅ Upload limitado a 10MB

### Logs
- Todas as transações são registradas
- Webhooks salvos com payload completo
- Erros capturados e armazenados
- Auditoria completa disponível

---

## 📱 Responsividade

- ✅ Portal do cliente 100% responsivo
- ✅ Checkout Mercado Pago mobile-friendly
- ✅ Boletos visualizáveis em mobile
- ✅ Upload de NF funciona em mobile

---

## 🐛 Solução de Problemas

### Mercado Pago não funciona

**Verificar**:
1. ✅ Credenciais corretas?
2. ✅ Integração marcada como "Ativa"?
3. ✅ Webhook configurado no painel MP?
4. ✅ Ambiente correto (teste/produção)?

**Logs**: Verificar em `Integrações > Logs de Integração`

### Boleto CORA não gera

**Verificar**:
1. ✅ Certificados instalados?
2. ✅ Client-ID correto?
3. ✅ Integração CORA ativa?
4. ✅ Dados do cliente completos?

**Logs**: Verificar em `Integrações > Logs de Integração`

### Webhook não atualiza status

**Verificar**:
1. ✅ URL do webhook acessível?
2. ✅ Webhook configurado no gateway?
3. ✅ Firewall bloqueando?
4. ✅ Logs de webhook recebidos?

**Tabela**: `webhooks_pagamento` (campo `processado`)

### Upload de NF falha

**Verificar**:
1. ✅ Diretório `uploads/nf/` existe?
2. ✅ Permissões 755?
3. ✅ Arquivo menor que 10MB?
4. ✅ Extensão permitida (PDF, XML, JPG, PNG)?

---

## 📊 Estatísticas

### Arquivos
- **7 arquivos PHP** criados/atualizados
- **1 arquivo SQL** de atualização
- **3 novas tabelas** no banco
- **8 novos campos** em tabelas existentes

### Funcionalidades
- **2 gateways** de pagamento integrados
- **4 métodos** de pagamento suportados
- **Webhooks** automáticos
- **Upload** de NF com validação

---

## 🎯 Próximos Passos (Opcional)

1. **Integração Stripe** (já preparado no código)
2. **Parcelamento** no Mercado Pago
3. **Assinatura recorrente** automática
4. **Emissão de NF** automática via API
5. **Relatório de faturamento** por gateway
6. **Dashboard** de transações

---

## 📚 Referências

- [Documentação Mercado Pago](https://www.mercadopago.com.br/developers/pt/docs)
- [API CORA](https://developers.cora.com.br/)
- [Webhooks Mercado Pago](https://www.mercadopago.com.br/developers/pt/docs/your-integrations/notifications/webhooks)

---

## ✅ Checklist de Instalação

- [ ] Banco de dados atualizado
- [ ] Arquivos enviados ao servidor
- [ ] Permissões configuradas
- [ ] Mercado Pago configurado
- [ ] CORA configurado
- [ ] Webhook testado
- [ ] Pagamento teste realizado
- [ ] Upload de NF testado
- [ ] Portal do cliente testado

---

**Versão**: 7.0  
**Data**: 22/12/2025  
**Sistema**: ERP INLAUDO  
**Status**: ✅ Pronto para Produção
