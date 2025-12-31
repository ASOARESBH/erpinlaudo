# 🔧 Correções V7.3.1 - Portal e Webhook

## 📋 Problemas Corrigidos

### Problema 1: Portal do Cliente Não Exibe Menus e Dados ✅

**Erro**: Página `portal_cliente.php` loga mas não mostra menus nem estatísticas

**Causa**: Funções `formatarMoeda()` e `formatarCNPJ()` não existiam (nomes incorretos)

**Solução**: Corrigido para usar `formatMoeda()` e `formatCNPJ()` (funções corretas do config.php)

**Arquivos Corrigidos**:
- `portal_cliente.php`
- `cliente_contratos.php`
- `cliente_contas_pagar.php`
- `cliente_pagar.php`

### Problema 2: Webhook Mercado Pago com Erro 500 ✅

**Erro**: Webhook retorna HTTP 500 Internal Server Error

**Causas Identificadas**:
1. Falta de validação de assinatura secreta
2. Erros não tratados adequadamente
3. Headers não capturados corretamente
4. Falta de logs de debug

**Solução**: Webhook completamente reescrito com:
- ✅ Validação de assinatura secreta (x-signature)
- ✅ Tratamento robusto de erros
- ✅ Logs detalhados
- ✅ Compatibilidade com ambiente de teste
- ✅ Resposta JSON padronizada

---

## 🆕 Funcionalidades Adicionadas

### 1. Validação de Assinatura Secreta

**Como Funciona**:
```
1. Mercado Pago envia header x-signature
2. Webhook extrai ts (timestamp) e v1 (hash)
3. Constrói manifest: "id:{payment_id};request-id:{request_id};ts:{ts};"
4. Gera hash HMAC-SHA256 com webhook_secret
5. Compara hash recebido com hash esperado
6. Se igual: Webhook válido ✅
7. Se diferente: Webhook rejeitado ❌
```

**Benefícios**:
- 🔒 Segurança contra webhooks falsos
- ✅ Validação de autenticidade
- 🛡️ Proteção contra ataques

### 2. Campo Assinatura Secreta Editável

**Página**: `integracao_mercadopago.php`

**Mudanças**:
- ✅ URL do webhook agora é editável
- ✅ Campo "Assinatura Secreta" adicionado
- ✅ Campo opcional (pode deixar em branco)
- ✅ Instruções de como obter

### 3. Logs Detalhados

**Localização**: `/logs/webhook_errors.log`

**O que é registrado**:
- ✅ Todos os erros do webhook
- ✅ Pagamentos aprovados
- ✅ Pagamentos rejeitados
- ✅ Assinaturas inválidas

---

## 🗄️ Alterações no Banco de Dados

### Script SQL: `ADD_WEBHOOK_SECRET.sql`

**Alterações**:
```sql
-- 1. Adicionar campo webhook_secret
ALTER TABLE integracoes_pagamento 
ADD COLUMN webhook_secret VARCHAR(500) NULL;

-- 2. Adicionar campo status_processamento
ALTER TABLE webhooks_pagamento 
ADD COLUMN status_processamento VARCHAR(50) NULL;

-- 3. Criar tabela configuracoes_gateway (compatibilidade)
CREATE TABLE configuracoes_gateway (...);

-- 4. Migrar dados
INSERT INTO configuracoes_gateway (...);
```

---

## 📝 Como Configurar

### Passo 1: Executar Script SQL

1. Acessar phpMyAdmin
2. Selecionar banco `inlaud99_erpinlaudo`
3. Ir na aba "SQL"
4. Copiar e colar conteúdo de `ADD_WEBHOOK_SECRET.sql`
5. Clicar em "Executar"
6. Verificar mensagem: "Script executado com sucesso!"

### Passo 2: Upload dos Arquivos

**Arquivos para Upload**:
1. `portal_cliente.php` (substituir)
2. `cliente_contratos.php` (substituir)
3. `cliente_contas_pagar.php` (substituir)
4. `cliente_pagar.php` (substituir)
5. `webhook_mercadopago.php` (substituir)
6. `integracao_mercadopago.php` (substituir)

