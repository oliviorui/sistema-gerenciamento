<?php
require_once '../config/bootstrap.php';

require_login($conn);

// Aceita id_entrega ou id (compatibilidade)
$id_entrega = 0;

if (isset($_GET['id_entrega'])) {
    $id_entrega = (int)$_GET['id_entrega'];
} elseif (isset($_GET['id'])) {
    $id_entrega = (int)$_GET['id'];
}

if ($id_entrega <= 0) {
    $tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');
    if ($tipo === 'admin' || $tipo === 'funcionario') {
        header("Location: ../pages/funcionario/entregas.php");
        exit();
    }
    header("Location: ../pages/logged/entregas.php");
    exit();
}

$id_user = (int)($_SESSION['usuario_id'] ?? 0);
$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');

$sql = "
SELECT
    e.id_entrega,
    e.id_estudante,
    e.arquivo_nome_original,
    e.arquivo_nome_servidor,
    e.arquivo_mime
FROM entregas e
WHERE e.id_entrega = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo "Erro ao preparar consulta.";
    exit();
}

$stmt->bind_param("i", $id_entrega);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if (!$res || $res->num_rows !== 1) {
    http_response_code(404);
    echo "Entrega não encontrada.";
    exit();
}

$entrega = $res->fetch_assoc();
if (!is_array($entrega)) {
    http_response_code(404);
    echo "Entrega não encontrada.";
    exit();
}

// Permissão
$id_estudante = (int)($entrega['id_estudante'] ?? 0);

$canDownload = false;
if ($tipo === 'admin' || $tipo === 'funcionario') {
    $canDownload = true;
} elseif ($tipo === 'estudante' && $id_user === $id_estudante) {
    $canDownload = true;
}

if (!$canDownload) {
    http_response_code(403);
    echo "Sem permissão para baixar este arquivo.";
    exit();
}

$serverName = (string)($entrega['arquivo_nome_servidor'] ?? '');
$origName = (string)($entrega['arquivo_nome_original'] ?? 'arquivo');

if ($serverName === '') {
    http_response_code(404);
    echo "Esta entrega não tem arquivo.";
    exit();
}

$serverNameSafe = basename($serverName);
$path = __DIR__ . '/../uploads/entregas/' . $serverNameSafe;

if (!is_file($path)) {
    http_response_code(404);
    echo "Arquivo não encontrado no servidor.";
    exit();
}

$mime = (string)($entrega['arquivo_mime'] ?? '');
if ($mime === '') {
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($path) ?: 'application/octet-stream';
}

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . rawurlencode($origName) . '"');
header('Content-Length: ' . (string)filesize($path));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

readfile($path);
exit();
