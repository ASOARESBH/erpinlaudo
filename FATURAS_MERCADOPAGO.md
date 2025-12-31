# 💳 Faturas Mercado Pago - Documentação

## 📋 Visão Geral

Nova página criada para visualizar todas as transações geradas via Mercado Pago, similar à página de Faturas Stripe existente.

---

## 🎯 Funcionalidades

### 1. Dashboard com Estatísticas

**Cards de Resumo**:
- 📊 **Total de Transações**: Quantidade total de transações
- ✅ **Total Aprovado**: Soma de todas as transações aprovadas
- ⏳ **Total Pendente**: Soma de transações pendentes/em processamento
- ❌ **Total Rejeitado**: Soma de transações rejeitadas/canceladas

### 2. Filtros Avançados

**Filtro por Status**:
- Pendente
- Aprovado
- Autorizado
- Em Processamento
- Em Mediação
- Rejeitado
- Cancelado
- Reembolsado
- Chargeback

**Filtro por Cliente**:
- Lista todos os clientes que têm transações no Mercado Pago
- Ordenado por razão social/nome

**Filtro por Período**:
- Hoje
- Esta Semana
- Este Mês
- Este Ano

### 3. Tabela de Transações

**Colunas Exibidas**:
1. **ID Transação**: Payment ID ou Transaction ID
2. **Cliente**: Nome/Razão Social + CNPJ/CPF
3. **Descrição**: Descrição da conta ou contrato
4. **Valor**: Valor formatado em Real (R$)
5. **Método**: Ícone + nome do método (Cartão, Boleto, PIX, etc)
6. **Status**: Badge colorido com emoji e status
7. **Data**: Data e hora da criação
8. **Ações**: Botões de ação

**Ações Disponíveis**:
- 🔗 **Link**: Abre link de pagamento (se disponível)
- 🎫 **Boleto**: Visualiza boleto (se método for boleto)
- 📋 **Detalhes**: Expande detalhes completos da transação

### 4. Detalhes da Transação

Ao clicar em "Detalhes", expande linha com informações completas:

**Informações Exibidas**:
- 🆔 Payment ID
- 🔢 Transaction ID
- 💰 Valor (destacado)
- 💳 Método de Pagamento (nome completo)
- 📅 Data de Vencimento (se houver)
- 🔄 Última Atualização
- 🎫 Linha Digitável (com botão para copiar)
- 🔗 Link de Pagamento (clicável)
- 📄 Resposta da API (JSON expandível)

---

## 🎨 Design

### Cores dos Cards

