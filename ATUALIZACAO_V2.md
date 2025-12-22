# Atualização do Sistema ERP INLAUDO - Versão 2.0

## 🎉 Novas Funcionalidades

Esta atualização traz três grandes melhorias ao sistema ERP INLAUDO, tornando-o ainda mais completo e profissional.

---

## 1. 🎫 Geração Automática de Boletos

O sistema agora gera boletos automaticamente através das APIs do **Stripe** e **CORA** quando você seleciona "Boleto" como forma de pagamento em Contas a Receber.

### Como Funciona

Ao cadastrar uma nova conta a receber com forma de pagamento "Boleto", você verá opções adicionais para:

1. **Marcar a opção** "Gerar boleto automaticamente via API"
2. **Selecionar a plataforma**: Stripe ou CORA
3. O sistema automaticamente:
   - Busca os dados completos do cliente
   - Envia para a API selecionada
   - Gera o boleto com código de barras e linha digitável
   - Salva todas as informações no banco de dados
   - Vincula o boleto à conta a receber

### Recursos

- **Geração em lote**: Se você criar uma conta recorrente (12 parcelas, por exemplo), o sistema gera 12 boletos automaticamente
- **Dados completos**: Utiliza todos os dados do cliente (nome, documento, endereço, contatos)
- **URLs diretas**: Acesso rápido ao boleto online e PDF
- **Código de barras e linha digitável**: Disponíveis para pagamento
- **Controle de status**: Acompanhe se o boleto está pendente, pago ou vencido

### Visualização de Boletos

Acesse **Integrações > Boletos Gerados** para:
- Ver todos os boletos gerados
- Acessar URLs dos boletos
- Visualizar códigos de barras e linhas digitáveis
- Acompanhar status de pagamento
- Ver totalizadores de valores

### Bibliotecas Criadas

- `lib_boleto_stripe.php`: Integração completa com Stripe
- `lib_boleto_cora.php`: Integração completa com CORA

Ambas as bibliotecas incluem:
- Geração de boletos
- Consulta de status
- Cancelamento de boletos
- Tratamento de erros
- Mapeamento de status

---

## 2. 📦 Módulo de Produtos/Serviços (Contratos)

Um novo menu **Produtos** foi adicionado ao sistema, permitindo o cadastro completo de produtos e serviços contratados pelos clientes.

### Funcionalidades

**Cadastro Completo**:
- Seleção de cliente
- Tipo: Produto ou Serviço
- Descrição detalhada
- Valor total do contrato
- Forma de pagamento
- Recorrência (número de parcelas)
- Status: Ativo ou Inativo
- Período: Data de início e término
- **Upload de contrato**: Anexe arquivos PDF, DOC, DOCX, JPG ou PNG

**Integração Automática**:
- Ao criar um contrato, você pode marcar a opção para gerar automaticamente as contas a receber
- O sistema cria todas as parcelas mensalmente com os valores calculados
- Vincula cada parcela ao contrato original

**Gestão Visual**:
- Dashboard com total de contratos ativos e valor total
- Filtros por status, tipo e cliente
- Visualização de período de vigência
- Download de contratos anexados

### Acesso

Menu **Produtos > Contratos**

---

## 3. 💰 Módulo CMV (Custo de Mercadoria Vendida)

Dentro de cada contrato, agora você pode calcular o **CMV** - todos os custos envolvidos para determinar a margem líquida real do negócio.

### Como Funciona

Ao visualizar um contrato, clique no botão **CMV** para acessar a tela de custos.

**Adicione Custos**:
- Descrição (Ex: Mão de obra, Material, Transporte, etc.)
- Valor unitário
- Quantidade
- Valor total (calculado automaticamente)
- Marcar se é custo recorrente
- Observações

**Cálculos Automáticos**:
O sistema calcula e exibe em tempo real:

1. **Valor Bruto do Contrato**: Valor total que o cliente pagará
2. **Total de Custos**: Soma de todos os custos cadastrados
3. **Valor Líquido**: Valor Bruto - Total de Custos
4. **Margem Líquida**: Percentual de lucro real

### Exemplo Prático

**Contrato de Serviço**: R$ 10.000,00

**Custos**:
- Mão de obra: R$ 3.000,00
- Materiais: R$ 1.500,00
- Transporte: R$ 500,00
- **Total de Custos**: R$ 5.000,00

**Resultado**:
- **Valor Líquido**: R$ 5.000,00
- **Margem Líquida**: 50%

### Custos Recorrentes

Marque a opção "Custo Recorrente" para custos que se repetem durante todo o contrato (como salários mensais). O sistema totaliza separadamente os custos recorrentes.

### Acesso

Menu **Produtos > Contratos** > Botão **CMV** em cada contrato

---

## 📊 Estrutura do Banco de Dados

### Novas Tabelas

1. **contratos**: Armazena produtos/serviços contratados
2. **cmv**: Armazena custos de cada contrato
3. **boletos**: Armazena boletos gerados via API

### Script de Atualização

Execute o arquivo `database_update.sql` no phpMyAdmin para criar as novas tabelas:

```sql
-- Copie e execute todo o conteúdo de database_update.sql
```

---

## 🗂️ Novos Arquivos Criados

### Bibliotecas
- `lib_boleto_stripe.php` - Integração Stripe
- `lib_boleto_cora.php` - Integração CORA

