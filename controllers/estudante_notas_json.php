<?php
require_once '../config/bootstrap.php';
require_login($conn);

header('Content-Type: application/json; charset=utf-8');

$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');
if ($tipo !== 'estudante') {
  http_response_code(403);
  echo json_encode(['error' => 'Acesso negado']);
  exit();
}

$idEstudante = (int)($_SESSION['usuario_id'] ?? 0);

$sql = "
SELECT
  d.nome AS disciplina,
  ROUND(AVG(n.nota), 2) AS media
FROM notas n
INNER JOIN disciplinas d ON d.id_disciplina = n.id_disciplina
WHERE n.id_estudante = ?
GROUP BY d.id_disciplina
ORDER BY d.nome ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['error' => 'Erro interno']);
  exit();
}

$stmt->bind_param("i", $idEstudante);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

$labels = [];
$values = [];

while ($row = $res->fetch_assoc()) {
  $labels[] = (string)$row['disciplina'];
  $values[] = (float)$row['media'];
}

echo json_encode([
  'labels' => $labels,
  'values' => $values
]);
exit();
