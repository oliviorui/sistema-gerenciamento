<?php
require_once __DIR__ . '/protecao_docente.php';

$idDocente = (int)($_SESSION['usuario_id'] ?? 0);

/**
 * Atribuições do docente
 */
$sqlAtr = "
SELECT
  a.id_atribuicao,
  t.nome AS turma,
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
 * Actividades apenas das atribuições do docente
 */
$sqlAt = "
SELECT
  atv.id_atividade,
  atv.titulo,
  atv.descricao,
  atv.data_limite,
  atv.data_criacao,
  t.nome AS turma,
  d.nome AS disciplina
FROM atividades atv
INNER JOIN atribuicoes a ON a.id_atribuicao = atv.id_atribuicao
INNER JOIN turmas t ON t.id_turma = a.id_turma
INNER JOIN disciplinas d ON d.id_disciplina = a.id_disciplina
WHERE a.id_docente = ?
ORDER BY atv.data_criacao DESC
";
$stmt2 = $conn->prepare($sqlAt);
$stmt2->bind_param("i", $idDocente);
$stmt2->execute();
$resAt = $stmt2->get_result();
$stmt2->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Actividades</title>
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
      <a class="active" href="atividades.php">Actividades</a>
      <a href="entregas.php">Submissões</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Gerenciar Actividades</h1>

    <section class="card">
      <h2>Criar Actividade (apenas nas tuas atribuições)</h2>

      <form action="../../controllers/criar_atividade.php" method="POST">
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

        <label>Título</label>
        <input type="text" name="titulo" required>

        <label>Descrição</label>
        <textarea name="descricao"></textarea>

        <label>Data limite</label>
        <input type="date" name="data_limite">

        <button type="submit">Criar</button>
      </form>
    </section>

    <section class="card">
      <h2>Minhas Actividades</h2>

      <table class="table">
        <thead>
          <tr>
            <th>Turma</th>
            <th>Disciplina</th>
            <th>Título</th>
            <th>Data limite</th>
            <th>Criada em</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($a = $resAt->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($a['turma']) ?></td>
              <td><?= htmlspecialchars($a['disciplina']) ?></td>
              <td>
                <strong><?= htmlspecialchars($a['titulo']) ?></strong><br>
                <small><?= htmlspecialchars((string)($a['descricao'] ?? '')) ?></small>
              </td>
              <td><?= htmlspecialchars((string)($a['data_limite'] ?? '')) ?></td>
              <td><?= htmlspecialchars($a['data_criacao']) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </section>
  </main>
</div>
</body>
</html>
