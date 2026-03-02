<?php
require_once '../config/bootstrap.php';

require_login($conn);

$id_entrega = 0;
if (isset($_GET['id_entrega'])) $id_entrega = (int)$_GET['id_entrega'];
elseif (isset($_GET['id'])) $id_entrega = (int)$_GET['id'];

if ($id_entrega <= 0) {
  echo "<p>Entrega inválida.</p>";
  exit();
}

$idUser = (int)($_SESSION['usuario_id'] ?? 0);
$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');

/**
 * Buscar entrega + dono + caminho + atribuição/docente
 */
$sql = "
SELECT
  e.id_entrega,
  e.id_estudante,
  e.arquivo_nome_original,
  e.arquivo_nome_servidor,
  e.arquivo_mime,

  a.id_docente
FROM entregas e
INNER JOIN atividades atv ON atv.id_atividade = e.id_atividade
INNER JOIN atribuicoes a ON a.id_atribuicao = atv.id_atribuicao
WHERE e.id_entrega = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }

$stmt->bind_param("i", $id_entrega);
$stmt->execute();
$res = $stmt->get_result();
$row = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$row) {
  echo "<p>Entrega não encontrada.</p>";
  exit();
}

/**
 * Regras de acesso:
 * - admin: sempre pode
 * - docente: só se for o docente da atribuição
 * - estudante: só se for o dono
 */
if ($tipo === 'docente') {
  if ((int)$row['id_docente'] !== $idUser) {
    http_response_code(403);
    echo "<p>Acesso negado.</p>";
    exit();
  }
} elseif ($tipo === 'estudante') {
  if ((int)$row['id_estudante'] !== $idUser) {
    http_response_code(403);
    echo "<p>Acesso negado.</p>";
    exit();
  }
} else {
  // admin OK
}

$filePath = __DIR__ . '/../uploads/' . (string)$row['arquivo_nome_servidor'];
if (!is_file($filePath)) {
  echo "<p>Ficheiro não encontrado no servidor.</p>";
  exit();
}

$mime = (string)($row['arquivo_mime'] ?? 'application/octet-stream');
$original = (string)($row['arquivo_nome_original'] ?? 'arquivo');

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . basename($original) . '"');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit();
