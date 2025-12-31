# 📁 Sistema de Documentos - Contas a Receber

## 📋 Visão Geral

Sistema completo para upload, gerenciamento e download de documentos vinculados às contas a receber, com acesso controlado para clientes via portal.

---

## 🎯 Funcionalidades Implementadas

### 1. Gerenciamento Administrativo

**Página**: `conta_receber_documentos.php`

**Funcionalidades**:
- ✅ Upload de múltiplos documentos por conta
- ✅ Categorização por tipo (NF, Boleto, Comprovante, Contrato, Recibo, Outro)
- ✅ Controle de visibilidade (visível/privado para cliente)
- ✅ Visualização de lista completa de documentos
- ✅ Download de documentos
- ✅ Exclusão de documentos
- ✅ Informações detalhadas (tamanho, data, usuário)

**Tipos de Documentos Suportados**:
- 📄 **Nota Fiscal** (NF)
- 🎫 **Boleto**
- ✅ **Comprovante de Pagamento**
- 📝 **Contrato**
- 🧾 **Recibo**
- 📎 **Outro**

**Formatos Permitidos**:
- PDF, XML, JPG, JPEG, PNG, DOC, DOCX, XLS, XLSX, ZIP
- Tamanho máximo: 10MB por arquivo

### 2. Portal do Cliente

**Página**: `cliente_documentos.php`

**Funcionalidades**:
- ✅ Visualização de documentos da conta
- ✅ Filtro automático (apenas documentos visíveis)
- ✅ Informações da conta
- ✅ Visualização online de documentos
- ✅ Download de documentos
- ✅ Interface responsiva e amigável

**Acesso**:
- Através do menu "Meu Financeiro"
- Botão "📁 Ver Documentos" em cada conta

---

## 🗄️ Estrutura do Banco de Dados

### Nova Tabela: documentos_contas_receber

```sql
CREATE TABLE documentos_contas_receber (
  id INT(11) PRIMARY KEY AUTO_INCREMENT,
  conta_receber_id INT(11) NOT NULL,
  tipo_documento VARCHAR(50) NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  descricao TEXT NULL,
  nome_arquivo VARCHAR(255) NOT NULL,
  caminho_arquivo VARCHAR(500) NOT NULL,
  tamanho_arquivo INT(11) NULL,
  extensao VARCHAR(10) NULL,
  visivel_cliente TINYINT(1) DEFAULT 1,
  data_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  usuario_upload_id INT(11) NULL,
  
  INDEX idx_conta_receber (conta_receber_id),
  INDEX idx_tipo_documento (tipo_documento),
  INDEX idx_visivel_cliente (visivel_cliente),
  
  FOREIGN KEY (conta_receber_id) 
    REFERENCES contas_receber(id) 
    ON DELETE CASCADE
);
```

**Campos**:
- `id`: ID único do documento
- `conta_receber_id`: ID da conta a receber (FK)
- `tipo_documento`: Tipo (nf, boleto, comprovante, contrato, recibo, outro)
- `titulo`: Título do documento
- `descricao`: Descrição opcional
- `nome_arquivo`: Nome original do arquivo
- `caminho_arquivo`: Caminho completo no servidor
- `tamanho_arquivo`: Tamanho em bytes
- `extensao`: Extensão do arquivo
- `visivel_cliente`: 1 = visível, 0 = privado
- `data_upload`: Data/hora do upload
- `usuario_upload_id`: ID do usuário que fez upload

---

## 📂 Estrutura de Arquivos

### Diretório de Upload
```
uploads/documentos_contas/
├── doc_1_1703285123_abc123.pdf
├── doc_1_1703285456_def456.xml
├── doc_2_1703285789_ghi789.jpg
└── ...
```

**Padrão de Nomenclatura**:
```
doc_{conta_id}_{timestamp}_{uniqid}.{extensao}
```

### Arquivos Criados/Atualizados

1. **CREATE_DOCUMENTOS_CONTAS.sql** - Script de criação da tabela
2. **conta_receber_documentos.php** - Gerenciamento admin (NOVO)
3. **cliente_documentos.php** - Visualização cliente (NOVO)
4. **contas_receber.php** - Botão "Documentos" adicionado (ATUALIZADO)
5. **cliente_financeiro.php** - Coluna "Documentos" adicionada (ATUALIZADO)

