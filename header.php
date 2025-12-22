<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>ERP INLAUDO</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="header-logo">
            <img src="LOGOBRANCA.png" alt="INLAUDO">
            <h1>ERP INLAUDO</h1>
        </div>
    </header>
    
    <nav class="navbar">
        <ul>
            <li>
                <a href="index.php">Dashboard</a>
            </li>
            <li>
                <a href="#">CRM</a>
                <ul class="dropdown">
                    <li><a href="clientes.php">Clientes</a></li>
                    <li><a href="interacoes.php">Interações</a></li>
                </ul>
            </li>
            <li>
                <a href="#">Financeiro</a>
                <ul class="dropdown">
                    <li><a href="contas_receber.php">Contas a Receber</a></li>
                    <li><a href="contas_pagar.php">Contas a Pagar</a></li>
                </ul>
            </li>
            <li>
                <a href="#">Produtos</a>
                <ul class="dropdown">
                    <li><a href="contratos.php">Contratos</a></li>
                </ul>
            </li>
            <li>
                <a href="#">Faturamento</a>
                <ul class="dropdown">
                    <li><a href="faturamento.php">Faturas Stripe</a></li>
                </ul>
            </li>
            <li>
                <a href="#">Integrações</a>
                <ul class="dropdown">
                    <li><a href="integracoes_boleto.php">Boleto (CORA/Stripe)</a></li>
                    <li><a href="boletos.php">Boletos Gerados</a></li>
                    <li><a href="email_config.php">E-mail Config</a></li>
                    <li><a href="email_templates.php">Templates de E-mail</a></li>
                    <li><a href="email_historico.php">Histórico de E-mails</a></li>
                    <li><a href="alertas_programados.php">Alertas Programados</a></li>
                    <li><a href="logs_integracao.php">Logs de Integração</a></li>
                </ul>
            </li>
        </ul>
    </nav>
