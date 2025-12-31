# 📋 Processador de Webhooks Mercado Pago - Documentação

## 🎯 Objetivo

Script desacoplado para conciliação financeira de pagamentos Mercado Pago.

**Função**: Processar webhooks recebidos e atualizar automaticamente o status das contas a receber.

---

## 🏗️ Arquitetura

### Fluxo de Processamento

```
Webhook Recebido (webhook_mercadopago.php)
    ↓
Salvo em webhooks_pagamento (processado = 0)
    ↓
CRON executa processar_webhooks_mercadopago.php
    ↓
Lê webhooks pendentes (processado = 0)
    ↓
Para cada webhook:
    1. Consulta API do Mercado Pago
    2. Verifica se status = 'approved'
    3. Extrai external_reference (conta_id)
    4. Atualiza contas_receber (status = 'pago')
    5. Marca webhook como processado
    ↓
Logs gravados em /logs/processar_webhooks.log
```

### Desacoplamento

✅ **Webhook** (webhook_mercadopago.php):
- Apenas recebe e salva dados
- Sempre retorna 200 OK
- Não processa pagamentos

✅ **Processador** (processar_webhooks_mercadopago.php):
- Lê dados salvos
- Processa em lote
- Executa conciliação financeira
- Pode ser executado múltiplas vezes (idempotente)

---

## 📁 Arquivo: processar_webhooks_mercadopago.php

### Características

✅ **Seguro para CRON**:
- Sem output HTML
- Logs em arquivo
- Não gera erro 500
- Exit codes corretos (0 = sucesso, 1 = erro)

✅ **Idempotente**:
- Não marca pagamento duas vezes
- Verifica se conta já está paga antes de atualizar
- Seguro para reprocessamento

✅ **Robusto**:
- Tratamento completo de erros
- Timeout configurado (30s)
- Retry automático via CRON
- Logs detalhados

✅ **Performático**:
- Processa em lote (20 por vez)
- Delay entre requisições (100ms)
- Queries otimizadas
- Índices no banco

---

## 🔧 Instalação

### Passo 1: Upload do Arquivo

Fazer upload de `processar_webhooks_mercadopago.php` para a raiz do ERP:

```
/home/inlaud99/public_html/processar_webhooks_mercadopago.php
```

**Permissões**: 644

### Passo 2: Criar Pasta de Logs

```bash
mkdir -p /home/inlaud99/public_html/logs
chmod 755 /home/inlaud99/public_html/logs
```

### Passo 3: Testar Manualmente

**Via SSH**:
```bash
cd /home/inlaud99/public_html
php processar_webhooks_mercadopago.php
```

**Via Browser** (apenas para teste inicial):
```
https://erp.inlaudo.com.br/processar_webhooks_mercadopago.php
```

**Verificar logs**:
```bash
tail -f /home/inlaud99/public_html/logs/processar_webhooks.log
```

### Passo 4: Configurar CRON

**Acessar cPanel**:
1. Login no cPanel da HostGator
2. Buscar "Cron Jobs"
3. Adicionar novo CRON

**Configuração Recomendada** (executar a cada 1 minuto):

```
* * * * * /usr/bin/php /home/inlaud99/public_html/processar_webhooks_mercadopago.php >/dev/null 2>&1
```

**Configuração Alternativa** (executar a cada 5 minutos):

```
*/5 * * * * /usr/bin/php /home/inlaud99/public_html/processar_webhooks_mercadopago.php >/dev/null 2>&1
```

**Explicação**:
- `* * * * *` = A cada 1 minuto
- `*/5 * * * *` = A cada 5 minutos
- `/usr/bin/php` = Caminho do PHP (pode variar, verificar com `which php`)
- `/home/inlaud99/public_html/processar_webhooks_mercadopago.php` = Caminho completo do script
- `>/dev/null 2>&1` = Redireciona output para /dev/null (silencioso)