**Total de Transações**: Gradiente roxo (#667eea → #764ba2)  
**Total Aprovado**: Gradiente verde (#10b981 → #059669)  
**Total Pendente**: Gradiente amarelo (#fbbf24 → #f59e0b)  
**Total Rejeitado**: Gradiente vermelho (#ef4444 → #dc2626)

### Badges de Status

| Status | Cor | Emoji |
|--------|-----|-------|
| Pendente | Amarelo | ⏳ |
| Aprovado | Verde | ✅ |
| Autorizado | Amarelo | 🔐 |
| Em Processamento | Amarelo | ⏳ |
| Em Mediação | Amarelo | ⚖️ |
| Rejeitado | Vermelho | ❌ |
| Cancelado | Cinza | 🚫 |
| Reembolsado | Laranja | ↩️ |
| Chargeback | Vermelho | ⚠️ |

### Ícones de Métodos de Pagamento

| Método | Ícone |
|--------|-------|
| Cartão de Crédito | 💳 Cartão |
| Cartão de Débito | 💳 Débito |
| Boleto Bancário | 🎫 Boleto |
| PIX | ⚡ PIX |
| Saldo Mercado Pago | 💰 Saldo MP |

---

## 🗄️ Consulta ao Banco de Dados

### Query Principal

```sql
SELECT 
    t.*,
    c.nome, 
    c.razao_social, 
    c.nome_fantasia, 
    c.tipo_pessoa,
    c.email,
    c.cnpj_cpf,
    cr.descricao as conta_descricao,
    cr.data_vencimento,
    ct.titulo as contrato_titulo
FROM transacoes_pagamento t
INNER JOIN clientes c ON t.cliente_id = c.id
LEFT JOIN contas_receber cr ON t.conta_receber_id = cr.id
LEFT JOIN contratos ct ON t.contrato_id = ct.id
WHERE t.gateway = 'mercadopago'
ORDER BY t.data_criacao DESC
```

### Tabelas Utilizadas

1. **transacoes_pagamento**: Tabela principal com todas as transações
2. **clientes**: Dados do cliente
3. **contas_receber**: Descrição da conta (se vinculada)
4. **contratos**: Título do contrato (se vinculado)

---

## 📊 Campos da Tabela transacoes_pagamento

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | ID único da transação |
| cliente_id | INT | ID do cliente |
| conta_receber_id | INT | ID da conta a receber (opcional) |
| contrato_id | INT | ID do contrato (opcional) |
| gateway | VARCHAR | Gateway de pagamento (mercadopago) |
| transaction_id | VARCHAR | ID da transação |
| payment_id | VARCHAR | ID do pagamento no Mercado Pago |
| valor | DECIMAL | Valor da transação |
| status | VARCHAR | Status do pagamento |
| metodo_pagamento | VARCHAR | Método de pagamento usado |
| payment_url | TEXT | URL do checkout |
| boleto_url | TEXT | URL do boleto (se aplicável) |
| linha_digitavel | VARCHAR | Linha digitável do boleto |
| response_json | TEXT | Resposta completa da API |
| data_criacao | DATETIME | Data de criação |
| data_atualizacao | DATETIME | Data da última atualização |

---

## 🚀 Como Usar

### Para Administradores

#### 1. Acessar a Página
```
Menu > Faturamento > Faturas Mercado Pago
```

#### 2. Visualizar Estatísticas
- Cards no topo mostram resumo geral
- Valores atualizados em tempo real

#### 3. Filtrar Transações
- Selecionar status desejado
- Selecionar cliente específico (opcional)
- Selecionar período (opcional)
- Clicar em "Filtrar"

#### 4. Ver Detalhes de uma Transação
- Localizar transação na tabela
- Clicar em "📋 Detalhes"
- Linha expande mostrando informações completas

#### 5. Copiar Linha Digitável
- Expandir detalhes da transação
- Localizar seção "Linha Digitável do Boleto"
- Clicar em "📋 Copiar Linha Digitável"
- Linha copiada para área de transferência

#### 6. Acessar Link de Pagamento
- Clicar em "🔗 Link" na coluna de ações
- Abre link em nova aba
- Cliente pode visualizar e pagar

---

## 🔍 Status das Transações

### Status Positivos (Verde)
- **approved**: Pagamento aprovado e confirmado

### Status Pendentes (Amarelo)
- **pending**: Aguardando processamento
- **authorized**: Autorizado mas não capturado
- **in_process**: Em processamento pelo banco
- **in_mediation**: Em mediação (disputa)

### Status Negativos (Vermelho/Cinza)
- **rejected**: Pagamento rejeitado
- **cancelled**: Pagamento cancelado
- **refunded**: Pagamento reembolsado
- **charged_back**: Chargeback realizado

---

## 💡 Diferenças entre Faturas Stripe e Mercado Pago

| Característica | Faturas Stripe | Faturas Mercado Pago |
|----------------|----------------|----------------------|
| **Fonte de Dados** | Tabela `faturamento` | Tabela `transacoes_pagamento` |
| **Filtro de Gateway** | Não possui | Filtrado por `gateway = 'mercadopago'` |
| **Métodos de Pagamento** | Boleto e Cartão | Boleto, PIX, Cartão, Saldo MP |
| **Status** | 5 status Stripe | 9 status Mercado Pago |
| **Linha Digitável** | Não exibe | Exibe com botão para copiar |
| **JSON da API** | Não exibe | Exibe em detalhes expandíveis |
| **Filtro de Período** | Não possui | Hoje, Semana, Mês, Ano |

---

## 📱 Responsividade

A página é totalmente responsiva:
- Cards de estatísticas se reorganizam em telas menores
- Filtros empilham verticalmente em mobile
- Tabela tem scroll horizontal em telas pequenas
- Botões de ação se adaptam ao tamanho da tela

---

## 🔧 Funcionalidades JavaScript

### 1. Expandir/Recolher Detalhes
```javascript
function verDetalhes(id) {
    const detalhes = document.getElementById('detalhes_' + id);
    if (detalhes.style.display === 'none') {
        detalhes.style.display = 'table-row';
    } else {
        detalhes.style.display = 'none';
    }
}
```

### 2. Copiar Linha Digitável
```javascript
function copiarLinhaDigitavel(linha) {
    navigator.clipboard.writeText(linha).then(function() {
        alert('✅ Linha digitável copiada!');
    }, function(err) {
        alert('❌ Erro ao copiar: ' + err);
    });
}
```

---

## 📊 Exemplos de Uso

### Exemplo 1: Ver todas as transações aprovadas
1. Acessar "Faturas Mercado Pago"
2. Selecionar Status: "Aprovado"
3. Clicar em "Filtrar"
4. Visualizar apenas transações aprovadas

### Exemplo 2: Ver transações de um cliente específico
1. Acessar "Faturas Mercado Pago"
2. Selecionar Cliente desejado
3. Clicar em "Filtrar"
4. Visualizar todas as transações daquele cliente

### Exemplo 3: Ver transações do mês atual
1. Acessar "Faturas Mercado Pago"
2. Selecionar Período: "Este Mês"
3. Clicar em "Filtrar"
4. Visualizar transações do mês

### Exemplo 4: Copiar linha digitável de boleto
1. Localizar transação com método "Boleto"
2. Clicar em "📋 Detalhes"
3. Localizar "Linha Digitável do Boleto"
4. Clicar em "📋 Copiar Linha Digitável"
5. Colar onde necessário (WhatsApp, e-mail, etc)

---

## 🐛 Solução de Problemas

### Nenhuma transação aparece

**Possíveis Causas**:
- Nenhuma transação foi criada ainda
- Filtros muito restritivos
- Problema na conexão com banco

**Solução**:
1. Clicar em "Limpar" para remover filtros
2. Verificar se há transações na tabela `transacoes_pagamento`
3. Verificar se campo `gateway` está como "mercadopago"

### Detalhes não expandem

**Possíveis Causas**:
- JavaScript desabilitado
- Erro no console do navegador

**Solução**:
1. Verificar console do navegador (F12)
2. Recarregar página
3. Verificar se função `verDetalhes()` existe

### Linha digitável não copia

**Possíveis Causas**:
- Navegador não suporta Clipboard API
- Permissão negada

**Solução**:
1. Usar navegador moderno (Chrome, Firefox, Edge)
2. Permitir acesso à área de transferência
3. Copiar manualmente se necessário

---

## ✅ Checklist de Instalação

- [x] Arquivo `faturas_mercadopago.php` criado
- [x] Link adicionado ao menu em `header.php`
- [x] Tabela `transacoes_pagamento` existe no banco
- [x] Permissões de acesso configuradas
- [x] Teste de visualização realizado
- [x] Teste de filtros realizado
- [x] Teste de detalhes expandidos realizado

---

## 📈 Benefícios

### Para a Empresa

✅ **Visibilidade Total**: Todas as transações em um só lugar  
✅ **Filtros Avançados**: Encontrar transações rapidamente  
✅ **Estatísticas em Tempo Real**: Acompanhar performance  
✅ **Detalhes Completos**: Informações técnicas para suporte  
✅ **Auditoria**: Histórico completo de todas as transações  

### Para o Suporte

✅ **Diagnóstico Rápido**: Ver status e detalhes da transação  
✅ **Copiar Dados**: Linha digitável e IDs facilmente copiáveis  
✅ **JSON da API**: Resposta completa para debugging  
✅ **Links Diretos**: Acessar checkout e boletos rapidamente  

---

## 🔄 Integração com Outros Módulos

### Webhooks
- Transações são atualizadas automaticamente via `webhook_mercadopago.php`
- Status sincronizado em tempo real

### Contas a Receber
- Transações vinculadas a contas a receber
- Descrição e vencimento exibidos

### Contratos
- Transações vinculadas a contratos
- Título do contrato exibido

### Clientes
- Dados do cliente sempre atualizados
- CNPJ/CPF formatado automaticamente

---

## 📝 Arquivos Relacionados

1. **faturas_mercadopago.php** - Página principal (nova)
2. **header.php** - Menu atualizado com novo link
3. **config.php** - Conexão com banco de dados
4. **style.css** - Estilos da página
5. **footer.php** - Rodapé padrão

---

## 🎯 Próximos Passos Sugeridos

### Curto Prazo
1. ✅ Testar página em produção
2. ✅ Verificar permissões de acesso
3. ✅ Treinar equipe no uso

### Médio Prazo
4. Adicionar exportação para Excel/CSV
5. Adicionar gráficos de performance
6. Implementar notificações de novas transações

### Longo Prazo
7. Dashboard consolidado (Stripe + Mercado Pago)
8. Relatórios mensais automáticos
9. Integração com sistema de comissões

---

**Versão**: 1.0  
**Data**: 22/12/2025  
**Arquivo**: faturas_mercadopago.php  
**Status**: ✅ Pronto para Produção
