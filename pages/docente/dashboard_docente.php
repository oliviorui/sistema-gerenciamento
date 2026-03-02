<?php
require_once __DIR__ . '/protecao_docente.php';

$idDocente = (int)($_SESSION['usuario_id'] ?? 0);
$nomeDocente = (string)($_SESSION['usuario_nome'] ?? 'Docente');

/**
 * Atribuições do docente: (turma, disciplina)
 */
$sqlAtr = "
SELECT
  a.id_atribuicao,
  t.id_turma,
  t.nome AS turma,
  d.id_disciplina,
  d.nome AS disciplina
FROM atribuicoes a
INNER JOIN turmas t ON t.id_turma = a.id_turma
INNER JOIN disciplinas d ON d.id_disciplina = a.id_disciplina
WHERE a.id_docente = ?
ORDER BY t.nome ASC, d.nome ASC
";
$stmt = $conn->prepare($sqlAtr);
$stmt->bind_param("i", $idDocente);
$stmt->execute();
$resAtr = $stmt->get_result();
$atribuicoes = [];
while ($row = $resAtr->fetch_assoc()) $atribuicoes[] = $row;
$stmt->close();

/**
 * Estudantes apenas das turmas atribuídas ao docente
 */
$sqlEst = "
SELECT u.id_usuario, u.nome, u.email, u.id_turma, t.nome AS turma
FROM usuarios u
INNER JOIN turmas t ON t.id_turma = u.id_turma
WHERE u.tipo = 'estudante'
  AND u.id_turma IN (SELECT id_turma FROM atribuicoes WHERE id_docente = ?)
ORDER BY t.nome ASC, u.nome ASC
";
$stmt2 = $conn->prepare($sqlEst);
$stmt2->bind_param("i", $idDocente);
$stmt2->execute();
$resEst = $stmt2->get_result();
$estudantes = [];
while ($row = $resEst->fetch_assoc()) $estudantes[] = $row;
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Docente</title>
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
      <a class="active" href="dashboard_docente.php">Painel Docente</a>
      <a href="atividades.php">Actividades</a>
      <a href="entregas.php">Submissões</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Bem-vindo, <?= htmlspecialchars($nomeDocente) ?></h1>

    <section class="card">
      <h2>Minhas Atribuições</h2>
      <?php if (count($atribuicoes) === 0): ?>
        <p>Não tens atribuições registadas.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($atribuicoes as $a): ?>
            <li><?= htmlspecialchars($a['turma']) ?> — <?= htmlspecialchars($a['disciplina']) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section class="card">
      <h2>Registar Nota (apenas nas tuas atribuições)</h2>

      <form action="../../controllers/adiciona_nota.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">

        <label>Atribuição (Turma — Disciplina)</label>
        <select name="id_atribuicao" required>
          <option value="">Selecione</option>
          <?php foreach ($atribuicoes as $a): ?>
            <option value="<?= (int)$a['id_atribuicao'] ?>">
              <?= htmlspecialchars($a['turma']) ?> — <?= htmlspecialchars($a['disciplina']) ?>
            </option>
          <?php endforeach; ?>
        </select>

        <label>Estudante (apenas das tuas turmas)</label>
        <select name="id_estudante" required>
          <option value="">Selecione</option>
          <?php foreach ($estudantes as $e): ?>
            <option value="<?= (int)$e['id_usuario'] ?>">
              <?= htmlspecialchars($e['turma']) ?> — <?= htmlspecialchars($e['nome']) ?> (<?= htmlspecialchars($e['email']) ?>)
            </option>
          <?php endforeach; ?>
        </select>

        <label>Tipo de Avaliação</label>
        <input type="text" name="tipo_avaliacao" placeholder="Teste, Exame, Trabalho..." required>

        <label>Nota (0-20)</label>
        <input type="number" name="nota" min="0" max="20" step="0.1" required>

        <label>Data (opcional)</label>
        <input type="date" name="data_avaliacao">

        <button type="submit">Guardar Nota</button>
      </form>
    </section>
  </main>
</div>
</body>
</html>