### Passo 3: Criar Pasta de Logs

**Via FTP ou cPanel**:
```
/home/inlaud99/public_html/logs/
```

**Permissões**: 755 (rwxr-xr-x)

### Passo 4: Configurar Mercado Pago

#### 4.1. Obter Assinatura Secreta

1. Acessar: https://www.mercadopago.com.br/developers/panel/app
2. Selecionar sua aplicação
3. Ir em "Webhooks"
4. Clicar em "Configurar webhooks"
5. Copiar a **"Assinatura secreta"** (campo com ícone de olho)

#### 4.2. Configurar no ERP

1. Acessar: `https://erp.inlaudo.com.br/integracao_mercadopago.php`
2. Colar a assinatura secreta no campo "Assinatura Secreta"
3. Verificar URL do webhook: `https://erp.inlaudo.com.br/webhook_mercadopago.php`
4. Marcar "Integração Ativa"
5. Clicar em "Salvar Configuração"

#### 4.3. Testar Webhook no Mercado Pago

1. No painel do Mercado Pago, ir em "Webhooks"
2. Clicar em "Simular notificação"
3. Selecionar evento: `payment.updated`
4. Clicar em "Enviar teste"
5. Verificar resposta: **200 - OK** ✅

---

## 🧪 Testes

### Teste 1: Portal do Cliente

**Objetivo**: Verificar se menus e dados aparecem

**Passos**:
1. Acessar: `https://erp.inlaudo.com.br/login_cliente.php`
2. Digitar CNPJ de cliente com contrato
3. Fazer login
4. **Verificar**:
   - ✅ Estatísticas aparecem (contratos, contas)
   - ✅ Menu "Meus Contratos" aparece
   - ✅ Menu "Contas a Pagar" aparece
   - ✅ CNPJ formatado aparece no header

**Resultado Esperado**: Tudo aparece corretamente ✅

### Teste 2: Webhook Mercado Pago

**Objetivo**: Verificar se webhook processa pagamentos

**Passos**:
1. Configurar assinatura secreta no ERP
2. No painel do Mercado Pago, simular notificação
3. Verificar resposta: `200 - OK`
4. Verificar logs: `/logs/webhook_errors.log`

**Resultado Esperado**: 
- ✅ Resposta 200
- ✅ JSON de sucesso retornado
- ✅ Sem erros nos logs

### Teste 3: Pagamento Real (Ambiente de Teste)

**Objetivo**: Verificar fluxo completo de pagamento

**Passos**:
1. Configurar credenciais de **teste** no ERP
2. Criar conta a receber
3. Gerar link de pagamento (Mercado Pago)
4. Usar cartão de teste: `5031 4332 1540 6351`
5. CVV: `123`, Validade: `11/25`
6. Verificar se status muda para "pago"

**Resultado Esperado**:
- ✅ Link gerado
- ✅ Pagamento aprovado
- ✅ Webhook recebido
- ✅ Status atualizado para "pago"
- ✅ Data de pagamento registrada

---

## 🔍 Verificação de Logs

### Ver Logs do Webhook

**Via SSH**:
```bash
tail -f /home/inlaud99/public_html/logs/webhook_errors.log
```

**Via cPanel**:
1. File Manager
2. Navegar até `/logs/`
3. Abrir `webhook_errors.log`

### Logs no Banco de Dados

**Query para ver webhooks recebidos**:
```sql
SELECT * 
FROM webhooks_pagamento 
WHERE gateway = 'mercadopago' 
ORDER BY data_recebimento DESC 
LIMIT 10;
```

**Query para ver webhooks com erro**:
```sql
SELECT * 
FROM webhooks_pagamento 
WHERE gateway = 'mercadopago' 
  AND processado = 0 
ORDER BY data_recebimento DESC;
```

---

## 🐛 Solução de Problemas

### Erro: "Portal ainda não mostra dados"

**Possíveis Causas**:
1. Arquivos não foram substituídos
2. Cache do navegador

