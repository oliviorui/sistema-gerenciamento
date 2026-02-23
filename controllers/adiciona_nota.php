<?php
require_once '../config/bootstrap.php';

require_funcionario_or_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/logged/dashboard.php');
    exit();
}

csrf_verify_or_exit();

$id_usuario_alvo = (int)($_POST['id_usuario_alvo'] ?? 0); // aluno
$id_disciplina = (int)($_POST['disciplina'] ?? 0);
$notaRaw = (string)($_POST['nota'] ?? '');
$tipo_avaliacao = trim((string)($_POST['tipo_avaliacao'] ?? ''));
$data_avaliacao = trim((string)($_POST['data_avaliacao'] ?? ''));

if ($id_usuario_alvo <= 0 || $id_disciplina <= 0 || $notaRaw === '' || $tipo_avaliacao === '') {
    echo "<p>Preencha todos os campos obrigatórios.</p>";
    exit();
}

$nota = (float)$notaRaw;
if ($nota < 0 || $nota > 20) {
    echo "<p>Nota inválida. Use um valor entre 0 e 20.</p>";
    exit();
}

if ($data_avaliacao === '') {
    $data_avaliacao = date('Y-m-d');
}

// Verificar se aluno existe e é estudante
$sqlAluno = "SELECT nome, tipo FROM usuarios WHERE id_usuario = ? LIMIT 1";
$stmtAluno = $conn->prepare($sqlAluno);

$nomeAluno = null;
$tipoAluno = null;

if ($stmtAluno) {
    $stmtAluno->bind_param("i", $id_usuario_alvo);
    $stmtAluno->execute();
    $resAluno = $stmtAluno->get_result();
    if ($resAluno && $resAluno->num_rows === 1) {
        $row = $resAluno->fetch_assoc();
        if (is_array($row)) {
            $nomeAluno = (string)($row['nome'] ?? '');
            $tipoAluno = (string)($row['tipo'] ?? '');
        }
    }
    $stmtAluno->close();
}

if ($nomeAluno === null || $tipoAluno !== 'estudante') {
    echo "<p>Selecione um estudante válido.</p>";
    exit();
}

// Nome da disciplina
$sqlDisc = "SELECT nome FROM disciplinas WHERE id_disciplina = ? LIMIT 1";
$stmtDisc = $conn->prepare($sqlDisc);

$nomeDisciplina = null;

if ($stmtDisc) {
    $stmtDisc->bind_param("i", $id_disciplina);
    $stmtDisc->execute();
    $resDisc = $stmtDisc->get_result();
    if ($resDisc && $resDisc->num_rows === 1) {
        $row = $resDisc->fetch_assoc();
        if (is_array($row) && isset($row['nome'])) {
            $nomeDisciplina = (string)$row['nome'];
        }
    }
    $stmtDisc->close();
}

if ($nomeDisciplina === null) {
    echo "<p>Disciplina inválida.</p>";
    exit();
}

// Inserir nota
$sqlIns = "INSERT INTO notas (id_usuario, id_disciplina, nota, data_avaliacao, tipo_avaliacao) VALUES (?, ?, ?, ?, ?)";
$stmtIns = $conn->prepare($sqlIns);

if (!$stmtIns) {
    echo "<p>Erro ao preparar inserção.</p>";
    exit();
}

$stmtIns->bind_param("iidss", $id_usuario_alvo, $id_disciplina, $nota, $data_avaliacao, $tipo_avaliacao);
$stmtIns->execute();
$stmtIns->close();

// Log (quem registrou)
$id_quem_registrou = (int)($_SESSION['usuario_id'] ?? 0);
$data_hora = date('Y-m-d H:i:s');
$descricao = "Lançou nota para $nomeAluno em $nomeDisciplina";

$sqlLog = "INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Registro')";
$stmtLog = $conn->prepare($sqlLog);

if ($stmtLog) {
    $stmtLog->bind_param("iss", $id_quem_registrou, $data_hora, $descricao);
    $stmtLog->execute();
    $stmtLog->close();
}

header('Location: ../pages/funcionario/dashboard_funcionario.php');
exit();
