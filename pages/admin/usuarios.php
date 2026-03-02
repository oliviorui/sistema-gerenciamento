<?php
require_once 'protecao_admin.php';

/** turmas para dropdown */
$turmas = [];
$rt = mysqli_query($conn, "SELECT id_turma, nome FROM turmas ORDER BY nome ASC");
if ($rt) while ($row = mysqli_fetch_assoc($rt)) $turmas[] = $row;

/** usuários */
$sql = "
SELECT u.id_usuario, u.nome, u.email, u.tipo, u.id_turma, u.data_cadastro, t.nome AS turma
FROM usuarios u
LEFT JOIN turmas t ON t.id_turma = u.id_turma
ORDER BY u.nome ASC
";
$usuarios = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Utilizadores</title>
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
      <a class="active" href="usuarios.php">Utilizadores</a>
      <a href="turmas.php">Turmas</a>
      <a href="disciplinas.php">Disciplinas</a>
      <a href="atribuicoes.php">Atribuições</a>
      <a href="logs.php">Logs</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Gerenciar Utilizadores</h1>

    <section class="card">
      <h2>Lista de Utilizadores</h2>

      <table class="table">
        <thead>
          <tr>
            <th>Nome</th>
            <th>E-mail</th>
            <th>Tipo</th>
            <th>Turma (se estudante)</th>
            <th>Cadastrado</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($usuarios): ?>
          <?php while ($u = mysqli_fetch_assoc($usuarios)): ?>
            <tr>
              <td><?= htmlspecialchars($u['nome'] ?? '') ?></td>
              <td><?= htmlspecialchars($u['email'] ?? '') ?></td>

              <td>
                <form method="POST" action="../../controllers/admin_atualizar_usuario.php" class="inline-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">
                  <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">

                  <select name="tipo" required>
                    <option value="estudante" <?= ($u['tipo']==='estudante')?'selected':''; ?>>Estudante</option>
                    <option value="docente" <?= ($u['tipo']==='docente')?'selected':''; ?>>Docente</option>
                    <option value="admin" <?= ($u['tipo']==='admin')?'selected':''; ?>>Admin</option>
                  </select>
              </td>

              <td>
                  <select name="id_turma">
                    <option value="">—</option>
                    <?php foreach ($turmas as $t): ?>
                      <option value="<?= (int)$t['id_turma'] ?>" <?= ((int)($u['id_turma'] ?? 0) === (int)$t['id_turma']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nome']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
              </td>

              <td><?= htmlspecialchars($u['data_cadastro'] ?? '') ?></td>
              <td>
                  <button type="submit">Salvar</button>
                </form>

                <form method="POST" action="../../controllers/admin_excluir_usuario.php" class="inline-form"
                      onsubmit="return confirm('Excluir utilizador?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">
                  <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                  <button type="submit" class="danger">Excluir</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>

      <p><small>Regra: apenas estudantes devem ter turma. Docente/Admin ficam com turma vazia.</small></p>
    </section>
  </main>
</div>
</body>
</html>
