<?php
declare(strict_types=1);

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/acl.php';

/**
 * Sessão mais segura (cookie httponly + samesite)
 */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/**
 * CSRF
 */
function csrf_get_token(): string
{
    start_secure_session();

    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return (string)$_SESSION['csrf_token'];
}

function csrf_verify_or_exit(): void
{
    start_secure_session();

    $token = (string)($_POST['csrf_token'] ?? '');
    $sessionToken = (string)($_SESSION['csrf_token'] ?? '');

    if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
        http_response_code(403);
        echo "<p>Falha de validação CSRF.</p>";
        exit();
    }
}

/**
 * Remember-me cookie
 */
function remember_cookie_set(string $token, int $days = 30): void
{
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    $expires = time() + ($days * 24 * 60 * 60);

    setcookie('remember_token', $token, [
        'expires' => $expires,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function remember_cookie_clear(): void
{
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/**
 * Login helpers
 */
function login_set_session(array $usuario): void
{
    start_secure_session();

    $_SESSION['usuario_id'] = (int)($usuario['id_usuario'] ?? 0);
    $_SESSION['usuario_nome'] = (string)($usuario['nome'] ?? '');
    $_SESSION['usuario_email'] = (string)($usuario['email'] ?? '');
    $_SESSION['usuario_tipo'] = (string)($usuario['tipo'] ?? 'estudante');
}

function is_logged_in(): bool
{
    start_secure_session();
    return !empty($_SESSION['usuario_id']);
}

/**
 * Auto-login por remember token (se existir) + sessão normal
 */
function require_login(mysqli $conn): void
{
    start_secure_session();

    if (!empty($_SESSION['usuario_id'])) {
        return;
    }

    $cookieToken = (string)($_COOKIE['remember_token'] ?? '');
    if ($cookieToken === '') {
        header('Location: ../auth/login.php');
        exit();
    }

    $tokenHash = hash('sha256', $cookieToken);

    $sql = "
        SELECT u.id_usuario, u.nome, u.email, u.tipo
        FROM user_tokens t
        INNER JOIN usuarios u ON u.id_usuario = t.id_usuario
        WHERE t.token_hash = ?
          AND t.expires_at > NOW()
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        header('Location: ../auth/login.php');
        exit();
    }

    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $res = $stmt->get_result();
    $usuario = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$usuario) {
        remember_cookie_clear();
        header('Location: ../auth/login.php');
        exit();
    }

    // Compatibilidade: migra tipos antigos
    if (($usuario['tipo'] ?? '') === 'funcionario') {
        $usuario['tipo'] = 'docente';
    }
    if (($usuario['tipo'] ?? '') === 'usuario') {
        $usuario['tipo'] = 'estudante';
    }

    login_set_session($usuario);
}

/**
 * Autorização por perfis
 */
function require_role(mysqli $conn, array $allowedRoles): void
{
    require_login($conn);

    $tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');

    // compat de dados antigos
    if ($tipo === 'funcionario') {
        $tipo = 'docente';
        $_SESSION['usuario_tipo'] = 'docente';
    }

    if (!in_array($tipo, $allowedRoles, true)) {
        http_response_code(403);
        echo "<p>Acesso negado.</p>";
        exit();
    }
}

function require_admin(mysqli $conn): void
{
    require_role($conn, ['admin']);
}

function require_docente_or_admin(mysqli $conn): void
{
    require_role($conn, ['docente', 'admin']);
}

// Backward-compat: caso ainda existam páginas antigas
function require_funcionario_or_admin(mysqli $conn): void
{
    require_docente_or_admin($conn);
}
