<?php
require_once '../../config/bootstrap.php';
require_login($conn);

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs</title>
    <link rel="stylesheet" href="../../css/app.css">
</head>
<body>

<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <img src="../../img/logo.png" alt="Logo">
            <strong>Sistema Acadêmico</strong>
        </div>

        <nav class="nav">
            <a href="dashboard_docente.php">Painel Docente</a>
            <a href="atividades.php">Actividades</a>
            <a href="entregas.php">Submissões</a>
            <a href="../../controllers/logout.php">Sair</a>
        </nav>
    </aside>

    <main class="content">
        <h1>Logs do Sistema</h1>
        <p>Esta página pode ser removida do módulo docente se quiseres restringir logs apenas ao Administrador.</p>
    </main>
</div>

</body>
</html>
