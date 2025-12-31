# 🔍 Análise Completa: Fluxo de Pagamento Mercado Pago

## 📋 Problema Relatado

**Situação**: PIX é gerado normalmente, mas quando o pagamento é efetuado, o status não muda de "pendente" para "pago"

---

## 🔄 Fluxo Atual (Como Está)

### 1. Geração do Pagamento

**Arquivo**: `gerar_link_pagamento.php`

```php
// Linha 116: Define external_reference
'external_reference' => 'conta_' . $contaId,

// Linha 156-167: Salva payment_id na tabela contas_receber
UPDATE contas_receber
SET gateway = 'mercadopago',
    payment_id = ?,
    idempotency_key = ?
WHERE id = ?
```

**✅ Funcionando**: PIX é gerado e `payment_id` é salvo

### 2. Webhook Recebe Notificação

**Arquivo**: `webhook_mercadopago.php`

```php
// Linha 47: Extrai payment_id do payload
$paymentId = $payload['data']['id'];

// Linha 75-92: Consulta API do Mercado Pago
$ch = curl_init("https://api.mercadopago.com/v1/payments/{$paymentId}");

// Linha 99-102: Verifica se status é 'approved'
if ($payment['status'] !== 'approved') {
    exit;
}

// Linha 114: Extrai conta_id do external_reference
$contaId = (int) str_replace('conta_', '', $payment['external_reference']);

// Linha 125-132: Atualiza contas_receber
UPDATE contas_receber
SET status = 'pago',
    data_pagamento = NOW()
WHERE id = ?
AND status <> 'pago'
```

**⚠️ PROBLEMA IDENTIFICADO**: Webhook atualiza `contas_receber` usando `id` da conta

---

## 🐛 Pontos de Falha Identificados

### 1. ❌ Tabela `configuracoes_gateway` Vazia

**Problema**: Webhook busca `access_token` em `configuracoes_gateway`:

```php
// Linha 56-64
SELECT access_token
FROM configuracoes_gateway
WHERE gateway = 'mercadopago'
AND ativo = 1
```

**Causa**: Tabela pode estar vazia ou sem dados do Mercado Pago

**Resultado**: Webhook não consegue consultar API → Status não atualiza

### 2. ❌ Webhook URL Não Configurada

**Problema**: Mercado Pago precisa da URL do webhook configurada

**Verificar**:
- URL está cadastrada no painel do Mercado Pago?
- URL está correta: `https://erp.inlaudo.com.br/webhook_mercadopago.php`?

### 3. ❌ Logs Não Estão Sendo Gerados

**Problema**: Pasta `/logs/` pode não existir

**Resultado**: Erros não são registrados, dificulta debug

### 4. ⚠️ External Reference Pode Estar Incorreto

**Problema**: Se `external_reference` não for salvo corretamente no Mercado Pago

**Verificar**: Se API do MP está recebendo `external_reference` corretamente

### 5. ⚠️ Webhook Pode Não Estar Sendo Chamado

**Problema**: Mercado Pago pode não estar enviando notificações

**Causas Possíveis**:
- URL do webhook incorreta
- Webhook não configurado no painel MP
- Firewall bloqueando requisições do MP
- SSL inválido

---

## ✅ Soluções Propostas

### Solução 1: Garantir Dados em `configuracoes_gateway`

**Script SQL**:
```sql
-- Verificar se registro existe
SELECT * FROM configuracoes_gateway WHERE gateway = 'mercadopago';

-- Se não existir, criar
INSERT INTO configuracoes_gateway (
    gateway, 
    ativo, 
    access_token, 
    public_key, 
    webhook_url,
    ambiente
) VALUES (
    'mercadopago',
    1,
    'SEU_ACCESS_TOKEN_AQUI',
    'SEU_PUBLIC_KEY_AQUI',
    'https://erp.inlaudo.com.br/webhook_mercadopago.php',
    'producao'
);

-- Se existir, atualizar
UPDATE configuracoes_gateway
SET access_token = 'SEU_ACCESS_TOKEN_AQUI',
    public_key = 'SEU_PUBLIC_KEY_AQUI',
    webhook_url = 'https://erp.inlaudo.com.br/webhook_mercadopago.php',
    ativo = 1
WHERE gateway = 'mercadopago';
```

### Solução 2: Criar Pasta de Logs

```bash
mkdir -p /home/inlaud99/public_html/logs
chmod 755 /home/inlaud99/public_html/logs
```

### Solução 3: Melhorar Webhook com Mais Logs

**Adicionar logs detalhados**:
- Log quando webhook é chamado
- Log do payload recebido
- Log da resposta da API MP
- Log do external_reference
- Log da atualização do banco

### Solução 4: Webhook Alternativo Usando `payment_id`

**Problema**: Se `external_reference` falhar, usar `payment_id`

**Código Alternativo**:
```php
// Buscar conta pelo payment_id
$stmt = $conn->prepare("
    SELECT id 
    FROM contas_receber 
    WHERE payment_id = ? 
    LIMIT 1
");
$stmt->execute([$paymentId]);
$conta = $stmt->fetch(PDO::FETCH_ASSOC);

if ($conta) {
    $contaId = $conta['id'];
    // Atualizar status
}
```

### Solução 5: Página de Teste do Webhook

