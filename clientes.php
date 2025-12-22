<?php
require_once 'config.php';

$pageTitle = 'Clientes';

// Processar busca
$busca = isset($_GET['busca']) ? sanitize($_GET['busca']) : '';
$filtroTipo = isset($_GET['tipo']) ? sanitize($_GET['tipo']) : '';

// Buscar clientes
$conn = getConnection();
$sql = "SELECT * FROM clientes WHERE 1=1";
$params = [];

if (!empty($busca)) {
    $sql .= " AND (nome LIKE ? OR razao_social LIKE ? OR nome_fantasia LIKE ? OR cnpj_cpf LIKE ? OR email LIKE ?)";
    $buscaParam = "%$busca%";
    $params = array_fill(0, 5, $buscaParam);
}

if (!empty($filtroTipo)) {
    $sql .= " AND tipo_cliente = ?";
    $params[] = $filtroTipo;
}

$sql .= " ORDER BY data_cadastro DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$clientes = $stmt->fetchAll();

include 'header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Gerenciamento de Clientes</h2>
        </div>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
            <div class="search-box" style="flex: 1; min-width: 250px;">
                <form method="GET" style="display: flex; gap: 0.5rem;">
                    <input type="text" name="busca" placeholder="Buscar por nome, CNPJ/CPF, e-mail..." value="<?php echo htmlspecialchars($busca); ?>">
                    <select name="tipo" style="width: auto;">
                        <option value="">Todos</option>
                        <option value="LEAD" <?php echo $filtroTipo == 'LEAD' ? 'selected' : ''; ?>>Leads</option>
                        <option value="CLIENTE" <?php echo $filtroTipo == 'CLIENTE' ? 'selected' : ''; ?>>Clientes</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </form>
            </div>
            <a href="cliente_form.php" class="btn btn-success">+ Novo Cliente</a>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>CNPJ/CPF</th>
                        <th>Nome/Razão Social</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Cidade/UF</th>
                        <th>Data Cadastro</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">
                                Nenhum cliente encontrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?php echo strtolower($cliente['tipo_cliente']); ?>">
                                        <?php echo $cliente['tipo_cliente']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    echo $cliente['tipo_pessoa'] == 'CNPJ' 
                                        ? formatCNPJ($cliente['cnpj_cpf']) 
                                        : formatCPF($cliente['cnpj_cpf']); 
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    echo $cliente['tipo_pessoa'] == 'CNPJ' 
                                        ? ($cliente['razao_social'] ?: $cliente['nome_fantasia']) 
                                        : $cliente['nome']; 
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                                <td><?php echo formatTelefone($cliente['celular'] ?: $cliente['telefone']); ?></td>
                                <td><?php echo htmlspecialchars($cliente['cidade']) . '/' . htmlspecialchars($cliente['estado']); ?></td>
                                <td><?php echo formatData($cliente['data_cadastro']); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="cliente_form.php?id=<?php echo $cliente['id']; ?>" class="btn btn-primary">Editar</a>
                                        <a href="cliente_delete.php?id=<?php echo $cliente['id']; ?>" 
                                           class="btn btn-danger" 
                                           onclick="return confirmarExclusao('Tem certeza que deseja excluir este cliente?')">
                                            Excluir
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
