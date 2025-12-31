#  🚀 Instalação Rápida - Sistema de Faturamento V7.1

## 📦 Arquivos Incluídos

1. **faturamento_completo.php** - Página principal de faturamento

1. **gerar_link_pagamento.php** - Geração de links de pagamento

1. **enviar_link_pagamento.php** - Envio de links por e-mail

1. **webhook_mercadopago.php** - Processamento de webhooks (ATUALIZADO)

---

## ⚡ Instalação em 5 Passos

### Passo 1: Upload dos Arquivos

```bash
# Fazer upload dos 4 arquivos para a pasta raiz do ERP
# Via FTP/FileZilla ou cPanel File Manager
```

### Passo 2: Adicionar Link no Menu

Editar arquivo **header.php** e adicionar após a linha do menu "Mercado Pago":

```php
<a href="faturamento_completo.php" class="nav-link">
    <i class="fas fa-file-invoice-dollar"></i> Faturamento
</a>
```

### Passo 3: Configurar Webhook no Mercado Pago

1. Acessar: [https://www.mercadopago.com.br/developers/panel/app](https://www.mercadopago.com.br/developers/panel/app)

1. Selecionar sua aplicação

1. Ir em "Webhooks"

1. Clicar em "Configurar notificações"

1. Adicionar URL: `https://seudominio.com/webhook_mercadopago.php`

1. Selecionar eventos: **Pagamentos**

1. Salvar

### Passo 4: Testar Geração de Link

1. Acessar: Menu > Faturamento

1. Clicar em "🔗 Gerar Link" em qualquer fatura

1. Verificar se link foi gerado

1. Copiar e testar link no navegador

### Passo 5: Testar Envio de E-mail

1. Após gerar link, clicar em "📧 Enviar"

1. Verificar e-mail do cliente

1. Clicar em "Enviar E-mail"

1. Verificar recebimento no e-mail do cliente

---

## ✅ Checklist de Verificação

- [ ] 4 arquivos enviados ao servidor

- [ ] Link "Faturamento" adicionado ao menu

- [ ] Webhook configurado no Mercado Pago

- [ ] Teste de geração de link realizado

- [ ] Teste de envio de e-mail realizado

- [ ] E-mail recebido pelo cliente

---

## 🔧 Configurações Necessárias

### Mercado Pago

- Public Key configurada

- Access Token configurado

- Integração marcada como ativa

### E-mail

- Servidor SMTP configurado

- Templates de e-mail criados

- Teste de envio realizado

---

## 🐛 Problemas Comuns

### Erro: "Integração não configurada"

**Solução**: Acessar Menu > Integrações > Mercado Pago e configurar credenciais

### Erro: "Cliente não encontrado"

**Solução**: Verificar se conta a receber tem cliente_id válido

### Erro: "Erro ao enviar e-mail"

**Solução**: Verificar configuração de e-mail em Menu > E-mail > Configuração

### Webhook não atualiza status

**Solução**: Verificar se URL do webhook está correta no painel do Mercado Pago

---

## 📞 Suporte

Em caso de dúvidas, consultar:

- **Documentação Completa**: FATURAMENTO_V7.1.md

- **Logs do Sistema**: Menu > Integrações > Logs

- **Webhooks Recebidos**: Tabela `webhooks_pagamento`

---

**Versão**: 7.1**Data**: 22/12/2025**Tempo de Instalação**: ~10 minutos

