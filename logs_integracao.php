<?php
require_once 'config.php';
require_once 'lib_logs.php';

$pageTitle = 'Logs de Integração';

// Processar filtros
$filtros = [];
if (!empty($_GET['tipo'])) $filtros['tipo'] = sanitize($_GET['tipo']);
if (!empty($_GET['status'])) $filtros['status'] = sanitize($_GET['status']);
if (!empty($_GET['acao'])) $filtros['acao'] = sanitize($_GET['acao']);
if (!empty($_GET['data_inicio'])) $filtros['data_inicio'] = $_GET['data_inicio'];
if (!empty($_GET['data_fim'])) $filtros['data_fim'] = $_GET['data_fim'];

// Paginação
$limite = 50;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;

// Buscar logs
$logs = LogIntegracao::buscar($filtros, $limite, $offset);
$totalLogs = LogIntegracao::contar($filtros);
$totalPaginas = ceil($totalLogs / $limite);

// Buscar estatísticas
$dataInicio = !empty($filtros['data_inicio']) ? $filtros['data_inicio'] : date('Y-m-d', strtotime('-30 days'));
$dataFim = !empty($filtros['data_fim']) ? $filtros['data_fim'] : date('Y-m-d');
$estatisticas = LogIntegracao::estatisticas($dataInicio, $dataFim);

// Processar estatísticas para exibição
$stats = [
    'total' => 0,
    'sucesso' => 0,
    'erro' => 0,
    'aviso' => 0,
    'tempo_medio' => 0
];

foreach ($estatisticas as $stat) {
    $stats['total'] += $stat['total'];
    $stats[$stat['status']] += $stat['total'];
    if ($stat['tempo_medio']) {
        $stats['tempo_medio'] += $stat['tempo_medio'];
    }
}

if (count($estatisticas) > 0) {
    $stats['tempo_medio'] = $stats['tempo_medio'] / count($estatisticas);
}

