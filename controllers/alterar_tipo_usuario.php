<?php
require_once '../config/bootstrap.php';

require_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/admin/usuarios.php");
    exit();
}

csrf_verify_or_exit();

$id = (int)($_POST['id'] ?? 0);
$novo_tipo = trim((string)($_POST['tipo'] ?? ''));

if ($id <= 0) {
    header("Location: ../pages/admin/usuarios.php");
    exit();
}

$allowed = ['estudante', 'funcionario', 'admin'];
if (!in_array($novo_tipo, $allowed, true)) {
    echo "<p>Tipo inválido.</p>";
    exit();
}

// Evita o admin se auto-rebaixar sem querer (opcional)
if ($id === (int)($_SESSION['usuario_id'] ?? 0) && $novo_tipo !== 'admin') {
    echo "<p>Você não pode remover o seu próprio acesso de admin.</p>";
    exit();
}

$sqlUp = "UPDATE usuarios SET tipo = ? WHERE id_usuario = ?";
$stmtUp = $conn->prepare($sqlUp);

if ($stmtUp) {
    $stmtUp->bind_param("si", $novo_tipo, $id);
    $stmtUp->execute();
    $stmtUp->close();
}

// LOG
$data_hora = date('Y-m-d H:i:s');
$descricao = "Alterou tipo de usuário (ID $id) para $novo_tipo";

$sqlLog = "INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Admin')";
$stmtLog = $conn->prepare($sqlLog);

if ($stmtLog) {
    $idAdmin = (int)($_SESSION['usuario_id'] ?? 0);
    $stmtLog->bind_param("iss", $idAdmin, $data_hora, $descricao);
    $stmtLog->execute();
    $stmtLog->close();
}

header("Location: ../pages/admin/usuarios.php");
exit();
