<?php
/**
 * Portal do Cliente - Realizar Pagamento
 * ERP INLAUDO - Versão 7.3
 */

require_once 'verifica_sessao_cliente.php';
require_once 'config.php';

$conn = getConnection();

$conta_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$conta_id) {
    header('Location: cliente_contas_pagar.php');
    exit;
}

// Buscar conta e verificar se pertence ao cliente
$stmt = $conn->prepare("
    SELECT cr.*
    FROM contas_receber cr
    WHERE cr.id = ? AND cr.cliente_id = ?
");
$stmt->execute([$conta_id, $cliente_id]);
$conta = $stmt->fetch();

if (!$conta) {
    header('Location: cliente_contas_pagar.php');
    exit;
}

// Verificar se conta já foi paga
if ($conta['status'] == 'pago') {
    header('Location: cliente_contas_pagar.php?erro=conta_paga');
    exit;
}

// Processar seleção de gateway
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gateway'])) {
    $gateway = $_POST['gateway'];
    
    // Redirecionar para gerar link de pagamento
    header("Location: gerar_link_pagamento.php?conta_id={$conta_id}&gateway={$gateway}&origem=cliente");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Realizar Pagamento - Portal do Cliente</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            min-height: 100vh;
        }
        
        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header-content {
            max-width: 1000px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .btn-back {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: rgba(255,255,255,0.3);
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .payment-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .payment-header {
            background: #f8fafc;
            padding: 2rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .payment-header h1 {
            color: #1e293b;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .payment-header p {
            color: #64748b;
        }
        
        .payment-body {
            padding: 2rem;
        }
        
        .info-section {
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-section h3 {
            color: #065f46;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            color: #047857;
            font-size: 0.85rem;
            margin-bottom: 0.25rem;
            font-weight: 600;
        }
        
        .info-value {
            color: #065f46;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .info-value.large {
            font-size: 2rem;
            color: #10b981;
        }
        
        .gateway-section h3 {
            color: #1e293b;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }
        
        .gateway-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .gateway-option {
            border: 3px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }
        
        .gateway-option:hover {
            border-color: #10b981;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        
        .gateway-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        
        .gateway-option input[type="radio"]:checked + .gateway-content {
            border-left: 4px solid #10b981;
        }
        
        .gateway-option input[type="radio"]:checked ~ .gateway-check {
            display: block;
        }
        
        .gateway-content {
            padding-left: 1rem;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }
        
        .gateway-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .gateway-name {
            color: #1e293b;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .gateway-description {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 0.75rem;
        }
        
        .gateway-methods {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .method-badge {
            background: #f0fdf4;
            color: #065f46;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .gateway-check {
            display: none;
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #10b981;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .btn {
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
        }
        
        .btn-primary {
            background: #10b981;
            color: white;
            width: 100%;
            justify-content: center;
        }
        
        .btn-primary:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
        }
        
        .btn-primary:disabled {
            background: #cbd5e1;
            cursor: not-allowed;
            transform: none;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .gateway-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <a href="cliente_contas_pagar.php" class="btn-back">← Voltar</a>
                <div>
                    <h2>Realizar Pagamento</h2>
                </div>
            </div>
            <div>
                <span><?php echo htmlspecialchars($cliente_nome); ?></span>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="payment-card">
            <div class="payment-header">
                <h1>💳 Realizar Pagamento</h1>
                <p>Selecione a forma de pagamento e prossiga com segurança</p>
            </div>
            
            <div class="payment-body">
                <!-- Informações da Conta -->
                <div class="info-section">
                    <h3>📋 Informações da Conta</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Descrição</span>
                            <span class="info-value"><?php echo htmlspecialchars($conta['descricao']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Valor a Pagar</span>
                            <span class="info-value large"><?php echo formatMoeda($conta['valor']); ?></span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Vencimento</span>
                            <span class="info-value">
                                <?php 
                                echo date('d/m/Y', strtotime($conta['data_vencimento']));
                                if (strtotime($conta['data_vencimento']) < time()) {
                                    echo ' <span style="color: #ef4444;">⚠️ Vencida</span>';
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div class="info-item">
                            <span class="info-label">Parcela</span>
                            <span class="info-value"><?php echo $conta['parcela_atual']; ?>/<?php echo $conta['recorrencia']; ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Seleção de Gateway -->
                <form method="POST" id="paymentForm">
                    <div class="gateway-section">
                        <h3>💰 Selecione a Forma de Pagamento</h3>
                        
                        <div class="gateway-grid">
                            <!-- Mercado Pago -->
                            <label class="gateway-option">
                                <input type="radio" name="gateway" value="mercadopago" required>
                                <div class="gateway-content">
                                    <div class="gateway-icon">💳</div>
                                    <div class="gateway-name">Mercado Pago</div>
                                    <div class="gateway-description">
                                        Pague com segurança através do Mercado Pago. Aceita múltiplas formas de pagamento.
                                    </div>
                                    <div class="gateway-methods">
                                        <span class="method-badge">PIX</span>
                                        <span class="method-badge">Boleto</span>
                                        <span class="method-badge">Cartão</span>
                                    </div>
                                </div>
                                <div class="gateway-check">✓</div>
                            </label>
                            
                            <!-- CORA -->
                            <label class="gateway-option">
                                <input type="radio" name="gateway" value="cora" required>
                                <div class="gateway-content">
                                    <div class="gateway-icon">🏦</div>
                                    <div class="gateway-name">CORA Banking</div>
                                    <div class="gateway-description">
                                        Pagamento via boleto bancário registrado pelo CORA Banking.
                                    </div>
                                    <div class="gateway-methods">
                                        <span class="method-badge">Boleto</span>
                                    </div>
                                </div>
                                <div class="gateway-check">✓</div>
                            </label>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        🔒 Prosseguir para Pagamento
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Informações de Segurança -->
        <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h3 style="color: #1e293b; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                🔒 Pagamento Seguro
            </h3>
            <p style="color: #64748b; line-height: 1.6; margin-bottom: 0.75rem;">
                Seus dados estão protegidos. Todas as transações são processadas através de gateways de pagamento certificados e seguros.
            </p>
            <p style="color: #64748b; line-height: 1.6;">
                Após a confirmação do pagamento, o status da sua conta será atualizado automaticamente.
            </p>
        </div>
    </div>
    
    <script>
        // Highlight da opção selecionada
        document.querySelectorAll('.gateway-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.gateway-option').forEach(opt => {
                    opt.style.borderColor = '#e2e8f0';
                });
                this.style.borderColor = '#10b981';
            });
        });
    </script>
</body>
</html>
