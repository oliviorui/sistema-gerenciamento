<?php
require_once '../config/bootstrap.php';

require_docente_or_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/docente/dashboard_docente.php');
    exit();
}

csrf_verify_or_exit();

$idDocente = (int)($_SESSION['usuario_id'] ?? 0);

$idAtribuicao = (int)($_POST['id_atribuicao'] ?? 0);
$idEstudante = (int)($_POST['id_estudante'] ?? 0);

$tipoAvaliacao = trim((string)($_POST['tipo_avaliacao'] ?? ''));
$notaRaw = (string)($_POST['nota'] ?? '');
$dataAvaliacao = trim((string)($_POST['data_avaliacao'] ?? ''));

if ($idAtribuicao <= 0 || $idEstudante <= 0 || $tipoAvaliacao === '' || $notaRaw === '') {
    echo "<p>Preencha os campos obrigatórios.</p>";
    exit();
}

$nota = (float)$notaRaw;
if ($nota < 0 || $nota > 20) {
    echo "<p>Nota inválida. Use um valor entre 0 e 20.</p>";
    exit();
}

/**
 * Buscar turma+disciplina da atribuição, mas só se pertencer ao docente logado
 */
$sql = "SELECT id_turma, id_disciplina FROM atribuicoes WHERE id_atribuicao = ? AND id_docente = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) { echo "<p>Erro interno.</p>"; exit(); }

$stmt->bind_param("ii", $idAtribuicao, $idDocente);
$stmt->execute();
$res = $stmt->get_result();
$atr = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$atr) {
    http_response_code(403);
    echo "<p>Operação não autorizada (atribuição inválida).</p>";
    exit();
}

$idTurma = (int)$atr['id_turma'];
$idDisciplina = (int)$atr['id_disciplina'];

/**
 * Validar se estudante pertence à mesma turma da atribuição
 */
$idTurmaEst = buscar_turma_do_estudante($conn, $idEstudante);
if ($idTurmaEst === null || $idTurmaEst !== $idTurma) {
    http_response_code(403);
    echo "<p>Operação não autorizada (estudante fora da turma).</p>";
    exit();
}

/**
 * Inserir nota
 */
$sqlIns = "
INSERT INTO notas (id_docente, id_estudante, id_disciplina, tipo_avaliacao, nota, data_avaliacao)
VALUES (?, ?, ?, ?, ?, ?)
";
$stmt2 = $conn->prepare($sqlIns);
if (!$stmt2) { echo "<p>Erro ao registar nota.</p>"; exit(); }

$stmt2->bind_param("iiisds", $idDocente, $idEstudante, $idDisciplina, $tipoAvaliacao, $nota, $dataAvaliacao);
$stmt2->execute();
$stmt2->close();

/**
 * Log
 */
$data_hora = date('Y-m-d H:i:s');
$descricao = "Nota registada (Docente:$idDocente, Estudante:$idEstudante, Disciplina:$idDisciplina)";
$stmtLog = $conn->prepare("INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Nota')");
if ($stmtLog) {
    $stmtLog->bind_param("iss", $idDocente, $data_hora, $descricao);
    $stmtLog->execute();
    $stmtLog->close();
}

header('Location: ../pages/docente/dashboard_docente.php');
exit();
