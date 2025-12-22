# ERP INLAUDO
## Sistema de Gestão Empresarial Completo

![INLAUDO](LOGOBRANCA.png)

**INLAUDO - Conectando Saúde e Tecnologia**

---

## 📋 Sobre o Sistema

O ERP INLAUDO é um sistema completo de gestão empresarial desenvolvido especialmente para a INLAUDO, integrando módulos de CRM, Financeiro, Faturamento, Produtos/Serviços e Integrações com APIs externas.

### Principais Funcionalidades

#### 🤝 CRM (Customer Relationship Management)
- **Cadastro de Clientes**: Gestão completa de clientes com busca automática de dados via CNPJ
- **Interações**: Histórico completo de contatos com clientes, lembretes e próximas ações
- **Classificação**: Separação entre LEADS e CLIENTES

#### 💰 Financeiro
- **Contas a Receber**: Gestão de recebíveis com geração automática de boletos
- **Contas a Pagar**: Controle de pagamentos e fornecedores
- **Plano de Contas**: Categorização de receitas e despesas

#### 📄 Produtos/Serviços (Contratos)
- **Cadastro de Contratos**: Produtos e serviços vinculados a clientes
- **Upload de Documentos**: Anexar contratos em PDF
- **Status**: Controle de contratos ativos e inativos
- **CMV (Custo de Mercadoria Vendida)**: Cálculo de custos e margem líquida por contrato

#### 💳 Faturamento
- **Integração Stripe**: Geração automática de faturas (Invoices)
- **Acompanhamento**: Status de faturas em tempo real
- **E-mails Automáticos**: Notificações de cobrança

#### 🔗 Integrações
- **CORA API v2**: Emissão de boletos registrados com mTLS
- **Stripe**: Faturamento e cobranças
- **E-mail SMTP**: Envio de alertas e notificações
- **APIs de CNPJ**: ReceitaWS e BrasilAPI

#### 📧 Sistema de E-mails
- **Configuração SMTP**: Suporte a Gmail, Outlook e outros
- **Templates Personalizáveis**: Editor de templates HTML
- **Alertas Automáticos**: Contas a pagar, contas a receber, interações
- **Histórico**: Rastreamento completo de e-mails enviados

#### 📊 Logs e Monitoramento
- **Logs de Integração**: Rastreamento de todas as chamadas de API
- **Histórico de E-mails**: Todos os e-mails enviados
- **Alertas Programados**: Gerenciamento de alertas agendados

---

## 🚀 Tecnologias

- **Backend**: PHP 7.4+ (procedural)
- **Frontend**: HTML5, CSS3, JavaScript
- **Banco de Dados**: MySQL 5.7+
- **Servidor Web**: Apache com mod_rewrite
- **APIs**: CORA v2, Stripe, ReceitaWS, BrasilAPI

---

## 📦 Instalação

### Requisitos
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Apache com mod_rewrite habilitado
- Extensões PHP: mysqli, curl, json, openssl

### Passo a Passo

1. **Clone o repositório**
```bash
git clone https://github.com/ASOARESBH/erpinlaudo.git
cd erpinlaudo
```

2. **Configure o banco de dados**
```bash
# Crie o banco de dados
mysql -u root -p -e "CREATE DATABASE inlaud99_erpinlaudo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Importe a estrutura
mysql -u root -p inlaud99_erpinlaudo < database.sql
```

3. **Configure as credenciais**
```bash
# Copie o arquivo de configuração de exemplo
cp config.php.example config.php

# Edite com suas credenciais
nano config.php
```

4. **Configure permissões**
```bash
chmod 755 certs/
chmod 755 uploads/
chmod 755 logs/
```

5. **Acesse o sistema**
```
http://seudominio.com/
```

---

## 🔧 Configuração

### Banco de Dados
Edite o arquivo `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inlaud99_erpinlaudo');
define('DB_USER', 'inlaud99_admin');
define('DB_PASS', 'sua_senha');
```

### Integração CORA
1. Acesse **Integrações > Boleto (CORA/Stripe)**
2. Configure:
   - Client-ID
   - Ambiente (Produção/Teste)
   - Upload de certificado e chave privada
3. Ative a integração
4. Teste a conexão

### Integração Stripe
1. Acesse **Integrações > Boleto (CORA/Stripe)**
2. Configure:
   - API Key (Publishable Key)
   - API Secret (Secret Key)
3. Ative a integração

### E-mail SMTP
1. Acesse **Integrações > E-mail Config**
2. Configure servidor SMTP
3. Teste o envio
4. Configure templates em **Templates de E-mail**

---