include 'header.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Logs de Integração - Monitoramento de APIs</h2>
        </div>
        
        <!-- Dashboard de Estatísticas -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; border-radius: 8px;">
                <p style="font-size: 0.875rem; margin-bottom: 0.5rem; opacity: 0.9;">Total de Logs</p>
                <p style="font-size: 1.5rem; font-weight: 600;"><?php echo number_format($stats['total'], 0, ',', '.'); ?></p>
                <small style="opacity: 0.9;">Últimos 30 dias</small>
            </div>
            
            <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 1rem; border-radius: 8px;">
                <p style="font-size: 0.875rem; margin-bottom: 0.5rem; opacity: 0.9;">Sucesso</p>
                <p style="font-size: 1.5rem; font-weight: 600;"><?php echo number_format($stats['sucesso'], 0, ',', '.'); ?></p>
                <small style="opacity: 0.9;">
                    <?php echo $stats['total'] > 0 ? number_format(($stats['sucesso'] / $stats['total']) * 100, 1) : 0; ?>%
                </small>
            </div>
            
            <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 1rem; border-radius: 8px;">
                <p style="font-size: 0.875rem; margin-bottom: 0.5rem; opacity: 0.9;">Erros</p>
                <p style="font-size: 1.5rem; font-weight: 600;"><?php echo number_format($stats['erro'], 0, ',', '.'); ?></p>
                <small style="opacity: 0.9;">
                    <?php echo $stats['total'] > 0 ? number_format(($stats['erro'] / $stats['total']) * 100, 1) : 0; ?>%
                </small>
            </div>
            
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1rem; border-radius: 8px;">
                <p style="font-size: 0.875rem; margin-bottom: 0.5rem; opacity: 0.9;">Tempo Médio</p>
                <p style="font-size: 1.5rem; font-weight: 600;"><?php echo number_format($stats['tempo_medio'], 3); ?>s</p>
                <small style="opacity: 0.9;">Resposta da API</small>
            </div>
        </div>
        
        <!-- Filtros -->
        <form method="GET" style="background: #f9fafb; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem;">
            <div class="form-row">
                <div class="form-group">
                    <label>Tipo de Integração</label>
                    <select name="tipo">
                        <option value="">Todos</option>
                        <option value="stripe" <?php echo ($filtros['tipo'] ?? '') == 'stripe' ? 'selected' : ''; ?>>Stripe</option>
                        <option value="cora" <?php echo ($filtros['tipo'] ?? '') == 'cora' ? 'selected' : ''; ?>>CORA</option>
                        <option value="api_cnpj" <?php echo ($filtros['tipo'] ?? '') == 'api_cnpj' ? 'selected' : ''; ?>>API CNPJ</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="">Todos</option>
                        <option value="sucesso" <?php echo ($filtros['status'] ?? '') == 'sucesso' ? 'selected' : ''; ?>>Sucesso</option>
                        <option value="erro" <?php echo ($filtros['erro'] ?? '') == 'erro' ? 'selected' : ''; ?>>Erro</option>
                        <option value="aviso" <?php echo ($filtros['aviso'] ?? '') == 'aviso' ? 'selected' : ''; ?>>Aviso</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Data Início</label>
                    <input type="date" name="data_inicio" value="<?php echo $filtros['data_inicio'] ?? ''; ?>">
                </div>
                
                <div class="form-group">
                    <label>Data Fim</label>
                    <input type="date" name="data_fim" value="<?php echo $filtros['data_fim'] ?? ''; ?>">
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="logs_integracao.php" class="btn btn-secondary">Limpar Filtros</a>
            </div>
        </form>
        
        <!-- Tabela de Logs -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Data/Hora</th>
                        <th>Tipo</th>
                        <th>Ação</th>
                        <th>Status</th>
                        <th>Mensagem</th>
                        <th>Tempo</th>
                        <th>HTTP</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">
                                Nenhum log encontrado com os filtros selecionados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td style="white-space: nowrap;">
                                    <?php echo formatDataHora($log['data_log']); ?>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $log['tipo'] == 'stripe' ? 'cliente' : 'lead'; ?>">
                                        <?php echo strtoupper($log['tipo']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['acao']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $log['status']; ?>">
                                        <?php echo ucfirst($log['status']); ?>
                                    </span>
                                </td>
                                <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($log['mensagem']); ?>
                                </td>
                                <td>
                                    <?php echo $log['tempo_resposta'] ? number_format($log['tempo_resposta'], 3) . 's' : '-'; ?>
                                </td>
                                <td>
                                    <?php if ($log['codigo_http']): ?>
                                        <span style="color: <?php echo $log['codigo_http'] >= 200 && $log['codigo_http'] < 300 ? '#10b981' : '#ef4444'; ?>; font-weight: 600;">
                                            <?php echo $log['codigo_http']; ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick="verDetalhes(<?php echo $log['id']; ?>)" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                        Ver Detalhes
                                    </button>
                                </td>
                            </tr>
                            
                            <!-- Detalhes do log (oculto por padrão) -->
                            <tr id="detalhes_<?php echo $log['id']; ?>" style="display: none;">
                                <td colspan="8" style="background: #f9fafb; padding: 1.5rem;">
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                                        <div>
                                            <h4 style="margin-bottom: 0.5rem; color: #1e40af;">Request (Enviado)</h4>
                                            <pre style="background: white; padding: 1rem; border-radius: 4px; overflow-x: auto; font-size: 0.875rem; max-height: 300px;"><?php echo htmlspecialchars($log['request_data'] ?: 'Nenhum dado de request'); ?></pre>
                                        </div>
                                        <div>
                                            <h4 style="margin-bottom: 0.5rem; color: #1e40af;">Response (Recebido)</h4>
                                            <pre style="background: white; padding: 1rem; border-radius: 4px; overflow-x: auto; font-size: 0.875rem; max-height: 300px;"><?php echo htmlspecialchars($log['response_data'] ?: 'Nenhum dado de response'); ?></pre>
                                        </div>
                                    </div>
                                    
                                    <?php if ($log['referencia_tipo'] && $log['referencia_id']): ?>
                                        <div style="margin-top: 1rem; padding: 1rem; background: white; border-radius: 4px;">
                                            <strong>Referência:</strong> 
                                            <?php echo ucfirst(str_replace('_', ' ', $log['referencia_tipo'])); ?> 
                                            #<?php echo $log['referencia_id']; ?>
                                            
                                            <?php if ($log['ip_origem']): ?>
                                                | <strong>IP:</strong> <?php echo htmlspecialchars($log['ip_origem']); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <?php if ($totalPaginas > 1): ?>
            <div style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 1.5rem;">
                <?php if ($pagina > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])); ?>" class="btn btn-secondary">
                        Anterior
                    </a>
                <?php endif; ?>
                
                <span style="padding: 0.75rem 1.5rem; background: #f3f4f6; border-radius: 4px;">
                    Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?>
                </span>
                
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])); ?>" class="btn btn-secondary">
                        Próxima
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function verDetalhes(logId) {
        const detalhes = document.getElementById('detalhes_' + logId);
        if (detalhes.style.display === 'none') {
            detalhes.style.display = 'table-row';
        } else {
            detalhes.style.display = 'none';
        }
    }
</script>

<?php include 'footer.php'; ?>
