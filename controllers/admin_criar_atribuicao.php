<?php
require_once '../config/bootstrap.php';
require_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../pages/admin/atribuicoes.php");
  exit();
}

csrf_verify_or_exit();

$id_docente = (int)($_POST['id_docente'] ?? 0);
$id_turma = (int)($_POST['id_turma'] ?? 0);
$id_disciplina = (int)($_POST['id_disciplina'] ?? 0);

if ($id_docente <= 0 || $id_turma <= 0 || $id_disciplina <= 0) {
  echo "<p>Dados inválidos.</p>";
  exit();
}

/** Confirma que o usuário é docente */
$stmt0 = $conn->prepare("SELECT 1 FROM usuarios WHERE id_usuario = ? AND tipo='docente' LIMIT 1");
$stmt0->bind_param("i", $id_docente);
$stmt0->execute();
$r0 = $stmt0->get_result();
$okDocente = ($r0 && $r0->num_rows > 0);
$stmt0->close();

if (!$okDocente) {
  echo "<p>O utilizador selecionado não é docente.</p>";
  exit();
}

$stmt = $conn->prepare("INSERT INTO atribuicoes (id_docente, id_turma, id_disciplina) VALUES (?, ?, ?)");
if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }

$stmt->bind_param("iii", $id_docente, $id_turma, $id_disciplina);
$stmt->execute();

/** Se for duplicado (unique), não quebra o sistema */
$stmt->close();

header("Location: ../pages/admin/atribuicoes.php");
exit();