**Solução**:
1. Verificar se arquivos foram enviados
2. Limpar cache do navegador (Ctrl+Shift+Del)
3. Testar em aba anônima
4. Verificar logs de erro do PHP

### Erro: "Webhook ainda retorna 500"

**Possíveis Causas**:
1. Script SQL não foi executado
2. Pasta `/logs/` não existe
3. Credenciais incorretas

**Solução**:
1. Executar script SQL novamente
2. Criar pasta `/logs/` com permissão 755
3. Verificar credenciais no banco
4. Ver logs: `/logs/webhook_errors.log`

### Erro: "Assinatura inválida"

**Possíveis Causas**:
1. Assinatura secreta incorreta
2. Assinatura secreta de produção em ambiente de teste

**Solução**:
1. Copiar assinatura secreta novamente do painel MP
2. Verificar se está usando credenciais corretas (teste ou produção)
3. Deixar campo em branco para desabilitar validação (não recomendado)

### Erro: "Pagamento não atualiza status"

**Possíveis Causas**:
1. Webhook não está configurado no Mercado Pago
2. URL do webhook incorreta
3. Transação não foi registrada

**Solução**:
1. Verificar configuração do webhook no painel MP
2. Verificar URL: `https://erp.inlaudo.com.br/webhook_mercadopago.php`
3. Verificar tabela `transacoes_pagamento`
4. Ver logs do webhook

---

## 📊 Checklist de Instalação

### Banco de Dados
- [ ] Script `ADD_WEBHOOK_SECRET.sql` executado
- [ ] Campo `webhook_secret` adicionado
- [ ] Campo `status_processamento` adicionado
- [ ] Tabela `configuracoes_gateway` criada

### Arquivos
- [ ] `portal_cliente.php` substituído
- [ ] `cliente_contratos.php` substituído
- [ ] `cliente_contas_pagar.php` substituído
- [ ] `cliente_pagar.php` substituído
- [ ] `webhook_mercadopago.php` substituído
- [ ] `integracao_mercadopago.php` substituído

### Pasta de Logs
- [ ] Pasta `/logs/` criada
- [ ] Permissão 755 definida

### Configuração Mercado Pago
- [ ] Assinatura secreta copiada
- [ ] Assinatura secreta colada no ERP
- [ ] URL do webhook configurada
- [ ] Webhook testado no painel MP
- [ ] Resposta 200 recebida

### Testes
- [ ] Login no portal funciona
- [ ] Menus aparecem
- [ ] Estatísticas aparecem
- [ ] Webhook responde 200
- [ ] Pagamento de teste aprovado
- [ ] Status atualizado automaticamente

---

## 📈 Melhorias Implementadas

### Segurança
✅ Validação de assinatura secreta  
✅ Tratamento robusto de erros  
✅ Logs detalhados  
✅ Proteção contra webhooks falsos  

### Usabilidade
✅ URL do webhook editável  
✅ Campo assinatura secreta visível  
✅ Instruções claras  
✅ Mensagens de erro descritivas  

### Confiabilidade
✅ Erros não quebram o webhook  
✅ Logs para debug  
✅ Compatibilidade com teste e produção  
✅ Resposta JSON padronizada  

---

## 🎯 Próximos Passos

### Curto Prazo
1. Testar em produção
2. Monitorar logs
3. Validar pagamentos reais

### Médio Prazo
4. Adicionar retry automático de webhooks com erro
5. Dashboard de webhooks recebidos
6. Notificações de pagamento por e-mail

### Longo Prazo
7. Suporte a outros gateways
8. Webhooks assíncronos com fila
9. Relatório de transações

---

## 📞 Suporte

**Logs de Erro**: `/logs/webhook_errors.log`  
**Logs de Webhook**: Tabela `webhooks_pagamento`  
**Documentação**: Este arquivo

---

**Versão**: 7.3.1  
**Data**: 28/12/2025  
**Status**: ✅ Pronto para Produção  
**Arquivos**: 8 (6 PHP + 1 SQL + 1 doc)