**Verificar caminho do PHP**:
```bash
which php
# Ou
which php-cli
```

---

## 📊 Configuração

### Constantes Configuráveis

No arquivo `processar_webhooks_mercadopago.php`:

```php
define('BATCH_SIZE', 20);        // Webhooks processados por execução
define('LOG_FILE', __DIR__ . '/logs/processar_webhooks.log');
define('MAX_RETRIES', 3);        // Máximo de tentativas (futuro)
```

**Ajustar BATCH_SIZE**:
- **10-20**: Ideal para servidores compartilhados
- **50-100**: Para servidores dedicados
- **200+**: Para alto volume (VPS/Cloud)

---

## 🔍 Monitoramento

### Verificar Logs

**Via SSH**:
```bash
tail -f /home/inlaud99/public_html/logs/processar_webhooks.log
```

**Via cPanel File Manager**:
1. Navegar até `/logs/`
2. Abrir `processar_webhooks.log`

### Exemplo de Log (Sucesso)

```
[2025-12-29 21:30:00] [INFO] ========== INICIANDO PROCESSAMENTO DE WEBHOOKS ==========
[2025-12-29 21:30:00] [INFO] Conexão com banco estabelecida
[2025-12-29 21:30:00] [INFO] Access token obtido
[2025-12-29 21:30:00] [INFO] Encontrados 3 webhook(s) pendente(s)
[2025-12-29 21:30:00] [INFO] Processando webhook #45 (transaction: 123456789)
[2025-12-29 21:30:01] [INFO] Transaction 123456789: status=approved, external_ref=conta_33
[2025-12-29 21:30:01] [SUCCESS] Conta 33 marcada como PAGA (transaction: 123456789)
[2025-12-29 21:30:01] [SUCCESS] Webhook #45 processado com SUCESSO
[2025-12-29 21:30:01] [INFO] Processamento concluído: 3 sucesso(s), 0 erro(s)
[2025-12-29 21:30:01] [INFO] ========== PROCESSAMENTO FINALIZADO ==========
```

### Exemplo de Log (Erro)

```
[2025-12-29 21:35:00] [INFO] ========== INICIANDO PROCESSAMENTO DE WEBHOOKS ==========
[2025-12-29 21:35:00] [INFO] Conexão com banco estabelecida
[2025-12-29 21:35:00] [ERROR] ERRO ao obter access token: Access token não configurado
[2025-12-29 21:35:00] [ERROR] ERRO CRÍTICO: Access token não configurado
[2025-12-29 21:35:00] [ERROR] ========== PROCESSAMENTO ABORTADO ==========
```

### Verificar Webhooks Pendentes

```sql
SELECT COUNT(*) as pendentes
FROM webhooks_pagamento
WHERE gateway = 'mercadopago'
AND processado = 0
AND transaction_id IS NOT NULL;
```

### Verificar Webhooks Processados (Últimas 24h)

```sql
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN erro IS NULL THEN 1 ELSE 0 END) as sucessos,
    SUM(CASE WHEN erro IS NOT NULL THEN 1 ELSE 0 END) as erros
FROM webhooks_pagamento
WHERE gateway = 'mercadopago'
AND processado = 1
AND data_processamento >= NOW() - INTERVAL 24 HOUR;
```

### Verificar Webhooks com Erro

```sql
SELECT id, transaction_id, erro, data_recebimento
FROM webhooks_pagamento
WHERE gateway = 'mercadopago'
AND processado = 0
AND erro IS NOT NULL
ORDER BY data_recebimento DESC
LIMIT 10;
```

---

## 🛡️ Idempotência

### Como Funciona

O script é **idempotente**, ou seja, pode ser executado múltiplas vezes sem causar problemas:

1. **Verificação antes de atualizar**:
```php
if ($conta['status'] === 'pago') {
    logProcessamento("Conta já estava marcada como paga (idempotência)");
    return true;
}
```

