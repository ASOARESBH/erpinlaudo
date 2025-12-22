# Atualização V4 - Sistema de E-mails e Alertas Automáticos

## 📧 Visão Geral

A versão 4.0 do ERP INLAUDO adiciona um **sistema completo de configuração de e-mails e templates personalizáveis** para alertas automáticos. Agora você pode configurar o servidor SMTP, criar templates de e-mail customizados e receber alertas automáticos sobre eventos importantes do sistema.

---

## 🎯 Novas Funcionalidades

### 1. Configuração de E-mail SMTP

**Localização**: Integrações > E-mail Config

Permite configurar um ou mais servidores SMTP para envio de e-mails:

- Suporte a Gmail, Outlook e outros provedores
- Configuração de TLS/SSL
- Modo de teste (redireciona todos os e-mails para um endereço de teste)
- Teste de configuração antes de ativar
- Múltiplas configurações (apenas uma ativa por vez)

**Provedores Suportados**:
- Gmail (smtp.gmail.com:587)
- Outlook/Hotmail (smtp-mail.outlook.com:587)
- Qualquer servidor SMTP customizado

### 2. Templates de E-mail Personalizáveis

**Localização**: Integrações > Templates de E-mail

Sistema completo de templates com editor visual:

- Criação de templates HTML e texto puro
- Sistema de variáveis dinâmicas ({{variavel}})
- Categorização (alerta, notificação, relatório, cobrança, sistema)
- Configuração de envio automático
- Dias de antecedência para alertas preventivos
- Destinatários padrão por template
- Teste de template antes de usar

**Templates Padrão Incluídos**:
1. **Alerta - Conta a Pagar Vencendo**: Notifica sobre contas próximas do vencimento
2. **Alerta - Conta a Receber Vencida**: Alerta sobre contas vencidas de clientes
3. **Lembrete - Próxima Interação com Cliente**: Lembra de interações agendadas

### 3. Histórico de E-mails Enviados

**Localização**: Integrações > Histórico de E-mails

Rastreamento completo de todos os e-mails enviados:

- Dashboard com estatísticas (total, sucessos, erros)
- Filtros por status, destinatário e período
- Visualização do conteúdo completo do e-mail
- Mensagens de erro detalhadas
- Paginação automática
- Últimos 30 dias em destaque

### 4. Alertas Programados

**Localização**: Integrações > Alertas Programados

Gerenciamento de alertas agendados:

- Visualização de todos os alertas programados
- Status (pendente, enviado, erro, cancelado)
- Cancelamento de alertas pendentes
- Reenvio de alertas com erro
- Estatísticas de envio
- Filtros por status e tipo

### 5. Processamento Automático de Alertas

**Script**: `processar_alertas.php`

Script que deve ser executado diariamente via CRON:

**Alertas Processados**:
- Contas a pagar vencendo (baseado em dias de antecedência)
- Contas a receber vencidas
- Próximas interações com clientes
- Alertas programados pendentes

**Configuração CRON**:
```bash
0 9 * * * /usr/bin/php /caminho/completo/para/erp-inlaudo/processar_alertas.php
```

---

## 🗄️ Banco de Dados

### Novas Tabelas (4)

#### 1. email_config
Armazena configurações de servidores SMTP.

**Campos principais**:
- nome_config, smtp_host, smtp_port, smtp_secure
- smtp_user, smtp_password
- from_email, from_name, reply_to_email
- ativo, testar_envio, email_teste

#### 2. email_templates
Armazena templates de e-mail personalizáveis.

**Campos principais**:
- codigo (único), nome, descricao, categoria
- assunto, corpo_html, corpo_texto
- variaveis_disponiveis (JSON)
- ativo, enviar_automatico, dias_antecedencia
- destinatarios_padrao

#### 3. email_historico
Registra histórico de todos os e-mails enviados.

**Campos principais**:
- template_id, destinatario, destinatario_nome
- assunto, corpo_html
- status (enviado, erro, pendente)
- mensagem_erro
- referencia_tipo, referencia_id
- ip_origem, data_envio

#### 4. alertas_programados
Gerencia alertas agendados para envio futuro.

**Campos principais**:
- template_id, tipo_alerta
- referencia_tipo, referencia_id
- destinatario_email
- data_programada, hora_programada
- status (pendente, enviado, cancelado, erro)
- tentativas, data_envio, mensagem_erro

---

## 📁 Novos Arquivos

### Biblioteca
- `lib_email.php` - Biblioteca completa de envio de e-mails

### Páginas
- `email_config.php` - Configuração de e-mail SMTP
- `email_templates.php` - Gerenciamento de templates
- `email_historico.php` - Histórico de e-mails enviados
- `alertas_programados.php` - Gerenciamento de alertas

### Scripts
- `processar_alertas.php` - Processamento automático de alertas

### Banco de Dados
- `database_update_v4.sql` - Script de atualização do banco

---

## 🚀 Como Usar

### Passo 1: Configurar E-mail SMTP

1. Acesse **Integrações > E-mail Config**
2. Clique em "Nova Configuração"
3. Preencha os dados do servidor SMTP:
   - **Gmail**: smtp.gmail.com:587 (TLS)
   - **Outlook**: smtp-mail.outlook.com:587 (TLS)
4. Para Gmail, use **Senha de App** (não a senha normal):
   - Acesse https://myaccount.google.com/security
   - Ative "Verificação em duas etapas"
   - Vá em "Senhas de app"
   - Gere uma senha para "E-mail"
5. Marque "Configuração Ativa"
6. Salve e teste o envio

### Passo 2: Configurar Templates

