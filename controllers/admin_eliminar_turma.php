<?php
require_once '../config/bootstrap.php';
require_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../pages/admin/turmas.php");
  exit();
}

csrf_verify_or_exit();

$id = (int)($_POST['id_turma'] ?? 0);
if ($id <= 0) {
  header("Location: ../pages/admin/turmas.php");
  exit();
}

$stmt = $conn->prepare("DELETE FROM turmas WHERE id_turma = ?");
if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: ../pages/admin/turmas.php");
exit();
