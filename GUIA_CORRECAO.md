# 🔧 Guia de Correção do Erro 500 - Faturamento

## 🎯 Problema Identificado

**Erro**: HTTP ERROR 500 na página de faturamento  
**Causa**: Campo `cliente_id` faltando na tabela `transacoes_pagamento`  
**Impacto**: Página **faturas_mercadopago.php** não funciona

---

## 🔍 Diagnóstico

A página `faturas_mercadopago.php` executa a seguinte query:

```sql
SELECT t.*, c.nome, c.razao_social, ...
FROM transacoes_pagamento t
INNER JOIN clientes c ON t.cliente_id = c.id  -- ❌ ERRO AQUI
```

**Problema**: O campo `t.cliente_id` não existe na tabela `transacoes_pagamento`.

---

## ✅ Solução

Adicionar o campo `cliente_id` na tabela `transacoes_pagamento`.

---

## 📋 Passo a Passo da Correção

### Opção 1: Via phpMyAdmin (Recomendado)

#### Passo 1: Acessar phpMyAdmin
1. Fazer login no cPanel da HostGator
2. Localizar e clicar em "phpMyAdmin"
3. Selecionar banco: `inlaud99_erpinlaudo`

#### Passo 2: Executar Script SQL
1. Clicar na aba "SQL" no topo
2. Copiar e colar o conteúdo do arquivo **CORRECAO_SIMPLES.sql**
3. Clicar em "Executar" (botão "Go")

#### Passo 3: Verificar Resultado
Você deve ver as mensagens:
```
✅ Correção concluída!
```

E uma tabela mostrando:
- `total`: Total de registros
- `com_cliente`: Registros com cliente_id preenchido
- `sem_cliente`: Registros sem cliente_id

#### Passo 4: Testar Página
1. Acessar: https://inlaudo.com.br/faturas_mercadopago.php
2. Verificar se a página carrega sem erro 500
3. Verificar se as transações aparecem

---

### Opção 2: Via Terminal SSH (Avançado)

```bash
# 1. Conectar via SSH
ssh usuario@inlaudo.com.br

# 2. Executar script
mysql -u inlaud99_admin -p inlaud99_erpinlaudo < CORRECAO_SIMPLES.sql

# 3. Digitar senha quando solicitado
# Senha: Admin259087@
```

---

## 📄 Arquivos Fornecidos

### 1. CORRECAO_SIMPLES.sql ⭐ (Recomendado)
- Script simples e direto
- Adiciona campo cliente_id
- Atualiza registros existentes
- Fácil de executar

### 2. CORRECAO_BANCO_DADOS.sql (Avançado)
- Script completo com verificações
- Evita erros se campo já existir
- Adiciona chave estrangeira
- Mais robusto

### 3. ANALISE_BANCO.md
- Documentação da análise realizada
- Lista de todas as tabelas
- Detalhes do problema

---

## 🔍 O Que o Script Faz

### 1. Adiciona Campo
```sql
ALTER TABLE transacoes_pagamento 
ADD COLUMN cliente_id INT(11) NULL AFTER id;
```

### 2. Adiciona Índice
```sql
ALTER TABLE transacoes_pagamento 
ADD INDEX idx_cliente_id (cliente_id);
```

### 3. Atualiza Registros Existentes
```sql
-- Via conta_receber
UPDATE transacoes_pagamento t
INNER JOIN contas_receber cr ON t.conta_receber_id = cr.id
SET t.cliente_id = cr.cliente_id;

-- Via contrato
UPDATE transacoes_pagamento t
INNER JOIN contratos ct ON t.contrato_id = ct.id
SET t.cliente_id = ct.cliente_id;
```

---

## ⚠️ Importante

### Antes de Executar:
1. ✅ Fazer backup do banco de dados
2. ✅ Verificar se está no banco correto: `inlaud99_erpinlaudo`
3. ✅ Usar o usuário correto: `inlaud99_admin`

### Após Executar:
1. ✅ Verificar se não houve erros
2. ✅ Testar página de Faturas Mercado Pago
3. ✅ Verificar se transações aparecem corretamente

---

## 🐛 Solução de Problemas

### Erro: "Column 'cliente_id' already exists"
**Causa**: Campo já foi adicionado anteriormente  
**Solução**: Ignorar erro, campo já existe

### Erro: "Access denied"
**Causa**: Usuário ou senha incorretos  
**Solução**: Verificar credenciais:
- Usuário: `inlaud99_admin`
- Senha: `Admin259087@`

### Erro: "Unknown database"
**Causa**: Nome do banco incorreto  
**Solução**: Verificar nome: `inlaud99_erpinlaudo`

### Página ainda dá erro 500
**Possíveis causas**:
1. Script não foi executado
2. Arquivo PHP tem outro erro
3. Permissões de arquivo

**Solução**:
1. Verificar se campo foi adicionado:
   ```sql
   DESCRIBE transacoes_pagamento;
   ```
2. Verificar logs de erro do PHP
3. Verificar permissões dos arquivos (644 para PHP)

---

## 📊 Verificação Manual

### Via phpMyAdmin:

1. Selecionar banco `inlaud99_erpinlaudo`
2. Clicar na tabela `transacoes_pagamento`
3. Clicar na aba "Estrutura"
4. Verificar se campo `cliente_id` aparece na lista

### Via SQL:

```sql
-- Ver estrutura da tabela
DESCRIBE transacoes_pagamento;

-- Ver dados
SELECT id, cliente_id, gateway, valor, status 
FROM transacoes_pagamento 
LIMIT 10;
```

---

## 📞 Suporte

Se após executar o script o erro persistir:

1. Verificar logs de erro do PHP
2. Verificar se todos os arquivos foram enviados
3. Limpar cache do navegador
4. Testar em navegador anônimo

---

## ✅ Checklist de Correção

- [ ] Backup do banco de dados realizado
- [ ] Arquivo CORRECAO_SIMPLES.sql baixado
- [ ] phpMyAdmin acessado
- [ ] Banco inlaud99_erpinlaudo selecionado
- [ ] Script SQL executado sem erros
- [ ] Campo cliente_id verificado na estrutura
- [ ] Página faturas_mercadopago.php testada
- [ ] Transações aparecem corretamente
- [ ] Filtros funcionam
- [ ] Detalhes expandem corretamente

---

## 🎯 Resultado Esperado

Após executar o script:

✅ Campo `cliente_id` adicionado  
✅ Registros existentes atualizados  
✅ Página faturas_mercadopago.php funciona  
✅ Transações aparecem com dados do cliente  
✅ Filtros funcionam normalmente  
✅ Detalhes expandem sem erro  

---

**Tempo Estimado**: 5-10 minutos  
**Dificuldade**: Fácil  
**Risco**: Baixo (script apenas adiciona campo)

---

**Versão**: 1.0  
**Data**: 22/12/2025  
**Status**: ✅ Pronto para Execução
