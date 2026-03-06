<?php
require_once __DIR__ . '/protecao_docente.php';

$id_entrega = (int)($_GET['id_entrega'] ?? 0);
if ($id_entrega <= 0) { echo "<p>Entrega inválida.</p>"; exit(); }

// Buscar dados da entrega (para mostrar info e carregar nota/feedback/status)
$sql = "
SELECT
  e.id_entrega, e.status, e.nota, e.feedback, e.arquivo_nome_original, e.arquivo_mime,
  u.nome AS estudante,
  atv.titulo AS atividade
FROM entregas e
INNER JOIN usuarios u ON u.id_usuario = e.id_estudante
INNER JOIN atividades atv ON atv.id_atividade = e.id_atividade
WHERE e.id_entrega = ?
LIMIT 1
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_entrega);
$stmt->execute();
$res = $stmt->get_result();
$e = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$e) { echo "<p>Entrega não encontrada.</p>"; exit(); }

$mime = (string)($e['arquivo_mime'] ?? '');
$canInline = ($mime === 'application/pdf' || str_starts_with($mime,'image/') || str_starts_with($mime,'text/'));
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ver Entrega</title>
  <link rel="stylesheet" href="../../css/app.css">
  <style>
    .viewer-wrap{display:grid;grid-template-columns:1.7fr 1fr;gap:16px;align-items:start}
    .viewer-box{background:#fff;border:1px solid #e6e6e6;border-radius:10px;padding:12px}
    .viewer-frame{width:100%;height:75vh;border:0;border-radius:8px}
  </style>
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
      <a class="active" href="entregas.php">Submissões</a>
      <a href="../../controllers/logout.php">Sair</a>
    </nav>
  </aside>

  <main class="content">
    <h1>Visualizar e Avaliar</h1>
    <p>
      <strong>Actividade:</strong> <?= htmlspecialchars($e['atividade'] ?? '') ?> |
      <strong>Estudante:</strong> <?= htmlspecialchars($e['estudante'] ?? '') ?> |
      <strong>Ficheiro:</strong> <?= htmlspecialchars($e['arquivo_nome_original'] ?? '') ?>
    </p>

    <div class="viewer-wrap">
      <div class="viewer-box">
        <h3>Pré-visualização</h3>

        <?php if ($canInline): ?>
          <iframe class="viewer-frame"
                  src="../../controllers/view_entrega.php?id_entrega=<?= (int)$id_entrega ?>"></iframe>
        <?php else: ?>
          <p>Este tipo de ficheiro não suporta pré-visualização no navegador.</p>
          <a class="btn" href="../../controllers/download_entrega.php?id_entrega=<?= (int)$id_entrega ?>">
            Baixar ficheiro
          </a>
        <?php endif; ?>

        <div style="margin-top:10px">
          <a href="../../controllers/download_entrega.php?id_entrega=<?= (int)$id_entrega ?>">Baixar</a>
        </div>
      </div>

      <div class="viewer-box">
        <h3>Avaliação</h3>
        <form action="../../controllers/avaliar_entrega.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_get_token()) ?>">
          <input type="hidden" name="id_entrega" value="<?= (int)$id_entrega ?>">

          <label>Nota (0 a 20)</label>
          <input type="number" name="nota" min="0" max="20" step="0.1"
                 value="<?= htmlspecialchars((string)($e['nota'] ?? '')) ?>" required>

          <label>Feedback</label>
          <input type="text" name="feedback"
                 value="<?= htmlspecialchars((string)($e['feedback'] ?? '')) ?>"
                 placeholder="Ex.: Faltou explicar melhor a introdução...">

          <label>Status</label>
          <select name="status">
            <option value="Pendente" <?= (($e['status'] ?? '') === 'Pendente') ? 'selected' : '' ?>>Pendente</option>
            <option value="Avaliado" <?= (($e['status'] ?? '') === 'Avaliado') ? 'selected' : '' ?>>Avaliado</option>
          </select>

          <button type="submit">Salvar avaliação</button>
        </form>
      </div>
    </div>
  </main>
</div>
</body>
</html>