**Criar página para testar webhook manualmente**:
```php
// teste_webhook.php
<?php
require_once 'config.php';

$paymentId = $_GET['payment_id'] ?? '';

if (!$paymentId) {
    die('Informe payment_id na URL');
}

// Simular webhook
$payload = [
    'data' => [
        'id' => $paymentId
    ]
];

// Chamar webhook
$ch = curl_init('https://erp.inlaudo.com.br/webhook_mercadopago.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json']
]);

$response = curl_exec($ch);
curl_close($ch);

echo "<h3>Resposta do Webhook:</h3>";
echo "<pre>$response</pre>";

// Verificar logs
echo "<h3>Logs:</h3>";
echo "<pre>";
echo file_get_contents(__DIR__ . '/logs/webhook_mercadopago.log');
echo "</pre>";
?>
```

---

## 🔧 Correções a Implementar

### 1. Webhook Melhorado

**Mudanças**:
- ✅ Buscar conta por `payment_id` se `external_reference` falhar
- ✅ Logs mais detalhados
- ✅ Tratamento de erros robusto
- ✅ Sempre retornar 200 OK

### 2. Migração de Dados

**Se credenciais estão em `integracoes_pagamento`**:
```sql
-- Migrar dados
INSERT INTO configuracoes_gateway (gateway, access_token, public_key, webhook_url, ativo)
SELECT 
    'mercadopago',
    mp_access_token,
    mp_public_key,
    mp_webhook_url,
    ativo
FROM integracoes_pagamento
WHERE gateway = 'mercadopago'
ON DUPLICATE KEY UPDATE
    access_token = VALUES(access_token),
    public_key = VALUES(public_key),
    webhook_url = VALUES(webhook_url),
    ativo = VALUES(ativo);
```

### 3. Script de Verificação

**Criar script para verificar configuração**:
```php
// verificar_config.php
<?php
require_once 'config.php';
$conn = getConnection();

echo "<h2>Verificação de Configuração</h2>";

// 1. Verificar configuracoes_gateway
$stmt = $conn->query("SELECT * FROM configuracoes_gateway WHERE gateway = 'mercadopago'");
$config = $stmt->fetch(PDO::FETCH_ASSOC);

echo "<h3>1. Configurações Gateway:</h3>";
if ($config) {
    echo "✅ Registro encontrado<br>";
    echo "Ativo: " . ($config['ativo'] ? 'SIM' : 'NÃO') . "<br>";
    echo "Access Token: " . (empty($config['access_token']) ? '❌ VAZIO' : '✅ Preenchido') . "<br>";
    echo "Webhook URL: " . htmlspecialchars($config['webhook_url']) . "<br>";
} else {
    echo "❌ Nenhum registro encontrado<br>";
}

// 2. Verificar pasta logs
echo "<h3>2. Pasta de Logs:</h3>";
if (is_dir(__DIR__ . '/logs')) {
    echo "✅ Pasta existe<br>";
    if (is_writable(__DIR__ . '/logs')) {
        echo "✅ Pasta tem permissão de escrita<br>";
    } else {
        echo "❌ Pasta SEM permissão de escrita<br>";
    }
} else {
    echo "❌ Pasta não existe<br>";
}

// 3. Verificar contas com payment_id
$stmt = $conn->query("SELECT COUNT(*) as total FROM contas_receber WHERE payment_id IS NOT NULL");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<h3>3. Contas com Payment ID:</h3>";
echo "Total: " . $result['total'] . "<br>";

// 4. Verificar contas pendentes com payment_id
$stmt = $conn->query("SELECT COUNT(*) as total FROM contas_receber WHERE payment_id IS NOT NULL AND status = 'pendente'");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<h3>4. Contas Pendentes com Payment ID:</h3>";
echo "Total: " . $result['total'] . " (podem estar pagas no MP mas não atualizadas)<br>";

// 5. Testar API do Mercado Pago
if ($config && !empty($config['access_token'])) {
    echo "<h3>5. Teste de API do Mercado Pago:</h3>";
    $ch = curl_init('https://api.mercadopago.com/v1/payments/search?limit=1');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $config['access_token']
        ]
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✅ API respondeu corretamente (HTTP 200)<br>";
    } else {
        echo "❌ API retornou erro (HTTP $httpCode)<br>";
    }
}
?>
```

---

## 📊 Checklist de Verificação

- [ ] Tabela `configuracoes_gateway` tem dados do Mercado Pago
- [ ] Campo `access_token` está preenchido
- [ ] Campo `webhook_url` está correto
- [ ] Campo `ativo` está como 1
- [ ] Pasta `/logs/` existe
- [ ] Pasta `/logs/` tem permissão 755
- [ ] Webhook URL configurada no painel do Mercado Pago
- [ ] SSL do site está válido
- [ ] Firewall não bloqueia IPs do Mercado Pago
- [ ] Logs do webhook estão sendo gerados

---

## 🎯 Próximos Passos

1. ✅ Verificar configuração com script de verificação
2. ✅ Criar pasta de logs se não existir
3. ✅ Atualizar webhook com logs detalhados
4. ✅ Adicionar busca por payment_id como fallback
5. ✅ Testar webhook manualmente
6. ✅ Verificar logs após teste
7. ✅ Fazer pagamento real de teste
8. ✅ Confirmar atualização de status

---

**Conclusão**: O problema está na falta de dados em `configuracoes_gateway` ou webhook não sendo chamado pelo Mercado Pago. As correções propostas resolverão ambos os cenários.
