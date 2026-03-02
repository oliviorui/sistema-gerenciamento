<?php
require_once '../config/bootstrap.php';

require_docente_or_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../pages/docente/entregas.php");
  exit();
}

csrf_verify_or_exit();

$idDocente = (int)($_SESSION['usuario_id'] ?? 0);

$id_entrega = (int)($_POST['id_entrega'] ?? 0);
$notaRaw = (string)($_POST['nota'] ?? '');
$feedback = trim((string)($_POST['feedback'] ?? ''));
$status = trim((string)($_POST['status'] ?? 'Pendente'));

if ($id_entrega <= 0 || $notaRaw === '') {
  echo "<p>Dados inválidos.</p>";
  exit();
}

$nota = (float)$notaRaw;
if ($nota < 0 || $nota > 20) {
  echo "<p>Nota inválida.</p>";
  exit();
}

if (!in_array($status, ['Pendente','Avaliado'], true)) {
  echo "<p>Status inválido.</p>";
  exit();
}

/**
 * Verifica se esta entrega pertence a uma actividade das atribuições do docente
 */
$sqlCheck = "
SELECT 1
FROM entregas e
INNER JOIN atividades atv ON atv.id_atividade = e.id_atividade
INNER JOIN atribuicoes a ON a.id_atribuicao = atv.id_atribuicao
WHERE e.id_entrega = ?
  AND a.id_docente = ?
LIMIT 1
";
$stmt = $conn->prepare($sqlCheck);
if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }

$stmt->bind_param("ii", $id_entrega, $idDocente);
$stmt->execute();
$res = $stmt->get_result();
$ok = ($res && $res->num_rows > 0);
$stmt->close();

if (!$ok) {
  http_response_code(403);
  echo "<p>Operação não autorizada.</p>";
  exit();
}

/**
 * Atualiza avaliação
 */
$stmt2 = $conn->prepare("UPDATE entregas SET nota = ?, feedback = ?, status = ? WHERE id_entrega = ?");
if (!$stmt2) { echo "<p>Erro ao atualizar.</p>"; exit(); }

$stmt2->bind_param("dssi", $nota, $feedback, $status, $id_entrega);
$stmt2->execute();
$stmt2->close();

/**
 * Log
 */
$data_hora = date('Y-m-d H:i:s');
$descricao = "Submissão avaliada (Entrega:$id_entrega, Nota:$nota)";
$stmtLog = $conn->prepare("INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Submissao')");
if ($stmtLog) {
  $stmtLog->bind_param("iss", $idDocente, $data_hora, $descricao);
  $stmtLog->execute();
  $stmtLog->close();
}

header("Location: ../pages/docente/entregas.php");
exit();
