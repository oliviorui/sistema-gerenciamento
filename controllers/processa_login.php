<?php
require_once '../config/bootstrap.php';

start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pages/auth/login.php');
    exit();
}

$email = trim((string)($_POST['email'] ?? ''));
$senha = (string)($_POST['senha'] ?? '');

if ($email === '' || $senha === '') {
    echo "<p>Preencha e-mail e senha.</p>";
    exit();
}

$sql = "SELECT id_usuario, nome, email, senha, tipo FROM usuarios WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo "<p>Erro ao preparar consulta.</p>";
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows !== 1) {
    echo "<p>E-mail não encontrado.</p>";
    $stmt->close();
    exit();
}

$usuario = $res->fetch_assoc();
$stmt->close();

if (!is_array($usuario) || !password_verify($senha, (string)$usuario['senha'])) {
    echo "<p>Senha incorreta.</p>";
    exit();
}

// Compatibilidade: se ainda existir 'usuario', converte na sessão para estudante
if (($usuario['tipo'] ?? '') === 'usuario') {
    $usuario['tipo'] = 'estudante';
}

login_set_session($usuario);

// Remember token (30 dias)
$rawToken = bin2hex(random_bytes(32));
$tokenHash = hash('sha256', $rawToken);
$expiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60));

$sqlToken = "INSERT INTO user_tokens (id_usuario, token_hash, expires_at) VALUES (?, ?, ?)";
$stmtTok = $conn->prepare($sqlToken);

if ($stmtTok) {
    $idUsuario = (int)$usuario['id_usuario'];
    $stmtTok->bind_param("iss", $idUsuario, $tokenHash, $expiresAt);
    $stmtTok->execute();
    $stmtTok->close();

    remember_cookie_set($rawToken, 30);
}

// Log de login
$data_hora = date('Y-m-d H:i:s');
$descricao = "Login realizado";

$sqlLog = "INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Login')";
$stmtLog = $conn->prepare($sqlLog);

if ($stmtLog) {
    $idUsuario = (int)$usuario['id_usuario'];
    $stmtLog->bind_param("iss", $idUsuario, $data_hora, $descricao);
    $stmtLog->execute();
    $stmtLog->close();
}

$tipo = (string)($usuario['tipo'] ?? 'estudante');

if ($tipo === 'admin') {
    header('Location: ../pages/admin/dashboard_admin.php');
    exit();
}

if ($tipo === 'funcionario') {
    header('Location: ../pages/funcionario/dashboard_funcionario.php');
    exit();
}

header('Location: ../pages/logged/dashboard.php');
exit();
