# Atualização - Integração CORA API v2 com mTLS

## 📋 Visão Geral

Esta atualização reimplementa completamente a integração com a API CORA para emissão de boletos registrados, utilizando a **API v2** oficial com autenticação **mTLS (Mutual TLS)** através de certificados digitais.

**Data**: 22 de Dezembro de 2025  
**Versão**: CORA v2 Integration

---

## 🎯 O Que Mudou

### Autenticação
- ❌ **Antes**: API Key e API Secret (método antigo)
- ✅ **Agora**: Client-ID + Certificado mTLS (método oficial)

### API
- ❌ **Antes**: API v1 (descontinuada)
- ✅ **Agora**: API v2 (atual e suportada)

### Segurança
- ❌ **Antes**: Autenticação básica
- ✅ **Agora**: Autenticação mútua com certificados digitais (máxima segurança)

---

## 📦 Arquivos Criados/Atualizados

### Novos Arquivos (4)
1. **lib_boleto_cora_v2.php** - Biblioteca completa da API CORA v2
2. **teste_cora_v2.php** - Script de teste da integração
3. **database_update_cora_v2.sql** - Atualização do banco de dados
4. **ATUALIZACAO_CORA_V2.md** - Esta documentação

### Arquivos Atualizados (2)
1. **integracoes_boleto.php** - Interface de configuração
2. **conta_receber_form.php** - Geração automática de boletos