2. **UPDATE condicional**:
```sql
UPDATE contas_receber
SET status = 'pago', ...
WHERE id = ?
AND status <> 'pago'  -- Só atualiza se não estiver pago
```

3. **Webhook marcado como processado**:
- Mesmo que falhe na atualização, webhook é marcado
- Evita reprocessamento infinito

### Cenários Seguros

✅ **Webhook duplicado**: Primeiro processa, segundo ignora  
✅ **CRON executado múltiplas vezes**: Sem problema  
✅ **Conta já paga manualmente**: Não sobrescreve  
✅ **Falha no meio do processamento**: Próxima execução continua  

---

## 🐛 Solução de Problemas

### Problema 1: Webhooks Não São Processados

**Sintomas**:
- Webhooks ficam com `processado = 0`
- Logs não mostram processamento

**Verificar**:
1. CRON está configurado?
```bash
crontab -l
```

2. Script tem permissão de execução?
```bash
ls -la processar_webhooks_mercadopago.php
```

3. Caminho do PHP está correto?
```bash
which php
```

4. Logs mostram erros?
```bash
tail -20 /home/inlaud99/public_html/logs/processar_webhooks.log
```

**Soluções**:
- Configurar CRON conforme Passo 4
- Ajustar permissões: `chmod 644 processar_webhooks_mercadopago.php`
- Atualizar caminho do PHP no CRON
- Verificar e corrigir erros nos logs

### Problema 2: Erro "Access token não configurado"

**Causa**: Tabela `configuracoes_gateway` vazia ou sem dados do Mercado Pago

**Solução**:
```sql
-- Verificar
SELECT * FROM configuracoes_gateway WHERE gateway = 'mercadopago';

-- Se vazio, inserir
INSERT INTO configuracoes_gateway (gateway, access_token, ativo)
VALUES ('mercadopago', 'SEU_ACCESS_TOKEN_AQUI', 1);

-- Se existe, atualizar
UPDATE configuracoes_gateway
SET access_token = 'SEU_ACCESS_TOKEN_AQUI',
    ativo = 1
WHERE gateway = 'mercadopago';
```

### Problema 3: Erro "Conta não encontrada"

**Causa**: `external_reference` não está no formato correto

**Verificar**:
```sql
SELECT transaction_id, payload
FROM webhooks_pagamento
WHERE gateway = 'mercadopago'
AND processado = 0
LIMIT 1;
```

**Solução**:
- Verificar se `external_reference` está sendo enviado ao gerar pagamento
- Formato esperado: `conta_123`
- Atualizar `gerar_link_pagamento.php` se necessário

### Problema 4: Erro "API retornou HTTP 401"

**Causa**: Access token inválido ou expirado

**Solução**:
1. Obter novo token no painel do Mercado Pago
2. Atualizar no banco:
```sql
UPDATE configuracoes_gateway
SET access_token = 'NOVO_TOKEN_AQUI'
WHERE gateway = 'mercadopago';
```

### Problema 5: Processamento Lento

**Sintomas**:
- Webhooks acumulam
- Processamento não acompanha volume

**Soluções**:
1. Aumentar `BATCH_SIZE`:
```php
define('BATCH_SIZE', 50); // Era 20
```

2. Reduzir intervalo do CRON:
```
* * * * *  // A cada 1 minuto (em vez de 5)
```

3. Remover delay entre requisições:
```php
// Comentar esta linha
// usleep(100000);
```

---

## 📈 Performance

### Métricas Esperadas

**Servidor Compartilhado**:
- 20 webhooks/minuto
- ~1.200 webhooks/hora
- ~28.800 webhooks/dia

**Servidor Dedicado/VPS**:
- 100 webhooks/minuto
- ~6.000 webhooks/hora
- ~144.000 webhooks/dia

### Otimizações Implementadas

✅ Processamento em lote (BATCH_SIZE)  
✅ Queries otimizadas com LIMIT  
✅ Índices no banco  
✅ Timeout configurado  
✅ Delay entre requisições (evita rate limit)  
✅ Exit rápido se não há webhooks  

