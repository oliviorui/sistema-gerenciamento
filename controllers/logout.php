<?php
require_once '../config/bootstrap.php';

start_secure_session();

/**
 * 1️⃣ Remover token remember-me (se existir)
 */
$cookieToken = $_COOKIE['remember_token'] ?? '';

if (!empty($cookieToken) && is_string($cookieToken)) {

    $tokenHash = hash('sha256', $cookieToken);

    $stmt = $conn->prepare("DELETE FROM user_tokens WHERE token_hash = ?");
    if ($stmt) {
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * 2️⃣ Registrar log de logout (SE o usuário ainda existir)
 */
$id_usuario = $_SESSION['usuario_id'] ?? null;

if (!empty($id_usuario)) {

    $stmtLog = $conn->prepare("
        INSERT INTO logs_atividades 
        (id_usuario, data_hora, descricao, tipo_actividade)
        SELECT id_usuario, ?, ?, 'Logout'
        FROM usuarios
        WHERE id_usuario = ?
    ");

    if ($stmtLog) {
        $data_hora = date('Y-m-d H:i:s');
        $descricao = 'Logout realizado';

        $stmtLog->bind_param("ssi", $data_hora, $descricao, $id_usuario);
        $stmtLog->execute();
        $stmtLog->close();
    }
}

/**
 * 3️⃣ Limpar cookie remember-me
 */
remember_cookie_clear();

/**
 * 4️⃣ Destruir sessão corretamente
 */
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

/**
 * 5️⃣ Evitar cache
 */
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/**
 * 6️⃣ Redirecionar para login
 */
header("Location: ../pages/auth/login.php");
exit();