### Diretório de Certificados
- **certs/** - Diretório para armazenar certificados (permissão 600)
  - certificate.pem
  - private-key.key

---

## 🔧 Instalação

### Passo 1: Fazer Backup
```bash
# Backup do banco de dados
mysqldump -u inlaud99_admin -p inlaud99_erpinlaudo > backup_antes_cora_v2.sql

# Backup dos arquivos
cp -r /caminho/para/erp-inlaudo /caminho/para/erp-inlaudo_backup
```

### Passo 2: Fazer Upload dos Arquivos
1. Faça upload de todos os novos arquivos para o servidor
2. Certifique-se de que o diretório `certs/` existe e tem permissão 755
3. Os certificados dentro de `certs/` devem ter permissão 600

### Passo 3: Atualizar Banco de Dados
```sql
-- No phpMyAdmin ou MySQL:
-- 1. Selecione o banco inlaud99_erpinlaudo
-- 2. Vá em "Importar" ou "SQL"
-- 3. Execute o arquivo database_update_cora_v2.sql
```

Ou via linha de comando:
```bash
mysql -u inlaud99_admin -p inlaud99_erpinlaudo < database_update_cora_v2.sql
```

### Passo 4: Configurar Permissões
```bash
# Permissões do diretório de certificados
chmod 755 /caminho/para/erp-inlaudo/certs
chmod 600 /caminho/para/erp-inlaudo/certs/*

# Permissões dos arquivos PHP
chmod 644 /caminho/para/erp-inlaudo/*.php
```

### Passo 5: Configurar Credenciais CORA
1. Acesse **Integrações > Boleto (CORA/Stripe)**
2. Na seção "Integração CORA":
   - **Client-ID**: int-6f2u3vpjglGsZ8nev37Wm7
   - **Ambiente**: Produção (ou Teste para desenvolvimento)
   - **Certificado**: Faça upload do arquivo `certificate.pem`
   - **Chave Privada**: Faça upload do arquivo `private-key.key`
3. Marque "Integração Ativa"
4. Clique em "Salvar Configurações"
5. Clique em "Testar Conexão" para verificar

---

## 🧪 Testando a Integração

### Teste Automático
1. Acesse: `http://seudominio.com/teste_cora_v2.php`
2. O script irá:
   - Verificar se os certificados existem
   - Criar instância da API
   - Testar conexão
   - Listar boletos existentes
3. Verifique os resultados na tela

### Teste Manual
1. Acesse **Financeiro > Contas a Receber**
2. Clique em "Nova Conta a Receber"
3. Preencha os dados:
   - Selecione um cliente
   - Descrição: "Teste de Boleto CORA v2"
   - Valor: R$ 100,00
   - Vencimento: 7 dias a partir de hoje
   - Forma de Pagamento: **Boleto**
4. Marque "Gerar boleto automaticamente"
5. Selecione plataforma: **CORA**
6. Salve
7. Verifique se o boleto foi gerado em **Integrações > Boletos Gerados**

### Verificar Logs
- Acesse **Integrações > Logs de Integração**
- Filtre por tipo: "cora_api_v2"
- Verifique se há erros

---

## 📚 Como Usar

### Geração Automática de Boletos

Ao criar uma conta a receber:
1. Selecione "Boleto" como forma de pagamento
2. Marque "Gerar boleto automaticamente"
3. Selecione "CORA" como plataforma
4. O sistema irá:
   - Buscar dados do cliente
   - Gerar boleto via API CORA v2
   - Salvar linha digitável, código de barras, PDF
   - Incluir QR Code Pix automático
   - Registrar tudo no banco de dados

### Consultar Boletos Gerados
- Acesse **Integrações > Boletos Gerados**
- Veja todos os boletos com:
  - Linha digitável
  - Link para PDF
  - QR Code Pix
  - Status (pendente, pago, cancelado)

### Cancelar Boleto
- Em **Boletos Gerados**, clique em "Cancelar"
- O sistema enviará requisição para a API CORA
- O boleto será marcado como cancelado

---

## 🔐 Segurança

### Autenticação mTLS
A autenticação mTLS (Mutual TLS) é o método mais seguro de autenticação de APIs:
- **Cliente autentica servidor**: Verifica certificado SSL do servidor CORA
- **Servidor autentica cliente**: Verifica certificado do cliente (você)
- **Criptografia bidirecional**: Toda comunicação é criptografada

### Armazenamento de Certificados
- Certificados são armazenados em `/certs/` com permissão 600
- Apenas o usuário do servidor pode ler os arquivos
- Nunca são expostos publicamente
- Não são incluídos em backups públicos

### Validade dos Certificados
- **Certificado atual**: Válido até 14/12/2026
- **Renovação**: Baixe novos certificados no painel CORA antes do vencimento
- **Alerta**: O sistema não funcionará com certificados vencidos

---

## 🌐 Ambientes

### Ambiente de Teste (Stage)
- **URL**: https://matls-clients.api.stage.cora.com.br
- **Uso**: Desenvolvimento e testes
- **Boletos**: Não são reais, não podem ser pagos
- **Certificados**: Use certificados de teste fornecidos pela CORA

### Ambiente de Produção
- **URL**: https://matls-clients.api.cora.com.br
- **Uso**: Operação real
- **Boletos**: São válidos e podem ser pagos
- **Certificados**: Use certificados de produção (já configurados)

---

## 📊 Estrutura da API v2

### Endpoint de Emissão
```
POST /v2/invoices/
```

### Estrutura de Dados
```json
{
  "code": "CR-123-1234567890",
  "customer": {
    "name": "Nome do Cliente",
    "email": "cliente@email.com",
    "document": {
      "identity": "12345678000190",
      "type": "CNPJ"
    },
    "address": {
      "street": "Rua Exemplo",
      "number": "123",
      "district": "Bairro",
      "city": "São Paulo",
      "state": "SP",
      "zip_code": "01234567",
      "country": "BRA"
    }
  },
  "services": [
    {
      "name": "Serviço",
      "description": "Descrição",
      "amount": 10000
    }
  ],
  "payment_terms": {
    "due_date": "2025-12-30",
    "fine": {
      "rate": 2.0
    },
    "interest": {
      "rate": 1.0
    }
  },
  "pix": {
    "enabled": true
  }
}
```

### Resposta
```json
{
  "id": "inv_abc123",
  "status": "pending",
  "digitable_line": "00190000090320204700900014033179986620000015000",
  "barcode": "00191986600000150000000032020470090001403317",
  "pdf_url": "https://...",
  "pix": {
    "qr_code": "data:image/png;base64,...",
    "emv": "00020126..."
  }
}
```

---

## 🐛 Solução de Problemas

### Erro: "Certificados não encontrados"
**Causa**: Arquivos de certificado não foram carregados  
**Solução**:
1. Acesse Integrações > Boleto
2. Faça upload dos arquivos certificate.pem e private-key.key
3. Salve as configurações

### Erro: "Falha na conexão"
**Causa**: Certificados inválidos ou vencidos  
**Solução**:
1. Verifique a validade dos certificados
2. Baixe novos certificados no painel CORA
3. Faça upload dos novos certificados

### Erro: "Integração CORA não está configurada"
**Causa**: Integração não está ativa  
**Solução**:
1. Acesse Integrações > Boleto
2. Marque "Integração Ativa"
3. Salve as configurações

### Erro: "Request has invalid parameters"
**Causa**: Dados do cliente incompletos  
**Solução**:
1. Verifique se o cliente tem todos os dados cadastrados:
   - Nome/Razão Social
   - CPF/CNPJ
   - Endereço completo (logradouro, número, bairro, cidade, UF, CEP)
   - E-mail
2. Atualize o cadastro do cliente

### Boleto não aparece em "Boletos Gerados"
**Causa**: Erro na geração não foi exibido  
**Solução**:
1. Acesse Integrações > Logs de Integração
2. Filtre por "cora_api_v2"
3. Verifique a mensagem de erro
4. Corrija o problema e tente novamente

---

## 📈 Benefícios da Atualização

### Segurança
- ✅ Autenticação mTLS (máxima segurança)
- ✅ Certificados digitais
- ✅ Criptografia bidirecional

### Funcionalidades
- ✅ API v2 (atual e suportada)
- ✅ QR Code Pix automático
- ✅ Multa e juros configuráveis
- ✅ Desconto por antecipação
- ✅ Logs detalhados

### Confiabilidade
- ✅ API oficial e documentada
- ✅ Suporte da CORA
- ✅ Tratamento robusto de erros
- ✅ Idempotência (evita duplicação)

### Rastreabilidade
- ✅ Logs completos de todas as requisições
- ✅ Histórico de boletos gerados
- ✅ Status em tempo real

---

## 🔗 Links Úteis

- [Documentação Oficial CORA](https://developers.cora.com.br)
- [Painel CORA](https://app.cora.com.br)
- [Obter Credenciais](https://app.cora.com.br) → Conta → Integrações via APIs
- [Logs do Sistema](logs_integracao.php)
- [Boletos Gerados](boletos.php)

---

## 📞 Suporte

### Problemas com a Integração
1. Verifique os logs em **Integrações > Logs de Integração**
2. Execute o teste em `teste_cora_v2.php`
3. Consulte esta documentação

### Problemas com Credenciais CORA
- Entre em contato com o suporte da CORA: suporte@cora.com.br
- Acesse o painel CORA para renovar certificados

---

## ✅ Checklist de Instalação

- [ ] Backup do banco de dados realizado
- [ ] Backup dos arquivos realizado
- [ ] Arquivos novos enviados para o servidor
- [ ] Banco de dados atualizado (database_update_cora_v2.sql)
- [ ] Permissões configuradas (certs/ = 755, certificados = 600)
- [ ] Certificados carregados via interface
- [ ] Client-ID configurado
- [ ] Ambiente selecionado (Produção ou Teste)
- [ ] Integração marcada como "Ativa"
- [ ] Teste de conexão realizado com sucesso
- [ ] Teste de emissão de boleto realizado
- [ ] Logs verificados sem erros

---

**Versão**: CORA v2  
**Data**: 22/12/2025  
**Sistema**: ERP INLAUDO  
**Desenvolvido para**: INLAUDO - Conectando Saúde e Tecnologia