## 📚 Documentação

### Arquivos de Documentação
- `README.md` - Documentação principal do sistema
- `INSTALACAO.txt` - Instruções rápidas de instalação
- `ATUALIZACAO_V2.md` - Atualização v2 (Produtos/Serviços e CMV)
- `ATUALIZACAO_V3.md` - Atualização v3 (Menu fixo e Faturamento Stripe)
- `ATUALIZACAO_V4.md` - Atualização v4 (Sistema de E-mails)
- `ATUALIZACAO_CORA_V2.md` - Integração CORA API v2 com mTLS

### Scripts SQL
- `database.sql` - Estrutura inicial do banco
- `database_update.sql` - Atualização v2
- `database_update_v3.sql` - Atualização v3
- `database_update_v4.sql` - Atualização v4
- `database_update_cora_v2.sql` - Atualização CORA v2

### Scripts de Teste
- `teste_stripe.php` - Teste da integração Stripe
- `teste_cora_v2.php` - Teste da integração CORA v2

---

## 🔐 Segurança

### Arquivos Sensíveis (não versionados)
- `config.php` - Credenciais do banco de dados
- `certs/*.pem` - Certificados digitais
- `certs/*.key` - Chaves privadas
- `logs/` - Arquivos de log

### Boas Práticas
- Mantenha o PHP atualizado
- Use senhas fortes no banco de dados
- Configure SSL/HTTPS no servidor
- Faça backups regulares
- Monitore os logs de integração

---

## 📊 Estrutura do Banco de Dados

### Tabelas Principais
- `clientes` - Cadastro de clientes
- `interacoes` - Histórico de interações
- `contas_receber` - Contas a receber
- `contas_pagar` - Contas a pagar
- `plano_contas` - Plano de contas
- `contratos` - Produtos/serviços contratados
- `cmv_custos` - Custos dos contratos (CMV)
- `boletos` - Boletos gerados
- `integracoes` - Configurações de integrações
- `email_config` - Configurações de e-mail
- `email_templates` - Templates de e-mail
- `email_historico` - Histórico de e-mails
- `alertas_programados` - Alertas agendados
- `logs_integracao` - Logs de APIs

---

## 🛠️ Desenvolvimento

### Estrutura de Arquivos
```
erpinlaudo/
├── *.php                    # Páginas do sistema
├── lib_*.php               # Bibliotecas e classes
├── config.php              # Configurações (não versionado)
├── database*.sql           # Scripts SQL
├── style.css               # Estilos CSS
├── .htaccess               # Configuração Apache
├── certs/                  # Certificados (não versionados)
├── uploads/                # Uploads (não versionados)
├── logs/                   # Logs (não versionados)
└── README.md               # Documentação
```

### Convenções de Código
- **PHP**: Estilo procedural, funções claras e documentadas
- **SQL**: Prepared statements para segurança
- **HTML**: Semântico e acessível
- **CSS**: Classes descritivas, mobile-first

### Contribuindo
1. Fork o projeto
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

---

## 📞 Suporte

### Problemas com o Sistema
- Verifique os logs em **Integrações > Logs de Integração**
- Consulte a documentação específica de cada módulo
- Execute os scripts de teste

### Problemas com Integrações
- **CORA**: Execute `teste_cora_v2.php`
- **Stripe**: Execute `teste_stripe.php`
- **E-mail**: Verifique **Histórico de E-mails**

---

## 📈 Roadmap

### Versão Atual: 4.0 + CORA v2
- ✅ CRM completo
- ✅ Financeiro completo
- ✅ Produtos/Serviços com CMV
- ✅ Faturamento Stripe
- ✅ Boletos CORA v2 com mTLS
- ✅ Sistema de E-mails
- ✅ Logs e Monitoramento

### Próximas Versões
- [ ] Dashboard com gráficos
- [ ] Relatórios em PDF
- [ ] API REST própria
- [ ] App mobile
- [ ] Webhooks CORA/Stripe
- [ ] Backup automático
- [ ] Multi-empresa

---

## 📄 Licença

Este projeto é proprietário e de uso exclusivo da INLAUDO.

---

## 👥 Equipe

**Desenvolvido para**: INLAUDO - Conectando Saúde e Tecnologia  
**Desenvolvedor**: Sistema ERP INLAUDO  
**Contato**: asoaresbh@gmail.com

---

## 🎯 Status do Projeto

![Status](https://img.shields.io/badge/status-em%20produ%C3%A7%C3%A3o-success)
![Versão](https://img.shields.io/badge/vers%C3%A3o-4.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)

**Última Atualização**: Dezembro 2025
