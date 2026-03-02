<?php
require_once '../../config/bootstrap.php';
require_login($conn);

$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');
if ($tipo !== 'estudante') {
    header("Location: ../docente/entregas.php");
    exit();
}

$idEstudante = (int)($_SESSION['usuario_id'] ?? 0);
$idTurma = buscar_turma_do_estudante($conn, $idEstudante);

if ($idTurma === null) {
    echo "<p>O teu utilizador não está associado a nenhuma turma. Contacta o administrador.</p>";
    exit();
}

/**
 * Actividades apenas da turma do estudante
 * (via atribuicoes -> atividades)
 */
$sql = "
SELECT
  atv.id_atividade,
  atv.titulo,
  atv.descricao,
  atv.data_limite,
  t.nome AS turma,
  d.nome AS disciplina,

  e.id_entrega,
  e.status,
  e.data_entrega,
  e.nota,
  e.feedback,
  e.arquivo_nome_original
FROM atividades atv
INNER JOIN atribuicoes a ON a.id_atribuicao = atv.id_atribuicao
INNER JOIN turmas t ON t.id_turma = a.id_turma
INNER JOIN disciplinas d ON d.id_disciplina = a.id_disciplina
LEFT JOIN entregas e ON e.id_atividade = atv.id_atividade AND e.id_estudante = ?
WHERE a.id_turma = ?
ORDER BY atv.data_criacao DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idEstudante, $idTurma);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entregas</title>
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
      <a href="dashboard.php">Dashboard</a>
      <a class="active" href="entregas.php">Entregas</a>
      <a href="desempenho.php">Desempenho</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Submeter Actividades</h1>

    <table class="table">
      <thead>
        <tr>
          <th>Disciplina</th>
          <th>Actividade</th>
          <th>Data limite</th>
          <th>Estado</th>
          <th>Submeter</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['disciplina']) ?></td>
          <td>
            <strong><?= htmlspecialchars($row['titulo']) ?></strong><br>
            <small><?= htmlspecialchars((string)($row['descricao'] ?? '')) ?></small>
          </td>
          <td><?= htmlspecialchars((string)($row['data_limite'] ?? '')) ?></td>
          <td>
            <?= htmlspecialchars((string)($row['status'] ?? 'Não submetido')) ?>
            <?php if ($row['nota'] !== null): ?>
              <br><small>Nota: <?= htmlspecialchars((string)$row['nota']) ?></small>
            <?php endif; ?>
          </td>
          <td>
            <form action="../../controllers/submeter_entrega.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">
              <input type="hidden" name="id_atividade" value="<?= (int)$row['id_atividade'] ?>">

              <input type="file" name="arquivo" required>
              <input type="text" name="comentario" placeholder="Comentário (opcional)">

              <button type="submit">Submeter</button>

              <?php if (!empty($row['id_entrega'])): ?>
                <br>
                <a href="../../controllers/download_entrega.php?id_entrega=<?= (int)$row['id_entrega'] ?>">
                  Baixar ficheiro
                </a>
              <?php endif; ?>
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
