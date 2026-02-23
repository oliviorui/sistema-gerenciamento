<?php
require_once '../config/bootstrap.php';

require_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/admin/disciplinas.php");
    exit();
}

csrf_verify_or_exit();

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header("Location: ../pages/admin/disciplinas.php");
    exit();
}

$sql = "DELETE FROM disciplinas WHERE id_disciplina = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

// LOG
$data_hora = date('Y-m-d H:i:s');
$descricao = "Excluiu uma disciplina";

$sqlLog = "INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Admin')";
$stmtLog = $conn->prepare($sqlLog);

if ($stmtLog) {
    $idAdmin = (int)($_SESSION['usuario_id'] ?? 0);
    $stmtLog->bind_param("iss", $idAdmin, $data_hora, $descricao);
    $stmtLog->execute();
    $stmtLog->close();
}

header("Location: ../pages/admin/disciplinas.php");
exit();
