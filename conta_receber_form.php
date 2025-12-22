<?php
require_once 'config.php';

$pageTitle = 'Cadastro de Conta a Receber';
$conn = getConnection();

// Verificar se é edição
$contaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$conta = null;

if ($contaId > 0) {
    $stmt = $conn->prepare("SELECT * FROM contas_receber WHERE id = ?");
    $stmt->execute([$contaId]);
    $conta = $stmt->fetch();
    
    if (!$conta) {
        header('Location: contas_receber.php');
        exit;
    }
}

// Buscar clientes
$stmtClientes = $conn->query("SELECT id, nome, razao_social, nome_fantasia, tipo_pessoa FROM clientes WHERE tipo_cliente = 'CLIENTE' ORDER BY razao_social, nome");
$clientes = $stmtClientes->fetchAll();

// Buscar planos de contas de receita
$stmtPlanos = $conn->query("SELECT id, nome FROM plano_contas WHERE tipo = 'RECEITA' AND ativo = 1 ORDER BY nome");
$planosContas = $stmtPlanos->fetchAll();

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $clienteId = (int)$_POST['cliente_id'];
    $planoContasId = (int)$_POST['plano_contas_id'];
    $descricao = sanitize($_POST['descricao']);
    $valor = (float)str_replace(',', '.', str_replace('.', '', $_POST['valor']));
    $dataVencimento = $_POST['data_vencimento'];
    $formaPagamento = sanitize($_POST['forma_pagamento']);
    $recorrencia = (int)$_POST['recorrencia'];
    $observacoes = sanitize($_POST['observacoes'] ?? '');
    
    // Verificar se deve gerar boleto
    $gerarBoleto = isset($_POST['gerar_boleto']) && $_POST['gerar_boleto'] == '1' && $formaPagamento == 'boleto';
    $plataformaBoleto = isset($_POST['plataforma_boleto']) ? sanitize($_POST['plataforma_boleto']) : null;
    
    // Verificar se deve gerar fatura Stripe
    $gerarFatura = isset($_POST['gerar_fatura_stripe']) && $_POST['gerar_fatura_stripe'] == '1';
    $formaPagamentoFatura = isset($_POST['forma_pagamento_fatura']) ? sanitize($_POST['forma_pagamento_fatura']) : 'boleto';
    
    try {
        if ($contaId > 0) {
            // Atualizar
            $sql = "UPDATE contas_receber SET 
                    cliente_id = ?, plano_contas_id = ?, descricao = ?, valor = ?,
                    data_vencimento = ?, forma_pagamento = ?, recorrencia = ?, observacoes = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $clienteId, $planoContasId, $descricao, $valor,
                $dataVencimento, $formaPagamento, $recorrencia, $observacoes, $contaId
            ]);
            $mensagem = "Conta a receber atualizada com sucesso!";
        } else {
            // Inserir com recorrência
            for ($i = 0; $i < $recorrencia; $i++) {
                $dataVencimentoParcela = date('Y-m-d', strtotime($dataVencimento . " +$i month"));
                
                $sql = "INSERT INTO contas_receber (
                        cliente_id, plano_contas_id, descricao, valor, data_vencimento,
                        forma_pagamento, recorrencia, parcela_atual, observacoes
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([
                    $clienteId, $planoContasId, $descricao, $valor, $dataVencimentoParcela,
                    $formaPagamento, $recorrencia, ($i + 1), $observacoes
                ]);
                
                // Gerar boleto se solicitado
                if ($gerarBoleto && $plataformaBoleto) {
                    $contaReceberIdGerada = $conn->lastInsertId();
                    
                    // Buscar dados do cliente
                    $stmtCliente = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
                    $stmtCliente->execute([$clienteId]);
                    $clienteDados = $stmtCliente->fetch();
                    
                    try {
                        if ($plataformaBoleto == 'stripe') {
                            require_once 'lib_boleto_stripe.php';
                            $boletoLib = new BoletoStripe();
                        } else {
                            // Usar nova biblioteca CORA v2 com mTLS
                            require_once 'lib_boleto_cora_v2.php';
                            
                            // Buscar configurações CORA
                            $stmtConfigCora = $conn->query("SELECT * FROM integracoes WHERE tipo = 'cora' AND ativo = 1");
                            $configCora = $stmtConfigCora->fetch();
                            
                            if (!$configCora) {
                                throw new Exception('Integração CORA não está configurada ou ativa');
                            }
                            
                            $coraConfig = json_decode($configCora['config'], true);
                            $clientId = $coraConfig['client_id'] ?? '';
                            $ambiente = $coraConfig['ambiente'] ?? 'production';
                            $certificado = $configCora['api_key'];
                            $privateKey = $configCora['api_secret'];
                            
                            if (!$clientId || !file_exists($certificado) || !file_exists($privateKey)) {
                                throw new Exception('Credenciais CORA incompletas. Configure em Integrações.');
                            }
                            
                            $boletoLib = new CoraAPIv2($clientId, $certificado, $privateKey, $ambiente);
                        }
                        
                        // Preparar dados conforme plataforma
                        if ($plataformaBoleto == 'cora') {
                            // Formato para CORA v2
                            $dadosCliente = [
                                'nome' => $clienteDados['tipo_pessoa'] == 'CNPJ' ? $clienteDados['razao_social'] : $clienteDados['nome'],
                                'email' => $clienteDados['email'] ?: 'naotem@email.com',
                                'documento' => $clienteDados['cnpj_cpf'],
                                'endereco' => [
                                    'logradouro' => $clienteDados['logradouro'] ?: '',
                                    'numero' => $clienteDados['numero'] ?: 'S/N',
                                    'bairro' => $clienteDados['bairro'] ?: '',
                                    'cidade' => $clienteDados['cidade'] ?: '',
                                    'uf' => $clienteDados['estado'] ?: '',
                                    'cep' => $clienteDados['cep'] ?: '',
                                    'complemento' => $clienteDados['complemento'] ?: ''
                                ]
                            ];
                            
                            $dadosCobranca = [
                                'codigo_unico' => 'CR-' . $contaReceberIdGerada . '-' . time(),
                                'descricao' => $descricao,
                                'valor' => $valor,
                                'data_vencimento' => $dataVencimentoParcela,
                                'multa' => [
                                    'percentual' => 2.0 // 2% de multa após vencimento
                                ],
                                'juros' => [
                                    'percentual_mes' => 1.0 // 1% ao mês de juros
                                ]
                            ];
                        } else {
                            // Formato para Stripe (mantém compatibilidade)
                            $dadosBoleto = [
                                'valor' => $valor,
                                'data_vencimento' => $dataVencimentoParcela,
                                'cliente_nome' => $clienteDados['tipo_pessoa'] == 'CNPJ' ? $clienteDados['razao_social'] : $clienteDados['nome'],
                                'cliente_email' => $clienteDados['email'],
                                'cliente_documento' => $clienteDados['cnpj_cpf'],
                                'cliente_telefone' => $clienteDados['celular'] ?: $clienteDados['telefone'],
                                'cliente_logradouro' => $clienteDados['logradouro'],
                                'cliente_numero' => $clienteDados['numero'],
                                'cliente_complemento' => $clienteDados['complemento'],
                                'cliente_bairro' => $clienteDados['bairro'],
                                'cliente_cidade' => $clienteDados['cidade'],
                                'cliente_estado' => $clienteDados['estado'],
                                'cliente_cep' => $clienteDados['cep'],
                                'descricao' => $descricao,
                                'conta_receber_id' => $contaReceberIdGerada
                            ];
                        }
                        
                        // Gerar boleto conforme plataforma
                        if ($plataformaBoleto == 'cora') {
                            $resultadoBoleto = $boletoLib->emitirBoleto($dadosCliente, $dadosCobranca);
                        } else {
                            $resultadoBoleto = $boletoLib->gerarBoleto($dadosBoleto);
                        }
                        
                        if ($resultadoBoleto['sucesso']) {
                            // Salvar boleto no banco
                            // Salvar boleto conforme formato da plataforma
                            if ($plataformaBoleto == 'cora') {
                                $sqlBoleto = "INSERT INTO boletos (conta_receber_id, plataforma, id_externo, codigo_unico, codigo_barras, linha_digitavel, url_boleto, url_pdf, qr_code_pix, pix_copia_cola, status, data_vencimento, valor, resposta_api) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                $stmtBoleto = $conn->prepare($sqlBoleto);
                                $stmtBoleto->execute([
                                    $contaReceberIdGerada,
                                    'cora',
                                    $resultadoBoleto['dados']['id_cora'] ?? null,
                                    $resultadoBoleto['dados']['codigo_unico'] ?? null,
                                    $resultadoBoleto['dados']['codigo_barras'] ?? null,
                                    $resultadoBoleto['dados']['linha_digitavel'] ?? null,
                                    $resultadoBoleto['dados']['url_boleto'] ?? null,
                                    $resultadoBoleto['dados']['url_pdf'] ?? null,
                                    $resultadoBoleto['dados']['qr_code_pix'] ?? null,
                                    $resultadoBoleto['dados']['pix_copia_cola'] ?? null,
                                    $resultadoBoleto['dados']['status'] ?? 'pendente',
                                    $dataVencimentoParcela,
                                    $valor,
                                    $resultadoBoleto['dados']['resposta_completa'] ?? null
                                ]);
                            } else {
                                $sqlBoleto = "INSERT INTO boletos (conta_receber_id, plataforma, id_externo, codigo_barras, linha_digitavel, url_boleto, url_pdf, status, data_vencimento, valor, resposta_api) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                $stmtBoleto = $conn->prepare($sqlBoleto);
                                $stmtBoleto->execute([
                                    $contaReceberIdGerada,
                                    $plataformaBoleto,
                                    $resultadoBoleto['boleto_id'],
                                    $resultadoBoleto['codigo_barras'],
                                    $resultadoBoleto['linha_digitavel'],
                                    $resultadoBoleto['url_boleto'],
                                    $resultadoBoleto['url_pdf'] ?? null,
                                    'pendente',
                                    $dataVencimentoParcela,
                                    $valor,
                                    $resultadoBoleto['resposta_completa']
                                ]);
                            }
                            
                            $boletoIdGerado = $conn->lastInsertId();
                            
                            // Atualizar conta a receber com ID do boleto
                            $conn->prepare("UPDATE contas_receber SET boleto_id = ? WHERE id = ?")->execute([$boletoIdGerado, $contaReceberIdGerada]);
                        }
                    } catch (Exception $e) {
                        // Log do erro mas não interrompe o processo
                        error_log("Erro ao gerar boleto: " . $e->getMessage());
                    }
                }
                
                // Gerar fatura Stripe se solicitado
                if ($gerarFatura) {
                    $contaReceberIdGerada = $conn->lastInsertId();
                    
                    // Buscar dados do cliente
                    $stmtCliente = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
                    $stmtCliente->execute([$clienteId]);
                    $clienteDados = $stmtCliente->fetch();
                    
                    try {
                        require_once 'lib_stripe_faturamento.php';
                        $stripeLib = new StripeFaturamento();
                        
                        // Criar ou obter customer
                        $customerId = $stripeLib->criarOuObterCustomer($clienteDados);
                        
                        // Criar fatura
                        $dadosFatura = [
                            'customer_id' => $customerId,
                            'cliente_id' => $clienteId,
                            'conta_receber_id' => $contaReceberIdGerada,
                            'descricao' => $descricao,
                            'valor' => $valor,
                            'data_vencimento' => $dataVencimentoParcela,
                            'forma_pagamento' => $formaPagamentoFatura
                        ];
                        
                        $resultadoFatura = $stripeLib->criarFatura($dadosFatura);
                        
                        if ($resultadoFatura['sucesso']) {
                            // Salvar fatura no banco
                            $sqlFatura = "INSERT INTO faturamento (conta_receber_id, cliente_id, stripe_invoice_id, stripe_customer_id, numero_fatura, descricao, valor_total, status, data_emissao, data_vencimento, url_fatura, hosted_invoice_url, payment_intent_id, boleto_url, forma_pagamento, resposta_api) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";
                            $stmtFatura = $conn->prepare($sqlFatura);
                            $stmtFatura->execute([
                                $contaReceberIdGerada,
                                $clienteId,
                                $resultadoFatura['invoice_id'],
                                $customerId,
                                $resultadoFatura['numero_fatura'],
                                $descricao,
                                $valor,
                                $resultadoFatura['status'],
                                $dataVencimentoParcela,
                                $resultadoFatura['url_fatura'],
                                $resultadoFatura['hosted_invoice_url'],
                                $resultadoFatura['payment_intent_id'],
                                $resultadoFatura['boleto_url'],
                                $formaPagamentoFatura,
                                $resultadoFatura['resposta_completa']
                            ]);
                            
                            $faturaIdGerada = $conn->lastInsertId();
                            
                            // Atualizar conta a receber com ID da fatura
                            $conn->prepare("UPDATE contas_receber SET fatura_id = ? WHERE id = ?")->execute([$faturaIdGerada, $contaReceberIdGerada]);
                        }
                    } catch (Exception $e) {
                        // Log do erro mas não interrompe o processo
                        error_log("Erro ao gerar fatura Stripe: " . $e->getMessage());
                    }
                }
            }
            $mensagem = "Conta(s) a receber cadastrada(s) com sucesso!";
        }
        
        header('Location: contas_receber.php?msg=' . urlencode($mensagem));
        exit;
        
    } catch (PDOException $e) {
        $erro = "Erro ao salvar conta a receber: " . $e->getMessage();
    }
}