1. Acesse **Integrações > Templates de E-mail**
2. Edite os templates padrão ou crie novos
3. Para ativar envio automático:
   - Marque "Enviar Automaticamente"
   - Configure "Dias de Antecedência"
   - Defina "Destinatários Padrão" (e-mails separados por vírgula)
4. Use variáveis no formato `{{variavel}}` para conteúdo dinâmico
5. Teste o template antes de ativar

### Passo 3: Configurar CRON

Para alertas automáticos, configure o CRON:

```bash
# Editar crontab
crontab -e

# Adicionar linha (executar diariamente às 9h)
0 9 * * * /usr/bin/php /caminho/completo/para/erp-inlaudo/processar_alertas.php
```

### Passo 4: Monitorar

- **Histórico de E-mails**: Veja todos os e-mails enviados
- **Alertas Programados**: Acompanhe alertas agendados
- **Logs**: Verifique erros e problemas

---

## 🎨 Variáveis Disponíveis nos Templates

### Contas a Pagar
- `{{descricao}}` - Descrição da conta
- `{{fornecedor}}` - Nome do fornecedor
- `{{valor}}` - Valor formatado
- `{{data_vencimento}}` - Data de vencimento
- `{{dias_restantes}}` - Dias até vencer
- `{{plano_contas}}` - Categoria do plano de contas
- `{{link_sistema}}` - Link para o sistema

### Contas a Receber
- `{{cliente}}` - Nome do cliente
- `{{descricao}}` - Descrição da conta
- `{{valor}}` - Valor formatado
- `{{data_vencimento}}` - Data de vencimento
- `{{dias_atraso}}` - Dias em atraso
- `{{contato_cliente}}` - E-mail ou telefone
- `{{link_sistema}}` - Link para o sistema

### Interações
- `{{cliente}}` - Nome do cliente
- `{{data_hora}}` - Data e hora da interação
- `{{forma_contato}}` - Forma de contato (telefone, e-mail, etc)
- `{{contato_cliente}}` - Telefone ou e-mail
- `{{historico}}` - Resumo do último contato
- `{{link_sistema}}` - Link para o sistema

---

## 🔧 Instalação da Atualização

### 1. Backup
Faça backup completo do banco de dados e dos arquivos.

### 2. Upload dos Arquivos
Faça upload de todos os arquivos novos para o servidor.

### 3. Atualizar Banco de Dados
Execute o script SQL no phpMyAdmin:
```sql
-- Selecione o banco inlaud99_erpinlaudo
-- Importe o arquivo database_update_v4.sql
```

### 4. Criar Diretório de Logs
```bash
mkdir -p /caminho/para/erp-inlaudo/logs
chmod 755 /caminho/para/erp-inlaudo/logs
```

### 5. Configurar Permissões
```bash
chmod 644 /caminho/para/erp-inlaudo/*.php
chmod 755 /caminho/para/erp-inlaudo/processar_alertas.php
```

---

## ⚠️ Observações Importantes

### Gmail
- Use **Senha de App**, não a senha normal
- Ative "Verificação em duas etapas" antes
- Limite de 500 e-mails por dia (conta gratuita)

### Outlook
- Use a senha normal da conta
- Limite de 300 e-mails por dia (conta gratuita)

### Modo de Teste
- Ative "Modo de Teste" para testar sem enviar e-mails reais
- Todos os e-mails serão redirecionados para o e-mail de teste

### CRON
- Ajuste o horário conforme necessário
- Verifique os logs em `/caminho/para/erp-inlaudo/logs/`
- Teste manualmente antes de configurar o CRON

---

## 📊 Benefícios

### Automação
- Alertas automáticos de vencimentos
- Lembretes de interações
- Notificações de inadimplência

### Profissionalismo
- E-mails personalizados com logo
- Templates HTML responsivos
- Comunicação consistente

### Controle
- Histórico completo de envios
- Rastreamento de erros
- Estatísticas de entrega

### Flexibilidade
- Templates personalizáveis
- Múltiplas configurações SMTP
- Modo de teste seguro

---

## 🐛 Solução de Problemas

### E-mails não estão sendo enviados

1. Verifique a configuração SMTP em "E-mail Config"
2. Teste a configuração com "Enviar E-mail de Teste"
3. Verifique o histórico de e-mails para mensagens de erro
4. Confirme que a configuração está marcada como "Ativa"

### Gmail retorna erro de autenticação

1. Certifique-se de usar **Senha de App**, não a senha normal
2. Ative "Verificação em duas etapas"
3. Gere uma nova senha de app
4. Verifique se o servidor é `smtp.gmail.com` e porta `587`

### Alertas automáticos não funcionam

1. Verifique se o CRON está configurado corretamente
2. Execute o script manualmente para testar: `php processar_alertas.php`
3. Verifique os logs em `/logs/alertas_YYYY-MM-DD.log`
4. Confirme que os templates têm "Enviar Automaticamente" marcado
5. Verifique se há destinatários padrão configurados

### E-mails vão para spam

1. Configure SPF, DKIM e DMARC no DNS do domínio
2. Use um e-mail do mesmo domínio como remetente
3. Evite palavras como "grátis", "promoção" no assunto
4. Peça aos destinatários para marcarem como "não spam"

---

## 📞 Suporte

Para dúvidas ou problemas, consulte:
- Documentação completa no sistema
- Logs de erro em `/logs/`
- Histórico de e-mails para debugging

---

**Versão**: 4.0  
**Data**: Dezembro 2025  
**Desenvolvido para**: ERP INLAUDO - Conectando Saúde e Tecnologia
