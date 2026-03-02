<?php
require_once '../config/bootstrap.php';
require_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../pages/admin/usuarios.php");
  exit();
}

csrf_verify_or_exit();

$id = (int)($_POST['id_usuario'] ?? 0);
if ($id <= 0) {
  header("Location: ../pages/admin/usuarios.php");
  exit();
}

/** Evita excluir o próprio admin logado */
if ($id === (int)($_SESSION['usuario_id'] ?? 0)) {
  echo "<p>Você não pode excluir o seu próprio utilizador.</p>";
  exit();
}

$stmt = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }

$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

header("Location: ../pages/admin/usuarios.php");
exit();
