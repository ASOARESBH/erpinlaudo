<?php
/**
 * Sistema de Autenticação e Controle de Sessão
 * 
 * Incluir este arquivo no início de todas as páginas protegidas
 * Exemplo: require_once 'auth.php';
 */

// Iniciar sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir tempo de expiração da sessão (30 minutos de inatividade)
define('SESSION_TIMEOUT', 1800); // 30 minutos em segundos

/**
 * Verificar se o usuário está autenticado
 */
function verificarAutenticacao() {
    // Verificar se existe sessão ativa
    if (!isset($_SESSION['usuario_id'])) {
        // Redirecionar para login
        header('Location: login.php');
        exit;
    }
    
    // Verificar timeout de inatividade
    if (isset($_SESSION['ultimo_acesso'])) {
        $tempoInativo = time() - $_SESSION['ultimo_acesso'];
        
        if ($tempoInativo > SESSION_TIMEOUT) {
            // Sessão expirou
            session_unset();
            session_destroy();
            header('Location: login.php?timeout=1');
            exit;
        }
    }
    
    // Atualizar último acesso
    $_SESSION['ultimo_acesso'] = time();
}

/**
 * Verificar se o usuário é administrador
 */
function verificarAdmin() {
    verificarAutenticacao();
    
    if ($_SESSION['usuario_nivel'] !== 'admin') {
        header('Location: index.php?erro=acesso_negado');
        exit;
    }
}

/**
 * Obter informações do usuário logado
 */
function getUsuarioLogado() {
    return [
        'id' => $_SESSION['usuario_id'] ?? null,
        'nome' => $_SESSION['usuario_nome'] ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
        'nivel' => $_SESSION['usuario_nivel'] ?? '',
        'login_time' => $_SESSION['login_time'] ?? time()
    ];
}

/**
 * Calcular tempo de sessão
 */
function getTempoSessao() {
    if (!isset($_SESSION['login_time'])) {
        return '0 min';
    }
    
    $segundos = time() - $_SESSION['login_time'];
    
    $horas = floor($segundos / 3600);
    $minutos = floor(($segundos % 3600) / 60);
    
    if ($horas > 0) {
        return "{$horas}h {$minutos}min";
    } else {
        return "{$minutos}min";
    }
}

/**
 * Fazer logout
 */
function logout() {
    require_once 'config.php';
    
    try {
        // Registrar logout no log
        if (isset($_SESSION['usuario_id'])) {
            $conn = getConnection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            
            $stmt = $conn->prepare("INSERT INTO logs_acesso (usuario_id, email, acao, ip, user_agent) VALUES (?, ?, 'logout', ?, ?)");
            $stmt->execute([
                $_SESSION['usuario_id'],
                $_SESSION['usuario_email'],
                $ip,
                $userAgent
            ]);
        }
    } catch (Exception $e) {
        error_log("Erro ao registrar logout: " . $e->getMessage());
    }
    
    // Destruir sessão
    session_unset();
    session_destroy();
    
    // Redirecionar para login
    header('Location: login.php?logout=1');
    exit;
}

// Verificar autenticação automaticamente
verificarAutenticacao();
?>
