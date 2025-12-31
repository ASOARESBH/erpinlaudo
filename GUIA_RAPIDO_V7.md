# 🚀 Guia Rápido - Integração Mercado Pago V7.0

## 📦 O Que Foi Implementado

✅ **Integração Mercado Pago** completa  
✅ **Seleção de gateway** em contratos (CORA/Mercado Pago/Stripe)  
✅ **Geração automática** de pagamentos no portal do cliente  
✅ **Webhooks** para atualização de status  
✅ **Upload de Notas Fiscais** em contas a receber  

---

## ⚡ Instalação em 5 Passos

### 1. Atualizar Banco de Dados
```
- Acessar phpMyAdmin
- Selecionar banco: inlaud99_erpinlaudo
- Importar: database_update_mercadopago.sql
- Aguardar conclusão
```

### 2. Upload dos Arquivos
```
Fazer upload de todos os arquivos do ZIP para o servidor
```

### 3. Configurar Mercado Pago
```
1. Acessar: Integrações > Mercado Pago
2. Obter credenciais em: https://www.mercadopago.com.br/developers/panel
3. Colar Public Key e Access Token
4. Marcar "Integração Ativa"
5. Salvar
```

### 4. Configurar Webhook
```
1. No painel do Mercado Pago, ir em "Webhooks"
2. Criar webhook com URL: https://seudominio.com/webhook_mercadopago.php
3. Selecionar eventos: Pagamentos
4. Salvar
```

### 5. Testar
```
1. Criar contrato com gateway "Mercado Pago"
2. Acessar portal do cliente
3. Clicar em "Pagar"
4. Completar pagamento teste
5. Verificar atualização de status
```

---

## 💡 Como Funciona

### Para Boleto (CORA)
```
Cliente clica "Pagar" → Boleto gerado → Cliente paga no banco
```

### Para Mercado Pago
```
Cliente clica "Pagar" → Redireciona para MP → Cliente escolhe método → Paga → Retorna ao portal
```

### Upload de NF
```
Admin acessa Contas a Receber → Clica "📄 NF" → Faz upload → Cliente baixa no portal
```

---

## 🔧 Arquivos no Pacote

1. **database_update_mercadopago.sql** - Atualização do banco
2. **lib_mercadopago.php** - Biblioteca de integração
3. **integracao_mercadopago.php** - Página de configuração
4. **gerar_pagamento.php** - Geração de pagamentos
5. **webhook_mercadopago.php** - Receptor de webhooks
6. **conta_receber_nf.php** - Upload de NF
7. **contrato_form.php** - Formulário atualizado
8. **INTEGRACAO_MERCADOPAGO_V7.md** - Documentação completa

---

## 📊 Novidades no Sistema

### Contratos
- Campo "Gateway de Pagamento"
- Seleção automática baseada na forma de pagamento
- Link de pagamento gerado automaticamente

### Portal do Cliente
- Botão "Pagar" em contratos e contas
- Redirecionamento para Mercado Pago
- Geração de boleto CORA
- Download de Notas Fiscais

### Contas a Receber
- Aba "NF" para upload
- Campos: número, data, valor, arquivo
- Download disponível para clientes

### Integrações
- Nova página "Mercado Pago"
- Configuração de credenciais
- Webhook automático
- Logs detalhados

---

## ✅ Checklist

- [ ] Banco atualizado
- [ ] Arquivos enviados
- [ ] Mercado Pago configurado
- [ ] Webhook configurado
- [ ] Teste realizado
- [ ] NF testada

---

## 🐛 Problemas Comuns

### Pagamento não funciona
→ Verificar credenciais e integração ativa

### Webhook não atualiza
→ Verificar URL configurada no Mercado Pago

### Upload de NF falha
→ Verificar permissões do diretório uploads/nf/

---

## 📚 Documentação Completa

Ver arquivo: **INTEGRACAO_MERCADOPAGO_V7.md**

---

**Versão**: 7.0  
**Data**: 22/12/2025  
**Status**: ✅ Pronto
