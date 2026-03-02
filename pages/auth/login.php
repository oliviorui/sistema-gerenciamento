<?php
require_once '../../config/bootstrap.php';

start_secure_session();

// Se já estiver autenticado, redireciona para o painel correcto
if (!empty($_SESSION['usuario_id'])) {
    $tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');

    if ($tipo === 'admin') {
        header('Location: ../admin/dashboard_admin.php');
        exit();
    }

    if ($tipo === 'docente') {
        header('Location: ../docente/dashboard_docente.php');
        exit();
    }

    header('Location: ../estudante/dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Acadêmico - Login</title>

    <!-- USA O CSS CORRETO -->
    <link rel="stylesheet" href="../../css/login.css">
</head>
<body>

<div class="container">
    <div class="login-box">

        <img src="../../img/logo.png" alt="Logo do Sistema" class="logo">

        <h1>Entrar no Sistema</h1>

        <form action="../../controllers/processa_login.php" method="POST">
            <label for="email">E-mail</label>
            <input type="email" name="email" id="email" required>

            <label for="senha">Senha</label>
            <input type="password" name="senha" id="senha" required>

            <button type="submit">Entrar</button>
        </form>

        <p>
            Ainda não tem conta?
            <a href="cadastro.php">Registar estudante</a>
        </p>

    </div>
</div>

</body>
</html>
