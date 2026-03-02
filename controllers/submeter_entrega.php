<?php
require_once '../config/bootstrap.php';

require_login($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/estudante/entregas.php");
    exit();
}

csrf_verify_or_exit();

$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');
if ($tipo !== 'estudante') {
    http_response_code(403);
    echo "Apenas estudantes podem submeter entregas.";
    exit();
}

$idEstudante = (int)($_SESSION['usuario_id'] ?? 0);
$idAtividade = (int)($_POST['id_atividade'] ?? 0);
$comentario = trim((string)($_POST['comentario'] ?? ''));

if ($idAtividade <= 0) {
    echo "<p>Atividade inválida.</p>";
    exit();
}

$idTurmaEst = buscar_turma_do_estudante($conn, $idEstudante);
if ($idTurmaEst === null) {
    echo "<p>Sem turma associada. Contacta o administrador.</p>";
    exit();
}

/**
 * Verificar se a actividade pertence à turma do estudante
 */
$sql = "
SELECT 1
FROM atividades atv
INNER JOIN atribuicoes a ON a.id_atribuicao = atv.id_atribuicao
WHERE atv.id_atividade = ?
  AND a.id_turma = ?
LIMIT 1
";
$stmt = $conn->prepare($sql);
if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }

$stmt->bind_param("ii", $idAtividade, $idTurmaEst);
$stmt->execute();
$res = $stmt->get_result();
$ok = ($res && $res->num_rows > 0);
$stmt->close();

if (!$ok) {
    http_response_code(403);
    echo "<p>Operação não autorizada (actividade fora da tua turma).</p>";
    exit();
}

/**
 * Upload
 */
if (!isset($_FILES['arquivo']) || ($_FILES['arquivo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    echo "<p>Selecione um ficheiro válido.</p>";
    exit();
}

$upload = $_FILES['arquivo'];
$nomeOriginal = (string)$upload['name'];
$tmpPath = (string)$upload['tmp_name'];
$mime = (string)($upload['type'] ?? '');

$allowed = ['application/pdf', 'image/png', 'image/jpeg', 'application/zip', 'text/plain'];
$maxSize = 10 * 1024 * 1024; // 10MB

if (($upload['size'] ?? 0) > $maxSize) {
    echo "<p>Ficheiro excede o tamanho máximo (10MB).</p>";
    exit();
}
if ($mime !== '' && !in_array($mime, $allowed, true)) {
    echo "<p>Formato de ficheiro não permitido.</p>";
    exit();
}

$ext = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
$nomeServidor = bin2hex(random_bytes(16)) . ($ext ? ('.' . $ext) : '');
$dest = __DIR__ . '/../uploads/' . $nomeServidor;

if (!move_uploaded_file($tmpPath, $dest)) {
    echo "<p>Falha ao guardar ficheiro.</p>";
    exit();
}

/**
 * Insert/Update submissão (1 por actividade)
 */
$sqlUp = "
INSERT INTO entregas (id_atividade, id_estudante, arquivo_nome_original, arquivo_nome_servidor, arquivo_mime, comentario)
VALUES (?, ?, ?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
  arquivo_nome_original = VALUES(arquivo_nome_original),
  arquivo_nome_servidor = VALUES(arquivo_nome_servidor),
  arquivo_mime = VALUES(arquivo_mime),
  comentario = VALUES(comentario),
  status = 'Pendente',
  data_entrega = NOW()
";
$stmt2 = $conn->prepare($sqlUp);
if (!$stmt2) { echo "<p>Erro ao registar submissão.</p>"; exit(); }

$stmt2->bind_param("iissss", $idAtividade, $idEstudante, $nomeOriginal, $nomeServidor, $mime, $comentario);
$stmt2->execute();
$stmt2->close();

header("Location: ../pages/estudante/entregas.php");
exit();
