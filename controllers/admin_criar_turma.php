<?php
require_once '../config/bootstrap.php';
require_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../pages/admin/turmas.php");
  exit();
}

csrf_verify_or_exit();

$nome = trim((string)($_POST['nome'] ?? ''));
if ($nome === '') {
  echo "<p>Nome inválido.</p>";
  exit();
}

$stmt = $conn->prepare("INSERT INTO turmas (nome) VALUES (?)");
if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }

$stmt->bind_param("s", $nome);
$stmt->execute();
$stmt->close();

header("Location: ../pages/admin/turmas.php");
exit();
