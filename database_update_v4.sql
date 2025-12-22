-- Atualização do Banco de Dados - ERP INLAUDO V4
-- Novas funcionalidades: Configuração de E-mail e Templates de Alertas

-- Tabela de Configuração de E-mail SMTP
CREATE TABLE IF NOT EXISTS email_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_config VARCHAR(100) NOT NULL COMMENT 'Nome da configuração',
    smtp_host VARCHAR(255) NOT NULL COMMENT 'Servidor SMTP (ex: smtp.gmail.com)',
    smtp_port INT NOT NULL DEFAULT 587 COMMENT 'Porta SMTP (587 para TLS, 465 para SSL)',
    smtp_secure ENUM('tls', 'ssl', 'none') DEFAULT 'tls' COMMENT 'Tipo de criptografia',
    smtp_user VARCHAR(255) NOT NULL COMMENT 'Usuário SMTP (e-mail)',
    smtp_password VARCHAR(255) NOT NULL COMMENT 'Senha SMTP',
    from_email VARCHAR(255) NOT NULL COMMENT 'E-mail remetente',
    from_name VARCHAR(255) NOT NULL COMMENT 'Nome do remetente',
    reply_to_email VARCHAR(255) COMMENT 'E-mail para resposta',
    ativo BOOLEAN DEFAULT TRUE COMMENT 'Configuração ativa',
    testar_envio BOOLEAN DEFAULT FALSE COMMENT 'Modo de teste',
    email_teste VARCHAR(255) COMMENT 'E-mail para testes',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Templates de E-mail
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(100) NOT NULL UNIQUE COMMENT 'Código único do template (ex: conta_pagar_vencendo)',
    nome VARCHAR(255) NOT NULL COMMENT 'Nome descritivo do template',
    descricao TEXT COMMENT 'Descrição do propósito do template',
    assunto VARCHAR(500) NOT NULL COMMENT 'Assunto do e-mail (aceita variáveis)',
    corpo_html TEXT NOT NULL COMMENT 'Corpo do e-mail em HTML (aceita variáveis)',
    corpo_texto TEXT COMMENT 'Corpo do e-mail em texto puro (fallback)',
    variaveis_disponiveis TEXT COMMENT 'JSON com lista de variáveis disponíveis',
    ativo BOOLEAN DEFAULT TRUE COMMENT 'Template ativo',
    enviar_automatico BOOLEAN DEFAULT FALSE COMMENT 'Enviar automaticamente quando evento ocorrer',
    dias_antecedencia INT DEFAULT 0 COMMENT 'Dias de antecedência para envio (para alertas)',
    destinatarios_padrao TEXT COMMENT 'E-mails padrão separados por vírgula',
    categoria ENUM('alerta', 'notificacao', 'relatorio', 'cobranca', 'sistema') DEFAULT 'alerta',
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_codigo (codigo),
    INDEX idx_ativo (ativo),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Histórico de E-mails Enviados
