<?php
require_once '../config/bootstrap.php';
require_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../pages/admin/disciplinas.php");
  exit();
}

csrf_verify_or_exit();

$id = (int)($_POST['id_disciplina'] ?? 0);
if ($id <= 0) {
  header("Location: ../pages/admin/disciplinas.php");
  exit();
}

$stmt = $conn->prepare("DELETE FROM disciplinas WHERE id_disciplina = ?");
if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: ../pages/admin/disciplinas.php");
exit();
