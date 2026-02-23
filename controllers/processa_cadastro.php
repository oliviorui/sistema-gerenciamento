<?php
require_once '../config/bootstrap.php';

start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/auth/cadastro.php");
    exit();
}

$nome = trim((string)($_POST['nome'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$senha = (string)($_POST['senha'] ?? '');

if ($nome === '' || $email === '' || $senha === '') {
    echo "<p>Preencha nome, e-mail e senha.</p>";
    exit();
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

// Verifica se email já existe
$sqlCheck = "SELECT id_usuario FROM usuarios WHERE email = ? LIMIT 1";
$stmtCheck = $conn->prepare($sqlCheck);
if (!$stmtCheck) {
    echo "<p>Erro ao preparar validação.</p>";
    exit();
}
$stmtCheck->bind_param("s", $email);
$stmtCheck->execute();
$resCheck = $stmtCheck->get_result();
$stmtCheck->close();

if ($resCheck && $resCheck->num_rows > 0) {
    echo "<p>Este e-mail já está registrado.</p>";
    exit();
}

// Insere usuário como estudante
$tipo = 'estudante';

$sql = "INSERT INTO usuarios (nome, email, senha, tipo, data_cadastro) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "<p>Erro ao preparar cadastro.</p>";
    exit();
}

$stmt->bind_param("ssss", $nome, $email, $senhaHash, $tipo);
$stmt->execute();
$idNovo = $stmt->insert_id;
$stmt->close();

// LOG
$data_hora = date('Y-m-d H:i:s');
$descricao = "Cadastro realizado";

$sqlLog = "INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Cadastro')";
$stmtLog = $conn->prepare($sqlLog);
if ($stmtLog) {
    $stmtLog->bind_param("iss", $idNovo, $data_hora, $descricao);
    $stmtLog->execute();
    $stmtLog->close();
}

header("Location: ../pages/auth/login.php");
exit();