CREATE TABLE IF NOT EXISTS email_historico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT COMMENT 'ID do template usado',
    destinatario VARCHAR(255) NOT NULL COMMENT 'E-mail do destinatário',
    destinatario_nome VARCHAR(255) COMMENT 'Nome do destinatário',
    assunto VARCHAR(500) NOT NULL COMMENT 'Assunto do e-mail enviado',
    corpo_html TEXT COMMENT 'Corpo HTML enviado',
    status ENUM('enviado', 'erro', 'pendente') DEFAULT 'pendente',
    mensagem_erro TEXT COMMENT 'Mensagem de erro se houver',
    referencia_tipo VARCHAR(50) COMMENT 'Tipo da entidade relacionada',
    referencia_id INT COMMENT 'ID da entidade relacionada',
    ip_origem VARCHAR(45),
    data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES email_templates(id) ON DELETE SET NULL,
    INDEX idx_destinatario (destinatario),
    INDEX idx_status (status),
    INDEX idx_data_envio (data_envio),
    INDEX idx_referencia (referencia_tipo, referencia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Alertas Programados
CREATE TABLE IF NOT EXISTS alertas_programados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL COMMENT 'ID do template a ser usado',
    tipo_alerta VARCHAR(100) NOT NULL COMMENT 'Tipo do alerta (conta_pagar, conta_receber, etc)',
    referencia_tipo VARCHAR(50) COMMENT 'Tipo da entidade',
    referencia_id INT COMMENT 'ID da entidade',
    destinatario_email VARCHAR(255) NOT NULL COMMENT 'E-mail do destinatário',
    data_programada DATE NOT NULL COMMENT 'Data para envio do alerta',
    hora_programada TIME DEFAULT '09:00:00' COMMENT 'Hora para envio',
    status ENUM('pendente', 'enviado', 'cancelado', 'erro') DEFAULT 'pendente',
    tentativas INT DEFAULT 0 COMMENT 'Número de tentativas de envio',
    data_envio TIMESTAMP NULL COMMENT 'Data/hora do envio efetivo',
    mensagem_erro TEXT,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES email_templates(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_data_programada (data_programada),
    INDEX idx_tipo_alerta (tipo_alerta),
    INDEX idx_referencia (referencia_tipo, referencia_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configuração de e-mail padrão (usuário deve editar)
INSERT INTO email_config (nome_config, smtp_host, smtp_port, smtp_secure, smtp_user, smtp_password, from_email, from_name, ativo)
VALUES ('Configuração Padrão', 'smtp.gmail.com', 587, 'tls', 'seu-email@gmail.com', 'sua-senha-app', 'seu-email@gmail.com', 'ERP INLAUDO', FALSE);

-- Inserir templates padrão
INSERT INTO email_templates (codigo, nome, descricao, assunto, corpo_html, corpo_texto, variaveis_disponiveis, ativo, enviar_automatico, dias_antecedencia, categoria) VALUES
(
    'conta_pagar_vencendo',
    'Alerta - Conta a Pagar Vencendo',
    'Alerta enviado quando uma conta a pagar está próxima do vencimento',
    'Alerta: Conta a Pagar Vencendo - {{descricao}}',
    '<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; }
        .footer { background: #1e40af; color: white; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
        .alert-box { background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 15px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .info-table td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .info-table td:first-child { font-weight: bold; width: 40%; }
        .btn { display: inline-block; padding: 12px 24px; background: #ef4444; color: white; text-decoration: none; border-radius: 4px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">⚠️ Alerta de Vencimento</h2>
            <p style="margin: 5px 0 0 0;">Conta a Pagar Próxima do Vencimento</p>
        </div>
        <div class="content">
            <div class="alert-box">
                <strong>Atenção!</strong> Uma conta a pagar está próxima do vencimento e requer sua atenção.
            </div>
            
            <table class="info-table">
                <tr>
                    <td>Descrição:</td>
                    <td>{{descricao}}</td>
                </tr>
                <tr>
                    <td>Fornecedor:</td>
                    <td>{{fornecedor}}</td>
                </tr>
                <tr>
                    <td>Valor:</td>
                    <td><strong style="color: #ef4444; font-size: 18px;">{{valor}}</strong></td>
                </tr>
                <tr>
                    <td>Data de Vencimento:</td>
                    <td><strong>{{data_vencimento}}</strong></td>
                </tr>
                <tr>
                    <td>Dias Restantes:</td>
                    <td>{{dias_restantes}} dias</td>
                </tr>
                <tr>
                    <td>Plano de Contas:</td>
                    <td>{{plano_contas}}</td>
                </tr>
            </table>
            
            <p><strong>Recomendação:</strong> Providencie o pagamento com antecedência para evitar multas e juros.</p>
            
            <a href="{{link_sistema}}" class="btn">Acessar Sistema</a>
        </div>
        <div class="footer">
            <p style="margin: 0;"><strong>ERP INLAUDO</strong> - Conectando Saúde e Tecnologia</p>
            <p style="margin: 5px 0 0 0;">Este é um e-mail automático, não responda.</p>
        </div>
    </div>
</body>
</html>',
    'ALERTA: Conta a Pagar Vencendo

Descrição: {{descricao}}
Fornecedor: {{fornecedor}}
Valor: {{valor}}
Data de Vencimento: {{data_vencimento}}
Dias Restantes: {{dias_restantes}} dias

Acesse o sistema: {{link_sistema}}

---
ERP INLAUDO - Conectando Saúde e Tecnologia',
    '{"descricao": "Descrição da conta", "fornecedor": "Nome do fornecedor", "valor": "Valor formatado", "data_vencimento": "Data de vencimento", "dias_restantes": "Dias até vencer", "plano_contas": "Categoria do plano de contas", "link_sistema": "Link para o sistema"}',
    TRUE,
    FALSE,
    3,
    'alerta'
),
(
    'conta_receber_vencida',
    'Alerta - Conta a Receber Vencida',
    'Alerta enviado quando uma conta a receber está vencida',
    'Alerta: Conta a Receber Vencida - {{cliente}}',
    '<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; }
        .footer { background: #1e40af; color: white; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
        .alert-box { background: #fffbeb; border-left: 4px solid #f59e0b; padding: 15px; margin: 15px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .info-table td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .info-table td:first-child { font-weight: bold; width: 40%; }
        .btn { display: inline-block; padding: 12px 24px; background: #f59e0b; color: white; text-decoration: none; border-radius: 4px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">💰 Alerta de Inadimplência</h2>
            <p style="margin: 5px 0 0 0;">Conta a Receber Vencida</p>
        </div>
        <div class="content">
            <div class="alert-box">
                <strong>Atenção!</strong> Uma conta a receber está vencida e requer ação de cobrança.
            </div>
            
            <table class="info-table">
                <tr>
                    <td>Cliente:</td>
                    <td><strong>{{cliente}}</strong></td>
                </tr>
                <tr>
                    <td>Descrição:</td>
                    <td>{{descricao}}</td>
                </tr>
                <tr>
                    <td>Valor:</td>
                    <td><strong style="color: #f59e0b; font-size: 18px;">{{valor}}</strong></td>
                </tr>
                <tr>
                    <td>Data de Vencimento:</td>
                    <td>{{data_vencimento}}</td>
                </tr>
                <tr>
                    <td>Dias em Atraso:</td>
                    <td><strong style="color: #dc2626;">{{dias_atraso}} dias</strong></td>
                </tr>
                <tr>
                    <td>Contato do Cliente:</td>
                    <td>{{contato_cliente}}</td>
                </tr>
            </table>
            
            <p><strong>Ação Recomendada:</strong> Entre em contato com o cliente para regularizar o pagamento.</p>
            
            <a href="{{link_sistema}}" class="btn">Acessar Sistema</a>
        </div>
        <div class="footer">
            <p style="margin: 0;"><strong>ERP INLAUDO</strong> - Conectando Saúde e Tecnologia</p>
            <p style="margin: 5px 0 0 0;">Este é um e-mail automático, não responda.</p>
        </div>
    </div>
</body>
</html>',
    'ALERTA: Conta a Receber Vencida

Cliente: {{cliente}}
Descrição: {{descricao}}
Valor: {{valor}}
Data de Vencimento: {{data_vencimento}}
Dias em Atraso: {{dias_atraso}} dias

Acesse o sistema: {{link_sistema}}',
    '{"cliente": "Nome do cliente", "descricao": "Descrição da conta", "valor": "Valor formatado", "data_vencimento": "Data de vencimento", "dias_atraso": "Dias em atraso", "contato_cliente": "E-mail ou telefone", "link_sistema": "Link para o sistema"}',
    TRUE,
    FALSE,
    0,
    'alerta'
),
(
    'interacao_proxima',
    'Lembrete - Próxima Interação com Cliente',
    'Lembrete de próxima interação agendada com cliente',
    'Lembrete: Interação Agendada com {{cliente}}',
    '<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .content { background: #f9fafb; padding: 20px; border: 1px solid #e5e7eb; }
        .footer { background: #1e40af; color: white; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
        .info-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin: 15px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .info-table td { padding: 8px; border-bottom: 1px solid #e5e7eb; }
        .info-table td:first-child { font-weight: bold; width: 40%; }
        .btn { display: inline-block; padding: 12px 24px; background: #3b82f6; color: white; text-decoration: none; border-radius: 4px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2 style="margin: 0;">📅 Lembrete de Interação</h2>
            <p style="margin: 5px 0 0 0;">Você tem uma interação agendada</p>
        </div>
        <div class="content">
            <div class="info-box">
                <strong>Lembrete!</strong> Você tem uma interação agendada com cliente.
            </div>
            
            <table class="info-table">
                <tr>
                    <td>Cliente:</td>
                    <td><strong>{{cliente}}</strong></td>
                </tr>
                <tr>
                    <td>Data/Hora:</td>
                    <td><strong>{{data_hora}}</strong></td>
                </tr>
                <tr>
                    <td>Forma de Contato:</td>
                    <td>{{forma_contato}}</td>
                </tr>
                <tr>
                    <td>Contato do Cliente:</td>
                    <td>{{contato_cliente}}</td>
                </tr>
                <tr>
                    <td>Histórico Anterior:</td>
                    <td>{{historico}}</td>
                </tr>
            </table>
            
            <p><strong>Prepare-se:</strong> Revise o histórico de interações antes do contato.</p>
            
            <a href="{{link_sistema}}" class="btn">Ver Detalhes no Sistema</a>
        </div>
        <div class="footer">
            <p style="margin: 0;"><strong>ERP INLAUDO</strong> - Conectando Saúde e Tecnologia</p>
            <p style="margin: 5px 0 0 0;">Este é um e-mail automático, não responda.</p>
        </div>
    </div>
</body>
</html>',
    'LEMBRETE: Interação Agendada

Cliente: {{cliente}}
Data/Hora: {{data_hora}}
Forma de Contato: {{forma_contato}}
Contato: {{contato_cliente}}

Acesse o sistema: {{link_sistema}}',
    '{"cliente": "Nome do cliente", "data_hora": "Data e hora da interação", "forma_contato": "Telefone, e-mail, presencial ou WhatsApp", "contato_cliente": "Telefone ou e-mail", "historico": "Resumo do último contato", "link_sistema": "Link para o sistema"}',
    TRUE,
    FALSE,
    1,
    'alerta'
);
