<?php
require_once 'protecao_admin.php';

/** Docentes */
$docentes = [];
$r1 = mysqli_query($conn, "SELECT id_usuario, nome, email FROM usuarios WHERE tipo='docente' ORDER BY nome ASC");
if ($r1) while ($row = mysqli_fetch_assoc($r1)) $docentes[] = $row;

/** Turmas */
$turmas = [];
$r2 = mysqli_query($conn, "SELECT id_turma, nome FROM turmas ORDER BY nome ASC");
if ($r2) while ($row = mysqli_fetch_assoc($r2)) $turmas[] = $row;

/** Disciplinas */
$disciplinas = [];
$r3 = mysqli_query($conn, "SELECT id_disciplina, nome FROM disciplinas ORDER BY nome ASC");
if ($r3) while ($row = mysqli_fetch_assoc($r3)) $disciplinas[] = $row;

/** Atribuições existentes */
$sql = "
SELECT
  a.id_atribuicao,
  u.nome AS docente,
  u.email AS email_docente,
  t.nome AS turma,
  d.nome AS disciplina,
  a.data_atribuicao
FROM atribuicoes a
INNER JOIN usuarios u ON u.id_usuario = a.id_docente
INNER JOIN turmas t ON t.id_turma = a.id_turma
INNER JOIN disciplinas d ON d.id_disciplina = a.id_disciplina
ORDER BY a.data_atribuicao DESC
";
$resAtr = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Atribuições</title>
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
      <a class="active" href="atribuicoes.php">Atribuições</a>
      <a href="logs.php">Logs</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Gerenciar Atribuições</h1>

    <section class="card">
      <h2>Criar Atribuição (Docente — Turma — Disciplina)</h2>

      <?php if (count($docentes) === 0 || count($turmas) === 0 || count($disciplinas) === 0): ?>
        <p>Para criar atribuições, é necessário existir pelo menos 1 docente, 1 turma e 1 disciplina.</p>
      <?php else: ?>
        <form method="POST" action="../../controllers/admin_criar_atribuicao.php">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">

          <label>Docente</label>
          <select name="id_docente" required>
            <option value="">Selecione</option>
            <?php foreach ($docentes as $d): ?>
              <option value="<?= (int)$d['id_usuario'] ?>">
                <?= htmlspecialchars($d['nome']) ?> (<?= htmlspecialchars($d['email']) ?>)
              </option>
            <?php endforeach; ?>
          </select>

          <label>Turma</label>
          <select name="id_turma" required>
            <option value="">Selecione</option>
            <?php foreach ($turmas as $t): ?>
              <option value="<?= (int)$t['id_turma'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
            <?php endforeach; ?>
          </select>

          <label>Disciplina</label>
          <select name="id_disciplina" required>
            <option value="">Selecione</option>
            <?php foreach ($disciplinas as $x): ?>
              <option value="<?= (int)$x['id_disciplina'] ?>"><?= htmlspecialchars($x['nome']) ?></option>
            <?php endforeach; ?>
          </select>

          <button type="submit">Atribuir</button>
        </form>
      <?php endif; ?>
    </section>

    <section class="card">
      <h2>Atribuições Existentes</h2>
      <table class="table">
        <thead>
          <tr>
            <th>Docente</th>
            <th>Turma</th>
            <th>Disciplina</th>
            <th>Data</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($resAtr): ?>
          <?php while ($a = mysqli_fetch_assoc($resAtr)): ?>
            <tr>
              <td><?= htmlspecialchars($a['docente'] ?? '') ?><br><small><?= htmlspecialchars($a['email_docente'] ?? '') ?></small></td>
              <td><?= htmlspecialchars($a['turma'] ?? '') ?></td>
              <td><?= htmlspecialchars($a['disciplina'] ?? '') ?></td>
              <td><?= htmlspecialchars($a['data_atribuicao'] ?? '') ?></td>
              <td>
                <form class="inline-form" method="POST" action="../../controllers/admin_eliminar_atribuicao.php"
                      onsubmit="return confirm('Eliminar atribuição?');">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">
                  <input type="hidden" name="id_atribuicao" value="<?= (int)$a['id_atribuicao'] ?>">
                  <button class="danger" type="submit">Eliminar</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </section>
  </main>
</div>
</body>
</html>