### Módulo Contratos
- `contratos.php` - Listagem de contratos
- `contrato_form.php` - Formulário de cadastro/edição
- `contrato_delete.php` - Exclusão de contrato

### Módulo CMV
- `contrato_cmv.php` - Tela de CMV com custos
- `cmv_delete.php` - Exclusão de custo

### Módulo Boletos
- `boletos.php` - Visualização de boletos gerados

### Banco de Dados
- `database_update.sql` - Script de atualização

### Diretórios
- `uploads/contratos/` - Armazenamento de contratos anexados

---

## 🚀 Como Atualizar

### Passo 1: Fazer Backup

**IMPORTANTE**: Faça backup completo do banco de dados antes de atualizar!

### Passo 2: Upload dos Novos Arquivos

1. Faça upload de todos os novos arquivos para o servidor
2. Certifique-se de que a pasta `uploads/contratos/` foi criada
3. Configure permissões 755 para a pasta `uploads/`

### Passo 3: Atualizar Banco de Dados

1. Acesse phpMyAdmin
2. Selecione o banco `inlaud99_erpinlaudo`
3. Vá na aba **SQL**
4. Copie todo o conteúdo de `database_update.sql`
5. Cole e execute

### Passo 4: Configurar Integrações

1. Acesse **Integrações > Boleto (CORA/Stripe)**
2. Configure as credenciais de API
3. Ative a integração desejada

### Passo 5: Testar

1. Crie um contrato de teste
2. Adicione custos no CMV
3. Crie uma conta a receber com boleto
4. Verifique se o boleto foi gerado

---

## ⚙️ Configurações Necessárias

### Permissões de Arquivo

```bash
chmod 755 uploads/
chmod 755 uploads/contratos/
```

### Extensões PHP Necessárias

- cURL (para chamadas de API)
- PDO MySQL
- FileInfo (para upload de arquivos)

### Integrações

**Para usar Stripe**:
1. Crie conta em https://stripe.com
2. Obtenha Publishable Key e Secret Key
3. Configure em Integrações > Boleto

**Para usar CORA**:
1. Crie conta em https://cora.com.br
2. Obtenha API Key e API Secret
3. Configure em Integrações > Boleto

---

## 🎯 Fluxo de Trabalho Recomendado

### 1. Cadastrar Cliente
Menu **CRM > Clientes**

### 2. Criar Contrato
Menu **Produtos > Contratos**
- Selecione o cliente
- Defina produto/serviço
- Configure recorrência
- Anexe contrato
- Marque para gerar contas a receber

### 3. Calcular CMV
Clique em **CMV** no contrato
- Adicione todos os custos
- Veja margem líquida em tempo real

### 4. Gerar Boletos
As contas a receber já foram criadas automaticamente
- Edite cada conta se necessário
- Ou crie novas manualmente
- Marque para gerar boleto via API

### 5. Acompanhar
- **Produtos > Contratos**: Status dos contratos
- **Financeiro > Contas a Receber**: Status de pagamentos
- **Integrações > Boletos Gerados**: Boletos e códigos

---

## 📈 Benefícios da Atualização

### Automação
- Geração automática de boletos
- Criação automática de contas a receber
- Cálculos automáticos de CMV

### Controle Financeiro
- Visão clara de custos vs receitas
- Margem líquida por contrato
- Acompanhamento de boletos

### Profissionalismo
- Boletos bancários oficiais
- Contratos organizados e anexados
- Análise de rentabilidade

### Integração
- Tudo conectado: Contratos → Contas a Receber → Boletos
- Dados centralizados
- Relatórios completos

---

## 🆘 Suporte e Problemas Comuns

### Erro ao Gerar Boleto

**Problema**: "Integração não está ativa"
**Solução**: Configure e ative a integração em Integrações > Boleto

**Problema**: "Erro na API"
**Solução**: Verifique se as credenciais estão corretas

### Erro ao Upload de Contrato

**Problema**: "Arquivo não foi enviado"
**Solução**: Verifique permissões da pasta uploads/

### CMV Não Calcula

**Problema**: Valores não aparecem
**Solução**: Certifique-se de que executou o database_update.sql

---

## 📝 Notas de Versão

**Versão**: 2.0
**Data**: Dezembro 2024
**Compatibilidade**: Requer versão 1.0 instalada

**Novos Recursos**:
- ✅ Geração automática de boletos (Stripe e CORA)
- ✅ Módulo de Produtos/Serviços com contratos
- ✅ Upload de arquivos de contrato
- ✅ Módulo CMV para cálculo de custos
- ✅ Integração completa entre módulos
- ✅ Visualização de boletos gerados

**Melhorias**:
- ✅ Menu de navegação atualizado
- ✅ Novos dashboards com totalizadores
- ✅ Cálculos automáticos em tempo real
- ✅ Interface aprimorada para boletos

---

## 🔮 Próximas Melhorias Sugeridas

1. Webhook para atualização automática de status de boletos
2. Relatórios de rentabilidade por cliente
3. Gráficos de CMV e margens
4. Exportação de contratos em PDF
5. Notificações de boletos próximos ao vencimento
6. Dashboard executivo com KPIs

---

**Sistema ERP INLAUDO - Versão 2.0**
**Desenvolvido para INLAUDO - Conectando Saúde e Tecnologia**