---

## 🚀 Como Usar

### Para Administradores

#### 1. Acessar Gerenciamento de Documentos
```
Contas a Receber > Localizar conta > Botão "📁 Documentos"
```

#### 2. Enviar Documento
1. Selecionar tipo de documento
2. Preencher título (obrigatório)
3. Adicionar descrição (opcional)
4. Escolher arquivo (máx. 10MB)
5. Marcar "Visível para o cliente" (se aplicável)
6. Clicar em "📤 Enviar Documento"

#### 3. Gerenciar Documentos
- **Visualizar**: Clicar em "👁️ Ver"
- **Baixar**: Clicar em "⬇️ Baixar"
- **Excluir**: Clicar em "🗑️ Excluir" (confirmar)

### Para Clientes

#### 1. Acessar Documentos
```
Portal do Cliente > Meu Financeiro > Clicar em "📁 Ver Documentos"
```

#### 2. Visualizar/Baixar
1. Localizar documento desejado
2. Clicar em "👁️ Visualizar" (abre em nova aba)
3. Ou clicar em "⬇️ Baixar" (download direto)

---

## 🎨 Interface

### Página Administrativa

**Seções**:
1. **Header**: Informações da conta e cliente
2. **Formulário de Upload**: Campos para novo documento
3. **Lista de Documentos**: Tabela com todos os documentos

