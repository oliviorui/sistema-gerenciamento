<?php
require_once 'protecao_admin.php';

$res = mysqli_query($conn, "SELECT id_turma, nome FROM turmas ORDER BY nome ASC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Turmas</title>
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
      <a class="active" href="turmas.php">Turmas</a>
      <a href="disciplinas.php">Disciplinas</a>
      <a href="atribuicoes.php">Atribuições</a>
      <a href="logs.php">Logs</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Gerenciar Turmas</h1>

    <section class="card">
      <h2>Criar Turma</h2>
      <form method="POST" action="../../controllers/admin_criar_turma.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">
        <label>Nome da turma</label>
        <input type="text" name="nome" placeholder="Ex: Turma A" required>
        <button type="submit">Criar</button>
      </form>
    </section>

    <section class="card">
      <h2>Turmas Registadas</h2>
      <table class="table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($res): ?>
          <?php while ($t = mysqli_fetch_assoc($res)): ?>
            <tr>
              <td><?= htmlspecialchars($t['nome'] ?? '') ?></td>
              <td>
                <form class="inline-form" method="POST" action="../../controllers/admin_eliminar_turma.php"
                      onsubmit="return confirm('Eliminar turma?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">
                  <input type="hidden" name="id_turma" value="<?= (int)$t['id_turma'] ?>">
                  <button class="danger" type="submit">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>
      <p><small>Nota: eliminar uma turma remove atribuições relacionadas e estudantes ficam sem turma (SET NULL).</small></p>
    </section>
  </main>
</div>
</body>
</html>
