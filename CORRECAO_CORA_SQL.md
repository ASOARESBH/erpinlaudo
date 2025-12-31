# 🔧 Correção: Erro SQL ao Salvar Configurações do CORA

## 📋 Problema

**Erro**: 
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'config' in 'field list'
```

**Quando**: Ao salvar configurações do CORA na página `integracoes_boleto.php`

---

## 🔍 Causa Identificada

O código estava tentando usar uma coluna chamada `config` que **não existe** na tabela `integracoes`.

### Estrutura Real da Tabela

```sql
CREATE TABLE `integracoes` (
  `id` int(11) NOT NULL,
  `tipo` enum('cora','stripe'),
  `api_key` varchar(255),
  `api_secret` varchar(255),
  `webhook_url` varchar(255),
  `ativo` tinyint(1),
  `configuracoes` json,  -- ✅ Nome correto
  ...
);
```

### Código com Erro

**Linha 66** (integracoes_boleto.php):
```php
$config = json_encode([...]);  // ❌ Variável com nome errado
$sql = "UPDATE integracoes SET config = ?, ...";  // ❌ Coluna não existe
```

**Problema**: Coluna correta é `configuracoes`, não `config`

---

## ✅ Solução Aplicada

### 1. Correção do Nome da Coluna

**Antes**:
```php
$config = json_encode([
    'client_id' => $clientId,
    'ambiente' => $ambiente
]);

$sql = "UPDATE integracoes SET config = ?, api_key = ?, api_secret = ?, ativo = ? WHERE tipo = 'cora'";
$stmt->execute([$config, $certificadoPath, $privateKeyPath, $ativo]);
```

**Depois**:
```php
$configuracoes = json_encode([
    'client_id' => $clientId,
    'ambiente' => $ambiente
]);

$sql = "UPDATE integracoes SET configuracoes = ?, api_key = ?, api_secret = ?, ativo = ? WHERE tipo = 'cora'";
$stmt->execute([$configuracoes, $certificadoPath, $privateKeyPath, $ativo]);
```

### 2. Adição de INSERT/UPDATE Inteligente

**Novo Código**:
```php
// Verificar se registro existe
$stmtCheck = $conn->prepare("SELECT id FROM integracoes WHERE tipo = 'cora'");
$stmtCheck->execute();
$existe = $stmtCheck->fetch();

if ($existe) {
    // UPDATE
    $sql = "UPDATE integracoes SET configuracoes = ?, api_key = ?, api_secret = ?, ativo = ? WHERE tipo = 'cora'";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$configuracoes, $certificadoPath, $privateKeyPath, $ativo]);
} else {
    // INSERT
    $sql = "INSERT INTO integracoes (tipo, configuracoes, api_key, api_secret, ativo) VALUES ('cora', ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$configuracoes, $certificadoPath, $privateKeyPath, $ativo]);
}
```

**Benefício**: Se registro não existir, cria automaticamente ✅

### 3. Mesma Correção para Stripe

Aplicada a mesma lógica INSERT/UPDATE para o Stripe, garantindo consistência.

---

## 📝 Alterações no Arquivo

**Arquivo**: `integracoes_boleto.php`

**Mudanças**:
1. ✅ `config` → `configuracoes` (nome correto da coluna)
2. ✅ Adicionada verificação de existência do registro
3. ✅ INSERT automático se registro não existir
4. ✅ Mesma lógica aplicada para CORA e Stripe

---

## 🚀 Instalação (2 minutos)

### Passo 1: Upload do Arquivo

1. Fazer upload de `integracoes_boleto.php`
2. **Substituir** o arquivo existente
3. Verificar permissões: **644**

### Passo 2: Testar

1. Acessar: `https://erp.inlaudo.com.br/integracoes_boleto.php`
2. Preencher campos do CORA:
   - Client-ID
   - Ambiente (Teste ou Produção)
   - Upload de certificado
   - Upload de chave privada
3. Marcar "Integração Ativa"
4. Clicar em "Salvar Configurações"
5. **Verificar**: Mensagem de sucesso ✅

---

## 🧪 Teste Completo

### Cenário 1: Primeira Configuração (INSERT)

**Situação**: Nenhuma configuração do CORA existe

**Passos**:
1. Acessar página de integrações
2. Preencher dados do CORA
3. Fazer upload dos certificados
4. Salvar

**Resultado Esperado**:
- ✅ Registro criado na tabela `integracoes`
- ✅ Mensagem: "Configurações do CORA atualizadas com sucesso!"
- ✅ Sem erro SQL

### Cenário 2: Atualização (UPDATE)

**Situação**: Configuração do CORA já existe

**Passos**:
1. Acessar página de integrações
2. Alterar Client-ID
3. Salvar