---

## 🔒 Segurança

### Boas Práticas Implementadas

✅ **Sem output HTML**: Seguro para CRON  
✅ **Logs em arquivo**: Não expõe dados  
✅ **Prepared statements**: Previne SQL injection  
✅ **Timeout configurado**: Evita travamento  
✅ **SSL verificado**: Conexão segura com API  
✅ **Access token do banco**: Não hardcoded  
✅ **Error reporting desabilitado**: Não expõe erros  

### Recomendações Adicionais

1. **Restringir acesso ao arquivo**:
```apache
# .htaccess
<Files "processar_webhooks_mercadopago.php">
    Order Deny,Allow
    Deny from all
    Allow from 127.0.0.1
</Files>
```

2. **Monitorar logs regularmente**:
```bash
# Alertar se houver muitos erros
grep -c "ERROR" /home/inlaud99/public_html/logs/processar_webhooks.log
```

3. **Backup regular dos logs**:
```bash
# Rotacionar logs mensalmente
mv processar_webhooks.log processar_webhooks_$(date +%Y%m).log
```

---

## 🧪 Testes

### Teste 1: Execução Manual

```bash
cd /home/inlaud99/public_html
php processar_webhooks_mercadopago.php
echo $?  # Deve retornar 0 (sucesso)
```

### Teste 2: Verificar Logs

```bash
tail -20 /home/inlaud99/public_html/logs/processar_webhooks.log
```

**Deve mostrar**:
- Início do processamento
- Webhooks encontrados
- Processamento individual
- Resumo final
- Finalização

### Teste 3: Simular Webhook

```sql
-- Inserir webhook de teste
INSERT INTO webhooks_pagamento (
    gateway,
    evento,
    transaction_id,
    payload,
    processado,
    data_recebimento
) VALUES (
    'mercadopago',
    'payment',
    '123456789',
    '{"id": 123456789}',
    0,
    NOW()
);

-- Executar processador
-- (via SSH ou CRON)

-- Verificar se foi processado
SELECT processado, data_processamento, erro
FROM webhooks_pagamento
WHERE transaction_id = '123456789';
```

### Teste 4: Idempotência

```bash
# Executar 3 vezes seguidas
php processar_webhooks_mercadopago.php
php processar_webhooks_mercadopago.php
php processar_webhooks_mercadopago.php

# Verificar logs: deve mostrar que conta já estava paga
grep "idempotência" /home/inlaud99/public_html/logs/processar_webhooks.log
```

---

## 📋 Checklist de Instalação

- [ ] Arquivo `processar_webhooks_mercadopago.php` enviado
- [ ] Permissões 644 definidas
- [ ] Pasta `/logs/` criada
- [ ] Permissões 755 na pasta de logs
- [ ] Teste manual executado com sucesso
- [ ] Logs sendo gerados corretamente
- [ ] CRON configurado
- [ ] CRON testado (aguardar 1-5 minutos)
- [ ] Webhooks sendo processados
- [ ] Contas sendo marcadas como pagas
- [ ] Monitoramento configurado

---

## 📞 Suporte

**Logs**: `/logs/processar_webhooks.log`  
**Queries de Verificação**: Ver seção "Monitoramento"  
**Documentação**: Este arquivo

---

## 🎯 Resumo

**Arquivo**: `processar_webhooks_mercadopago.php`  
**Função**: Conciliação financeira automática  
**Execução**: Via CRON (1-5 minutos)  
**Logs**: `/logs/processar_webhooks.log`  
**Status**: ✅ Pronto para produção

**Características**:
- ✅ Desacoplado
- ✅ Idempotente
- ✅ Seguro para CRON
- ✅ Logs detalhados
- ✅ Tratamento de erros
- ✅ Performance otimizada

---

**Data**: 29/12/2025  
**Versão**: 1.0  
**Autor**: Manus AI
