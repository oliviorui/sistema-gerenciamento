<?php
// config/bootstrap.php
declare(strict_types=1);

require_once __DIR__ . '/conexao.php';

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

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    $token = csrf_get_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_verify_or_exit(): void
{
    start_secure_session();

    $sent = $_POST['csrf_token'] ?? '';
    $ok = is_string($sent) && isset($_SESSION['csrf_token']) && hash_equals((string)$_SESSION['csrf_token'], $sent);

    if (!$ok) {
        http_response_code(403);
        echo "Ação bloqueada (CSRF inválido).";
        exit();
    }
}

/**
 * Remember-me token (cookie + DB)
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

function login_set_session(array $usuario): void
{
    start_secure_session();
    session_regenerate_id(true);

    $_SESSION['usuario_id'] = (int)$usuario['id_usuario'];
    $_SESSION['usuario_nome'] = (string)$usuario['nome'];
    $_SESSION['usuario_tipo'] = (string)$usuario['tipo'];
}

function try_auto_login_by_remember_token(mysqli $conn): void
{
    start_secure_session();

    if (!empty($_SESSION['usuario_id'])) {
        return;
    }

    $cookieToken = $_COOKIE['remember_token'] ?? '';
    if (!is_string($cookieToken) || $cookieToken === '') {
        return;
    }

    $tokenHash = hash('sha256', $cookieToken);

    $sql = "
        SELECT u.id_usuario, u.nome, u.tipo
        FROM user_tokens t
        INNER JOIN usuarios u ON u.id_usuario = t.id_usuario
        WHERE t.token_hash = ?
          AND t.expires_at > NOW()
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return;
    }

    $stmt->bind_param("s", $tokenHash);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows === 1) {
        $usuario = $res->fetch_assoc();
        if (is_array($usuario)) {
            login_set_session($usuario);
        }
    }

    $stmt->close();
}

function require_login(mysqli $conn): void
{
    start_secure_session();
    try_auto_login_by_remember_token($conn);

    if (empty($_SESSION['usuario_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
}

/**
 * Roles
 */
function require_role(mysqli $conn, array $allowedRoles): void
{
    require_login($conn);

    $tipo = (string)($_SESSION['usuario_tipo'] ?? '');
    if (!in_array($tipo, $allowedRoles, true)) {
        http_response_code(403);
        echo "Sem permissão.";
        exit();
    }
}

function require_admin(mysqli $conn): void
{
    require_role($conn, ['admin']);
}

function require_funcionario_or_admin(mysqli $conn): void
{
    require_role($conn, ['funcionario', 'admin']);
}
