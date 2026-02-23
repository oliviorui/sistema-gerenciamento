<?php
require_once '../config/bootstrap.php';

start_secure_session();

// Remove token do BD (se existir cookie)
$cookieToken = $_COOKIE['remember_token'] ?? '';
if (is_string($cookieToken) && $cookieToken !== '') {
    $tokenHash = hash('sha256', $cookieToken);

    $sqlDel = "DELETE FROM user_tokens WHERE token_hash = ?";
    $stmt = $conn->prepare($sqlDel);
    if ($stmt) {
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $stmt->close();
    }
}

if (!empty($_SESSION['usuario_id'])) {
    $id_usuario = (int)$_SESSION['usuario_id'];

    $data_hora = date('Y-m-d H:i:s');
    $descricao = 'Logout realizado';

    $sqlLog = "INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Logout')";
    $stmtLog = $conn->prepare($sqlLog);
    if ($stmtLog) {
        $stmtLog->bind_param("iss", $id_usuario, $data_hora, $descricao);
        $stmtLog->execute();
        $stmtLog->close();
    }
}

remember_cookie_clear();

session_unset();
session_destroy();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

header("Location: ../pages/auth/login.php");
exit();
