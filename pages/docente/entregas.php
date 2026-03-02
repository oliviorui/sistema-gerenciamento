<?php
require_once 'protecao_docente.php';

$idDocente = (int)($_SESSION['usuario_id'] ?? 0);

/**
 * Só submissões das atribuições do docente
 */
$sql = "
SELECT
  e.id_entrega,
  e.status,
  e.data_entrega,
  e.comentario,
  e.arquivo_nome_original,
  e.nota,
  e.feedback,

  atv.titulo AS atividade,
  atv.data_limite,

  t.nome AS turma,
  d.nome AS disciplina,

  u.nome AS estudante,
  u.email AS email_estudante

FROM entregas e
INNER JOIN atividades atv ON atv.id_atividade = e.id_atividade
INNER JOIN atribuicoes a ON a.id_atribuicao = atv.id_atribuicao
INNER JOIN turmas t ON t.id_turma = a.id_turma
INNER JOIN disciplinas d ON d.id_disciplina = a.id_disciplina
INNER JOIN usuarios u ON u.id_usuario = e.id_estudante

WHERE a.id_docente = ?
ORDER BY e.data_entrega DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idDocente);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submissões</title>
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
      <a class="active" href="entregas.php">Submissões</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Gerenciar Submissões (apenas das tuas atribuições)</h1>

    <table class="table">
      <thead>
        <tr>
          <th>Turma</th>
          <th>Disciplina</th>
          <th>Actividade</th>
          <th>Estudante</th>
          <th>Ficheiro</th>
          <th>Data</th>
          <th>Status</th>
          <th>Avaliar</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($e = $res->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($e['turma'] ?? '') ?></td>
          <td><?= htmlspecialchars($e['disciplina'] ?? '') ?></td>
          <td>
            <strong><?= htmlspecialchars($e['atividade'] ?? '') ?></strong><br>
            <small>Limite: <?= htmlspecialchars((string)($e['data_limite'] ?? '')) ?></small>
          </td>
          <td>
            <?= htmlspecialchars($e['estudante'] ?? '') ?><br>
            <small><?= htmlspecialchars($e['email_estudante'] ?? '') ?></small>
          </td>
          <td>
            <a href="../../controllers/download_entrega.php?id_entrega=<?= (int)$e['id_entrega'] ?>">
              <?= htmlspecialchars($e['arquivo_nome_original'] ?? 'Download') ?>
            </a>
          </td>
          <td><?= htmlspecialchars($e['data_entrega'] ?? '') ?></td>
          <td><?= htmlspecialchars($e['status'] ?? '') ?></td>
          <td>
            <form action="../../controllers/avaliar_entrega.php" method="POST" class="inline-form">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">
              <input type="hidden" name="id_entrega" value="<?= (int)$e['id_entrega'] ?>">

              <input type="number" name="nota" min="0" max="20" step="0.1"
                     value="<?= htmlspecialchars((string)($e['nota'] ?? '')) ?>" required>

              <input type="text" name="feedback" placeholder="Feedback"
                     value="<?= htmlspecialchars((string)($e['feedback'] ?? '')) ?>">

              <select name="status">
                <option value="Pendente" <?= (($e['status'] ?? '') === 'Pendente') ? 'selected' : '' ?>>Pendente</option>
                <option value="Avaliado" <?= (($e['status'] ?? '') === 'Avaliado') ? 'selected' : '' ?>>Avaliado</option>
              </select>

              <button type="submit">Salvar</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </main>
</div>

</body>
</html>