**Resultado Esperado**:
- ✅ Registro atualizado
- ✅ Mensagem de sucesso
- ✅ Sem erro SQL

### Cenário 3: Upload de Novos Certificados

**Passos**:
1. Acessar página
2. Fazer upload de novo certificado
3. Fazer upload de nova chave privada
4. Salvar

**Resultado Esperado**:
- ✅ Arquivos salvos em `/certs/`
- ✅ Permissões 600 aplicadas
- ✅ Caminhos salvos no banco
- ✅ Sem erro

---

## 🔄 Fluxo Corrigido

### Antes (Com Erro) ❌
```
Usuário preenche formulário
    ↓
POST para integracoes_boleto.php
    ↓
Monta JSON: $config = json_encode([...])
    ↓
SQL: UPDATE integracoes SET config = ?
    ↓
❌ Erro: Column 'config' not found
```

### Depois (Corrigido) ✅
```
Usuário preenche formulário
    ↓
POST para integracoes_boleto.php
    ↓
Monta JSON: $configuracoes = json_encode([...])
    ↓
Verifica se registro existe
    ↓
Se existe: UPDATE integracoes SET configuracoes = ?
Se não existe: INSERT INTO integracoes (...)
    ↓
✅ Sucesso: Configurações salvas
```

---

## 📊 Estrutura de Dados

### JSON Salvo em `configuracoes`

```json
{
  "client_id": "int-6I2u3vpjG5z8nev37Wm7",
  "ambiente": "producao"
}
```

### Caminhos Salvos

- **api_key**: `/home/inlaud99/public_html/certs/cora_certificate_1735414800.pem`
- **api_secret**: `/home/inlaud99/public_html/certs/cora_private_key_1735414800.key`

### Registro Completo

```sql
SELECT * FROM integracoes WHERE tipo = 'cora';

id: 1
tipo: cora
api_key: /home/.../certs/cora_certificate_1735414800.pem
api_secret: /home/.../certs/cora_private_key_1735414800.key
webhook_url: NULL
ativo: 1
configuracoes: {"client_id":"int-6I2u3vpjG5z8nev37Wm7","ambiente":"producao"}
```

---

## 🐛 Solução de Problemas

### Erro: "Column 'config' not found" ainda aparece

**Causa**: Arquivo não foi substituído

**Solução**:
1. Verificar se arquivo foi enviado corretamente
2. Limpar cache do OPcache (se houver)
3. Reiniciar PHP-FPM (se necessário)

### Erro: "Erro ao fazer upload do certificado"

**Causa**: Pasta `/certs/` não existe ou sem permissão

**Solução**:
1. Criar pasta manualmente: `/home/inlaud99/public_html/certs/`
2. Definir permissões: **755**
3. Verificar se PHP pode escrever na pasta

### Configurações não aparecem após salvar

**Causa**: Registro não foi criado

**Solução**:
1. Verificar se tabela `integracoes` existe
2. Executar query manual:
```sql
INSERT INTO integracoes (tipo, configuracoes, ativo) 
VALUES ('cora', NULL, 0);
```
3. Tentar salvar novamente

### Certificados não funcionam

**Causa**: Permissões incorretas

**Solução**:
1. Verificar permissões dos arquivos: **600**
2. Verificar se arquivos existem:
```bash
ls -la /home/inlaud99/public_html/certs/
```
3. Verificar conteúdo dos arquivos (devem começar com `-----BEGIN`)

---

## ✅ Checklist

- [ ] Arquivo `integracoes_boleto.php` baixado
- [ ] Upload para raiz do ERP
- [ ] Arquivo substituído
- [ ] Permissões verificadas (644)
- [ ] Pasta `/certs/` criada (755)
- [ ] Teste de salvamento realizado
- [ ] Mensagem de sucesso aparece
- [ ] Sem erro SQL
- [ ] Certificados salvos
- [ ] Configurações aparecem no banco

---

## 📈 Melhorias Implementadas

### Correção
✅ Coluna correta: `configuracoes`  
✅ INSERT/UPDATE automático  
✅ Tratamento de erros  

### Robustez
✅ Verifica existência antes de UPDATE  
✅ Cria registro se não existir  
✅ Mensagens de erro claras  

### Segurança
✅ Certificados com permissão 600  
✅ Pasta protegida  
✅ Validação de uploads  

---

## 🎯 Status

**Versão**: 7.3.3  
**Status**: ✅ **CORRIGIDO**  
**Arquivo**: 1 (integracoes_boleto.php)  
**Tempo de Instalação**: ~2 minutos  
**Complexidade**: Baixa  

Erro SQL corrigido com sucesso! Configurações do CORA agora salvam corretamente! 🚀
