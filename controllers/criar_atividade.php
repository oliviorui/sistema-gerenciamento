<?php
require_once '../config/bootstrap.php';

require_docente_or_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/docente/atividades.php");
    exit();
}

csrf_verify_or_exit();

$idDocente = (int)($_SESSION['usuario_id'] ?? 0);

$idAtribuicao = (int)($_POST['id_atribuicao'] ?? 0);
$titulo = trim((string)($_POST['titulo'] ?? ''));
$descricao = trim((string)($_POST['descricao'] ?? ''));
$data_limite = trim((string)($_POST['data_limite'] ?? ''));

if ($idAtribuicao <= 0 || $titulo === '') {
    echo "<p>Preencha os campos obrigatórios.</p>";
    exit();
}

/**
 * Atribuição precisa pertencer ao docente
 */
$stmt = $conn->prepare("SELECT 1 FROM atribuicoes WHERE id_atribuicao = ? AND id_docente = ? LIMIT 1");
if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }

$stmt->bind_param("ii", $idAtribuicao, $idDocente);
$stmt->execute();
$res = $stmt->get_result();
$ok = ($res && $res->num_rows > 0);
$stmt->close();

if (!$ok) {
    http_response_code(403);
    echo "<p>Operação não autorizada.</p>";
    exit();
}

$stmt2 = $conn->prepare("INSERT INTO atividades (id_atribuicao, titulo, descricao, data_limite) VALUES (?, ?, ?, ?)");
if (!$stmt2) { echo "<p>Erro ao criar actividade.</p>"; exit(); }

$stmt2->bind_param("isss", $idAtribuicao, $titulo, $descricao, $data_limite);
$stmt2->execute();
$stmt2->close();

header("Location: ../pages/docente/atividades.php");
exit();
