<?php
require_once '../../config/bootstrap.php';
require_login($conn);

$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');
if ($tipo !== 'estudante') {
  header("Location: ../docente/dashboard_docente.php");
  exit();
}

$idEstudante = (int)($_SESSION['usuario_id'] ?? 0);
$nome = (string)($_SESSION['usuario_nome'] ?? 'Estudante');

$idTurma = buscar_turma_do_estudante($conn, $idEstudante);
if ($idTurma === null) {
  echo "<p>Você não está associado(a) a nenhuma turma. Contacte o administrador.</p>";
  exit();
}

/**
 * Notas do estudante (somente dele)
 */
$sqlNotas = "
SELECT
  n.id_nota,
  d.nome AS disciplina,
  n.tipo_avaliacao,
  n.nota,
  COALESCE(DATE_FORMAT(n.data_avaliacao, '%Y-%m-%d'), '') AS data_avaliacao,
  n.data_registo
FROM notas n
INNER JOIN disciplinas d ON d.id_disciplina = n.id_disciplina
WHERE n.id_estudante = ?
ORDER BY d.nome ASC, n.data_registo DESC
";
$stmt = $conn->prepare($sqlNotas);
$stmt->bind_param("i", $idEstudante);
$stmt->execute();
$resNotas = $stmt->get_result();
$stmt->close();

/**
 * Médias por disciplina (somente dele)
 */
$sqlMedias = "
SELECT
  d.nome AS disciplina,
  ROUND(AVG(n.nota), 2) AS media
FROM notas n
INNER JOIN disciplinas d ON d.id_disciplina = n.id_disciplina
WHERE n.id_estudante = ?
GROUP BY d.id_disciplina
ORDER BY d.nome ASC
";
$stmt2 = $conn->prepare($sqlMedias);
$stmt2->bind_param("i", $idEstudante);
$stmt2->execute();
$resMedias = $stmt2->get_result();
$medias = [];
$mediaGeral = null;

$soma = 0.0;
$qtd = 0;
while ($row = $resMedias->fetch_assoc()) {
  $medias[] = $row;
  $soma += (float)$row['media'];
  $qtd += 1;
}
$stmt2->close();

if ($qtd > 0) $mediaGeral = round($soma / $qtd, 2);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Desempenho</title>
  <link rel="stylesheet" href="../../css/app.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      <a href="entregas.php">Entregas</a>
      <a class="active" href="desempenho.php">Desempenho</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Desempenho — <?= htmlspecialchars($nome) ?></h1>

    <section class="card">
      <h2>Resumo</h2>
      <p><strong>Turma:</strong> <?= (int)$idTurma ?></p>
      <p><strong>Média Geral:</strong> <?= ($mediaGeral !== null) ? htmlspecialchars((string)$mediaGeral) : '—' ?></p>
      <p><small>A média geral aqui é a média das médias por disciplina.</small></p>
    </section>

    <section class="card">
      <h2>Gráfico de Médias por Disciplina</h2>
      <canvas id="graficoMedias" height="110"></canvas>
      <p id="msgGrafico" style="margin-top:10px;"></p>
    </section>

    <section class="card">
      <h2>Médias por Disciplina</h2>
      <?php if (count($medias) === 0): ?>
        <p>Não existem notas registadas ainda.</p>
      <?php else: ?>
        <table class="table">
          <thead>
            <tr>
              <th>Disciplina</th>
              <th>Média</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($medias as $m): ?>
              <tr>
                <td><?= htmlspecialchars($m['disciplina'] ?? '') ?></td>
                <td><?= htmlspecialchars((string)($m['media'] ?? '0')) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </section>

    <section class="card">
      <h2>Histórico de Notas</h2>
      <table class="table">
        <thead>
          <tr>
            <th>Disciplina</th>
            <th>Avaliação</th>
            <th>Nota</th>
            <th>Data</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $temNotas = false;
          while ($n = $resNotas->fetch_assoc()):
            $temNotas = true;
          ?>
            <tr>
              <td><?= htmlspecialchars($n['disciplina'] ?? '') ?></td>
              <td><?= htmlspecialchars($n['tipo_avaliacao'] ?? '') ?></td>
              <td><?= htmlspecialchars((string)($n['nota'] ?? '')) ?></td>
              <td><?= htmlspecialchars((string)($n['data_avaliacao'] ?? '')) ?></td>
            </tr>
          <?php endwhile; ?>
          <?php if (!$temNotas): ?>
            <tr><td colspan="4">Não existem notas registadas.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </section>

  </main>
</div>

<script>
(async function () {
  const ctx = document.getElementById('graficoMedias');
  const msg = document.getElementById('msgGrafico');

  try {
    const res = await fetch('../../controllers/estudante_notas_json.php', { credentials: 'same-origin' });
    if (!res.ok) throw new Error('Falha ao carregar dados.');

    const data = await res.json();

    if (!data || !Array.isArray(data.labels) || data.labels.length === 0) {
      msg.textContent = 'Sem dados para exibir no gráfico.';
      return;
    }

    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.labels,
        datasets: [{
          label: 'Média',
          data: data.values
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            max: 20
          }
        }
      }
    });
  } catch (e) {
    msg.textContent = 'Não foi possível carregar o gráfico.';
  }
})();
</script>

</body>
</html>
