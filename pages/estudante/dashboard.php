<?php
require_once '../../config/bootstrap.php';
require_login($conn);

$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');
if ($tipo !== 'estudante') {
  header("Location: ../docente/dashboard_docente.php");
  exit();
}

$nome = (string)($_SESSION['usuario_nome'] ?? 'Estudante');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Estudante</title>
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
      <a class="active" href="dashboard.php">Dashboard</a>
      <a href="entregas.php">Entregas</a>
      <a href="desempenho.php">Desempenho</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Bem-vindo, <?= htmlspecialchars($nome) ?></h1>
    <p>Use o menu para consultar desempenho e submeter actividades.</p>
  </main>
</div>
</body>
</html>
