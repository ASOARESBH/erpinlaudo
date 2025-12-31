<?php
require_once 'config.php';

$pageTitle = 'Dashboard';

// Buscar estatísticas
$conn = getConnection();

// Total de clientes
$stmt = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE tipo_cliente = 'CLIENTE'");
$totalClientes = $stmt->fetch()['total'];

// Total de leads
$stmt = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE tipo_cliente = 'LEAD'");
$totalLeads = $stmt->fetch()['total'];

// Contas a receber pendentes
$stmt = $conn->query("SELECT COUNT(*) as total, SUM(valor) as valor_total FROM contas_receber WHERE status = 'pendente'");
$contasReceber = $stmt->fetch();

// Contas a pagar pendentes
$stmt = $conn->query("SELECT COUNT(*) as total, SUM(valor) as valor_total FROM contas_pagar WHERE status = 'pendente'");
$contasPagar = $stmt->fetch();

// Próximas interações (próximos 7 dias)
$dataLimite = date('Y-m-d H:i:s', strtotime('+7 days'));
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM interacoes WHERE proximo_contato_data <= ? AND proximo_contato_data >= NOW()");
$stmt->execute([$dataLimite]);
$proximasInteracoes = $stmt->fetch()['total'];

// Contas vencidas
$stmt = $conn->query("SELECT COUNT(*) as total FROM contas_receber WHERE status = 'vencido'");
$contasVencidas = $stmt->fetch()['total'];

include 'header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Dashboard - Visão Geral</h2>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
            <!-- Card Clientes -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo $totalClientes; ?></h3>
                <p style="opacity: 0.9;">Total de Clientes</p>
            </div>
            
            <!-- Card Leads -->
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo $totalLeads; ?></h3>
                <p style="opacity: 0.9;">Total de Leads</p>
            </div>
            
            <!-- Card Contas a Receber -->
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo $contasReceber['total'] ?: 0; ?></h3>
                <p style="opacity: 0.9;">Contas a Receber Pendentes</p>
                <p style="font-size: 1.2rem; margin-top: 0.5rem;"><?php echo formatMoeda($contasReceber['valor_total'] ?: 0); ?></p>
            </div>
            
            <!-- Card Contas a Pagar -->
            <div style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo $contasPagar['total'] ?: 0; ?></h3>
                <p style="opacity: 0.9;">Contas a Pagar Pendentes</p>
                <p style="font-size: 1.2rem; margin-top: 0.5rem;"><?php echo formatMoeda($contasPagar['valor_total'] ?: 0); ?></p>
            </div>
            
            <!-- Card Próximas Interações -->
            <div style="background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo $proximasInteracoes; ?></h3>
                <p style="opacity: 0.9;">Interações nos Próximos 7 Dias</p>
            </div>
            
            <!-- Card Contas Vencidas -->
            <div style="background: linear-gradient(135deg, #ff0844 0%, #ffb199 100%); color: white; padding: 1.5rem; border-radius: 8px;">
                <h3 style="font-size: 2rem; margin-bottom: 0.5rem;"><?php echo $contasVencidas; ?></h3>
                <p style="opacity: 0.9;">Contas Vencidas</p>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>Acesso Rápido</h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <a href="clientes.php" class="btn btn-primary" style="text-align: center;">Gerenciar Clientes</a>
            <a href="interacoes.php" class="btn btn-primary" style="text-align: center;">Gerenciar Interações</a>
            <a href="contas_receber.php" class="btn btn-success" style="text-align: center;">Contas a Receber</a>
            <a href="contas_pagar.php" class="btn btn-danger" style="text-align: center;">Contas a Pagar</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
