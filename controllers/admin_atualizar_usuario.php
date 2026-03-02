<?php
require_once '../config/bootstrap.php';
require_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: ../pages/admin/usuarios.php");
  exit();
}

csrf_verify_or_exit();

$id = (int)($_POST['id_usuario'] ?? 0);
$tipo = trim((string)($_POST['tipo'] ?? ''));
$id_turma_raw = (string)($_POST['id_turma'] ?? '');

if ($id <= 0) { header("Location: ../pages/admin/usuarios.php"); exit(); }

$allowed = ['estudante','docente','admin'];
if (!in_array($tipo, $allowed, true)) { echo "<p>Tipo inválido.</p>"; exit(); }

if ($id === (int)($_SESSION['usuario_id'] ?? 0) && $tipo !== 'admin') {
  echo "<p>Você não pode remover o seu próprio acesso de administrador.</p>";
  exit();
}

$id_turma = null;
if ($tipo === 'estudante' && $id_turma_raw !== '') {
  $tmp = (int)$id_turma_raw;
  if ($tmp > 0) $id_turma = $tmp;
}

if ($id_turma === null) {
  $stmt = $conn->prepare("UPDATE usuarios SET tipo = ?, id_turma = NULL WHERE id_usuario = ?");
  if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }
  $stmt->bind_param("si", $tipo, $id);
} else {
  $stmt = $conn->prepare("UPDATE usuarios SET tipo = ?, id_turma = ? WHERE id_usuario = ?");
  if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }
  $stmt->bind_param("sii", $tipo, $id_turma, $id);
}

$stmt->execute();
$stmt->close();

header("Location: ../pages/admin/usuarios.php");
exit();
