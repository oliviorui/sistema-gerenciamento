<?php
require_once '../config/bootstrap.php';

require_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/admin/usuarios.php");
    exit();
}

csrf_verify_or_exit();

$id = (int)($_POST['id'] ?? 0);
$novo_tipo = trim((string)($_POST['tipo'] ?? ''));

if ($id <= 0) {
    header("Location: ../pages/admin/usuarios.php");
    exit();
}

$allowed = ['estudante','docente','admin'];
if (!in_array($novo_tipo, $allowed, true)) {
    echo "<p>Tipo inválido.</p>";
    exit();
}

// Evita o admin se auto-rebaixar sem querer (opcional)
if ($id === (int)($_SESSION['usuario_id'] ?? 0) && $novo_tipo !== 'admin') {
    echo "<p>Você não pode remover o seu próprio acesso de administrador.</p>";
    exit();
}

$stmt = $conn->prepare("UPDATE usuarios SET tipo = ? WHERE id_usuario = ?");
if (!$stmt) {
    echo "<p>Erro ao preparar atualização.</p>";
    exit();
}

$stmt->bind_param("si", $novo_tipo, $id);
$stmt->execute();
$stmt->close();

header("Location: ../pages/admin/usuarios.php");
exit();
