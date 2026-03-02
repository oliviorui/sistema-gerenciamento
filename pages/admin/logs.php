<?php
require_once 'protecao_admin.php';

$res = mysqli_query($conn, "
  SELECT l.id_log, l.data_hora, l.tipo_actividade, l.descricao, u.nome AS usuario, u.email
  FROM logs_atividades l
  LEFT JOIN usuarios u ON u.id_usuario = l.id_usuario
  ORDER BY l.data_hora DESC
  LIMIT 300
");
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
      <a href="dashboard_admin.php">Dashboard</a>
      <a href="usuarios.php">Utilizadores</a>
      <a href="turmas.php">Turmas</a>
      <a href="disciplinas.php">Disciplinas</a>
      <a href="atribuicoes.php">Atribuições</a>
      <a class="active" href="logs.php">Logs</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Consultar Logs</h1>

    <table class="table">
      <thead>
        <tr>
          <th>Data/Hora</th>
          <th>Tipo</th>
          <th>Utilizador</th>
          <th>Descrição</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($res): ?>
        <?php while ($l = mysqli_fetch_assoc($res)): ?>
          <tr>
            <td><?= htmlspecialchars($l['data_hora'] ?? '') ?></td>
            <td><?= htmlspecialchars($l['tipo_actividade'] ?? '') ?></td>
            <td>
              <?= htmlspecialchars($l['usuario'] ?? '—') ?><br>
              <small><?= htmlspecialchars($l['email'] ?? '') ?></small>
            </td>
            <td><?= htmlspecialchars($l['descricao'] ?? '') ?></td>
          </tr>
        <?php endwhile; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </main>
</div>
</body>
</html>