**Cores por Tipo**:
- 📄 NF: Azul (#3b82f6)
- 🎫 Boleto: Amarelo (#f59e0b)
- ✅ Comprovante: Verde (#10b981)
- 📝 Contrato: Roxo (#8b5cf6)
- 🧾 Recibo: Cinza (#64748b)
- 📎 Outro: Cinza (#64748b)

### Portal do Cliente

**Seções**:
1. **Informações da Conta**: Descrição, valor, vencimento, status
2. **Lista de Documentos**: Cards visuais com documentos

**Design**:
- Cards grandes e visuais
- Ícones por tipo de documento
- Botões destacados para ações
- Responsivo para mobile

---

## 🔒 Segurança

### Controle de Acesso

**Administrativo**:
- Requer login de usuário
- Acesso a todos os documentos
- Pode marcar/desmarcar visibilidade

**Cliente**:
- Requer login de cliente
- Acesso apenas a documentos da própria conta
- Vê apenas documentos marcados como "visível"
- Não pode excluir ou editar

### Validações

**Upload**:
- ✅ Extensões permitidas verificadas
- ✅ Tamanho máximo de 10MB
- ✅ Título obrigatório
- ✅ Conta deve existir e pertencer ao cliente

**Download**:
- ✅ Cliente só acessa seus documentos
- ✅ Verificação de visibilidade
- ✅ Arquivos servidos diretamente (sem listagem de diretório)

---

## 📊 Exemplos de Uso

### Exemplo 1: Enviar Nota Fiscal

**Admin**:
1. Acessar conta a receber
2. Clicar em "📁 Documentos"
3. Selecionar tipo: "Nota Fiscal"
4. Título: "NF 12345 - Serviços Dezembro"
5. Descrição: "Nota fiscal referente aos serviços prestados em dezembro"
6. Upload do arquivo PDF
7. Marcar "Visível para o cliente"
8. Enviar

**Cliente**:
1. Acessar "Meu Financeiro"
2. Localizar conta
3. Clicar em "📁 Ver Documentos"
4. Ver NF disponível
5. Clicar em "👁️ Visualizar" ou "⬇️ Baixar"

### Exemplo 2: Enviar Comprovante (Privado)

**Admin**:
1. Acessar documentos da conta
2. Tipo: "Comprovante de Pagamento"
3. Título: "Comprovante Interno - Transferência"
4. Upload do arquivo
5. **Desmarcar** "Visível para o cliente"
6. Enviar

**Resultado**:
- Admin vê o comprovante
- Cliente **não** vê o comprovante

### Exemplo 3: Múltiplos Documentos

**Cenário**: Conta com NF, Boleto e Contrato

**Admin envia**:
1. NF (visível)
2. Boleto (visível)
3. Contrato (visível)
4. Comprovante interno (privado)

**Cliente vê**:
- ✅ NF
- ✅ Boleto
- ✅ Contrato
- ❌ Comprovante interno

---

## 🐛 Solução de Problemas

### Erro ao fazer upload

**Possíveis Causas**:
- Arquivo muito grande (>10MB)
- Extensão não permitida
- Sem permissão de escrita na pasta

**Solução**:
1. Verificar tamanho do arquivo
2. Verificar extensão
3. Verificar permissões da pasta `uploads/documentos_contas/` (755)

### Cliente não vê documentos

**Possíveis Causas**:
- Documento marcado como privado
- Cliente acessando conta errada
- Documento não foi enviado

**Solução**:
1. Verificar se documento está marcado como "Visível"
2. Verificar se conta pertence ao cliente
3. Verificar se upload foi concluído

### Arquivo não abre

**Possíveis Causas**:
- Arquivo corrompido
- Caminho incorreto
- Arquivo foi excluído

**Solução**:
1. Verificar se arquivo existe no servidor
2. Fazer novo upload
3. Verificar logs de erro

---

## ✅ Checklist de Instalação

### Banco de Dados
- [ ] Executar script `CREATE_DOCUMENTOS_CONTAS.sql`
- [ ] Verificar se tabela foi criada
- [ ] Verificar foreign key

### Arquivos
- [ ] Upload de `conta_receber_documentos.php`
- [ ] Upload de `cliente_documentos.php`
- [ ] Atualizar `contas_receber.php`
- [ ] Atualizar `cliente_financeiro.php`

### Diretórios
- [ ] Criar pasta `uploads/documentos_contas/`
- [ ] Definir permissões 755
- [ ] Testar escrita

### Testes
- [ ] Teste de upload (admin)
- [ ] Teste de visualização (admin)
- [ ] Teste de exclusão (admin)
- [ ] Teste de visualização (cliente)
- [ ] Teste de download (cliente)
- [ ] Teste de visibilidade (privado/público)

---

## 📈 Benefícios

### Para a Empresa

✅ **Organização**: Todos os documentos centralizados  
✅ **Rastreabilidade**: Histórico completo de uploads  
✅ **Controle**: Visibilidade configurável  
✅ **Profissionalismo**: Portal moderno para clientes  
✅ **Auditoria**: Registro de quem enviou e quando  

### Para os Clientes

✅ **Acesso 24/7**: Documentos disponíveis a qualquer hora  
✅ **Praticidade**: Download direto pelo portal  
✅ **Organização**: Documentos agrupados por conta  
✅ **Transparência**: Acesso imediato a NFs e comprovantes  
✅ **Mobilidade**: Acesso via celular  

---

## 🔄 Fluxo Completo

```
1. Admin cria conta a receber
   ↓
2. Admin acessa "Documentos"
   ↓
3. Admin envia NF (visível)
   ↓
4. Cliente recebe notificação (opcional)
   ↓
5. Cliente acessa portal
   ↓
6. Cliente vai em "Meu Financeiro"
   ↓
7. Cliente clica em "Ver Documentos"
   ↓
8. Cliente visualiza/baixa NF
   ↓
9. Cliente efetua pagamento
   ↓
10. Admin envia comprovante (privado)
   ↓
11. Admin marca conta como "paga"
   ↓
✅ Ciclo completo
```

---

## 🎯 Próximos Passos Sugeridos

### Curto Prazo
1. Testar em produção
2. Treinar equipe administrativa
3. Comunicar clientes sobre nova funcionalidade

### Médio Prazo
4. Adicionar notificação por e-mail quando documento for enviado
5. Permitir upload de múltiplos arquivos de uma vez
6. Adicionar visualizador de PDF inline

### Longo Prazo
7. Assinatura digital de documentos
8. Versionamento de documentos
9. Compartilhamento de documentos entre contas

---

**Versão**: 7.2  
**Data**: 22/12/2025  
**Status**: ✅ Pronto para Produção  
**Arquivos**: 5 (2 novos + 2 atualizados + 1 SQL)