include 'header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2><?php echo $contaId > 0 ? 'Editar Conta a Receber' : 'Nova Conta a Receber'; ?></h2>
        </div>
        
        <?php if (isset($erro)): ?>
            <div class="alert alert-error"><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Cliente *</label>
                    <select name="cliente_id" required>
                        <option value="">Selecione o cliente...</option>
                        <?php foreach ($clientes as $c): ?>
                            <option value="<?php echo $c['id']; ?>" 
                                    <?php echo ($conta && $conta['cliente_id'] == $c['id']) ? 'selected' : ''; ?>>
                                <?php 
                                echo $c['tipo_pessoa'] == 'CNPJ' 
                                    ? ($c['razao_social'] ?: $c['nome_fantasia']) 
                                    : $c['nome']; 
                                ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Plano de Contas *</label>
                    <select name="plano_contas_id" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($planosContas as $plano): ?>
                            <option value="<?php echo $plano['id']; ?>" 
                                    <?php echo ($conta && $conta['plano_contas_id'] == $plano['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($plano['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Descrição *</label>
                <input type="text" name="descricao" required 
                       value="<?php echo $conta ? htmlspecialchars($conta['descricao']) : ''; ?>">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Valor (R$) *</label>
                    <input type="text" name="valor" id="valor" required 
                           value="<?php echo $conta ? number_format($conta['valor'], 2, ',', '.') : ''; ?>"
                           onkeyup="this.value = formatMoeda(this.value)">
                </div>
                
                <div class="form-group">
                    <label>Data de Vencimento *</label>
                    <input type="date" name="data_vencimento" required 
                           value="<?php echo $conta ? $conta['data_vencimento'] : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Forma de Pagamento *</label>
                    <select name="forma_pagamento" required>
                        <option value="">Selecione...</option>
                        <option value="boleto" <?php echo ($conta && $conta['forma_pagamento'] == 'boleto') ? 'selected' : ''; ?>>Boleto</option>
                        <option value="cartao_credito" <?php echo ($conta && $conta['forma_pagamento'] == 'cartao_credito') ? 'selected' : ''; ?>>Cartão de Crédito</option>
                        <option value="cartao_debito" <?php echo ($conta && $conta['forma_pagamento'] == 'cartao_debito') ? 'selected' : ''; ?>>Cartão de Débito</option>
                        <option value="pix" <?php echo ($conta && $conta['forma_pagamento'] == 'pix') ? 'selected' : ''; ?>>PIX</option>
                        <option value="dinheiro" <?php echo ($conta && $conta['forma_pagamento'] == 'dinheiro') ? 'selected' : ''; ?>>Dinheiro</option>
                        <option value="transferencia" <?php echo ($conta && $conta['forma_pagamento'] == 'transferencia') ? 'selected' : ''; ?>>Transferência</option>
                    </select>
                </div>
            </div>
            
            <?php if (!$conta): ?>
            <div class="form-group">
                <label>Recorrência (Número de Parcelas) *</label>
                <input type="number" name="recorrencia" min="1" max="120" value="1" required>
                <small style="color: #6b7280;">Informe quantas vezes esta conta se repetirá. Para pagamento único, deixe 1.</small>
            </div>
            
            <div id="opcoes_boleto" style="display: none; border: 2px solid #2563eb; padding: 1.5rem; border-radius: 8px; background: #eff6ff;">
                <h3 style="color: #1e40af; margin-bottom: 1rem;">Opções de Geração de Boleto</h3>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="gerar_boleto" id="gerar_boleto" value="1">
                        <span>Gerar boleto automaticamente via API</span>
                    </label>
                </div>
                
                <div class="form-group" id="plataforma_boleto_group" style="display: none;">
                    <label>Plataforma de Geração *</label>
                    <select name="plataforma_boleto" id="plataforma_boleto">
                        <option value="">Selecione...</option>
                        <option value="stripe">Stripe</option>
                        <option value="cora">CORA</option>
                    </select>
                    <small style="color: #6b7280;">Certifique-se de que a integração está ativa em Integrações > Boleto</small>
                </div>
            </div>
            
            <div style="border: 2px solid #10b981; padding: 1.5rem; border-radius: 8px; background: #f0fdf4; margin-top: 1rem;">
                <h3 style="color: #059669; margin-bottom: 1rem;">Faturamento Stripe (Recomendado)</h3>
                
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="gerar_fatura_stripe" id="gerar_fatura_stripe" value="1" checked>
                        <span>Gerar fatura automaticamente no Stripe</span>
                    </label>
                    <small style="color: #6b7280; display: block; margin-top: 0.5rem;">
                        O Stripe criará um customer (se não existir) e uma fatura completa com boleto ou cartão.
                    </small>
                </div>
                
                <div class="form-group" id="forma_pagamento_fatura_group">
                    <label>Forma de Pagamento da Fatura *</label>
                    <select name="forma_pagamento_fatura" id="forma_pagamento_fatura">
                        <option value="boleto">Boleto</option>
                        <option value="card">Cartão</option>
                    </select>
                    <small style="color: #6b7280;">Boleto: gera boleto bancário | Cartão: permite pagamento com cartão</small>
                </div>
            </div>
            <?php else: ?>
            <input type="hidden" name="recorrencia" value="<?php echo $conta['recorrencia']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label>Observações</label>
                <textarea name="observacoes" rows="4"><?php echo $conta ? htmlspecialchars($conta['observacoes']) : ''; ?></textarea>
            </div>
            
            <div class="btn-group">
                <button type="submit" class="btn btn-success">Salvar</button>
                <a href="contas_receber.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
    // Mostrar opções de boleto quando forma de pagamento for boleto
    document.addEventListener('DOMContentLoaded', function() {
        const formaPagamento = document.querySelector('select[name="forma_pagamento"]');
        const opcoesBoleto = document.getElementById('opcoes_boleto');
        const gerarBoleto = document.getElementById('gerar_boleto');
        const plataformaBoletoGroup = document.getElementById('plataforma_boleto_group');
        
        if (formaPagamento) {
            formaPagamento.addEventListener('change', function() {
                if (this.value === 'boleto') {
                    opcoesBoleto.style.display = 'block';
                } else {
                    opcoesBoleto.style.display = 'none';
                    gerarBoleto.checked = false;
                    plataformaBoletoGroup.style.display = 'none';
                }
            });
        }
        
        if (gerarBoleto) {
            gerarBoleto.addEventListener('change', function() {
                if (this.checked) {
                    plataformaBoletoGroup.style.display = 'block';
                    document.getElementById('plataforma_boleto').required = true;
                } else {
                    plataformaBoletoGroup.style.display = 'none';
                    document.getElementById('plataforma_boleto').required = false;
                }
            });
        }
    });
</script>

<?php include 'footer.php'; ?>
