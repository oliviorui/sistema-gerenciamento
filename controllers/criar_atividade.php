<?php
require_once '../config/bootstrap.php';

require_funcionario_or_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/funcionario/atividades.php");
    exit();
}

csrf_verify_or_exit();

$id_disciplina = (int)($_POST['id_disciplina'] ?? 0);
$titulo = trim((string)($_POST['titulo'] ?? ''));
$descricao = trim((string)($_POST['descricao'] ?? ''));
$data_limite = trim((string)($_POST['data_limite'] ?? ''));

if ($id_disciplina <= 0 || $titulo === '') {
    echo "<p>Disciplina e título são obrigatórios.</p>";
    exit();
}

if ($data_limite === '') {
    $data_limite = null;
} else {
    // formato esperado YYYY-MM-DD, mantemos como string
}

$criado_por = (int)($_SESSION['usuario_id'] ?? 0);

$sql = "INSERT INTO atividades (id_disciplina, titulo, descricao, data_limite, criado_por) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "<p>Erro ao preparar criação da atividade.</p>";
    exit();
}

$stmt->bind_param("isssi", $id_disciplina, $titulo, $descricao, $data_limite, $criado_por);
$stmt->execute();
$stmt->close();

// Log
$data_hora = date('Y-m-d H:i:s');
$descLog = "Criou atividade: $titulo";

$sqlLog = "INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Registro')";
$stmtLog = $conn->prepare($sqlLog);
if ($stmtLog) {
    $stmtLog->bind_param("iss", $criado_por, $data_hora, $descLog);
    $stmtLog->execute();
    $stmtLog->close();
}

header("Location: ../pages/funcionario/atividades.php");
exit();
