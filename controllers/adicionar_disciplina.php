<?php
require_once '../config/bootstrap.php';

require_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/admin/disciplinas.php");
    exit();
}

csrf_verify_or_exit();

$nome = trim((string)($_POST['nome'] ?? ''));
$codigo = trim((string)($_POST['codigo'] ?? ''));
$descricao = trim((string)($_POST['descricao'] ?? ''));

if ($nome === '' || $codigo === '') {
    echo "<p>Nome e código são obrigatórios.</p>";
    exit();
}

$sql = "INSERT INTO disciplinas (nome, codigo, descricao) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "<p>Erro ao preparar inserção.</p>";
    exit();
}

$stmt->bind_param("sss", $nome, $codigo, $descricao);
$stmt->execute();
$stmt->close();

// LOG
$data_hora = date('Y-m-d H:i:s');
$descricao_log = "Adicionou disciplina: $nome";

$sqlLog = "INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Admin')";
$stmtLog = $conn->prepare($sqlLog);

if ($stmtLog) {
    $idAdmin = (int)($_SESSION['usuario_id'] ?? 0);
    $stmtLog->bind_param("iss", $idAdmin, $data_hora, $descricao_log);
    $stmtLog->execute();
    $stmtLog->close();
}

header("Location: ../pages/admin/disciplinas.php");
exit();
