<?php
require_once '../../config/bootstrap.php';
require_login($conn);

$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');
if ($tipo !== 'estudante') {
    header("Location: ../funcionario/entregas.php");
    exit();
}

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Estudante';
$id_estudante = (int)($_SESSION['usuario_id'] ?? 0);

$today = new DateTime('today');

$sql = "
SELECT
    a.id_atividade,
    d.nome AS disciplina,
    a.titulo,
    a.descricao,
    a.data_limite,
    e.id_entrega,
    e.status,
    e.data_entrega,
    e.feedback,
    e.nota,
    e.arquivo_nome_original
FROM atividades a
INNER JOIN disciplinas d ON d.id_disciplina = a.id_disciplina
LEFT JOIN entregas e
    ON e.id_atividade = a.id_atividade AND e.id_estudante = ?
ORDER BY a.criado_em DESC
";

$stmt = $conn->prepare($sql);
$atividades = [];
if ($stmt) {
    $stmt->bind_param("i", $id_estudante);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $atividades[] = $row;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entregas - Estudante</title>
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
            <a href="dashboard.php">Página Inicial</a>
            <a href="entregas.php">Entregas</a>
            <a href="logs.php">Atividades no sistema</a>
        </nav>

        <div class="divider"></div>

        <form action="../../controllers/logout.php" method="POST">
            <?= csrf_field(); ?>
            <button class="btn btn-danger logout" type="submit">Sair</button>
        </form>
    </aside>

    <header class="topbar">
        <h1>Entregas</h1>
        <div class="actions">
            <span class="muted">Bem-vindo(a), <strong><?= htmlspecialchars((string)$usuario_nome, ENT_QUOTES, 'UTF-8'); ?></strong></span>
        </div>
    </header>

    <main class="main">
        <h2 class="page-title">Atividades disponíveis</h2>

        <?php if (count($atividades) === 0): ?>
            <p class="muted">Nenhuma atividade criada ainda.</p>
        <?php endif; ?>

        <?php foreach ($atividades as $a): ?>
            <?php
                $dataLimiteStr = (string)($a['data_limite'] ?? '');
                $deadline = null;
                if ($dataLimiteStr !== '') {
                    $deadline = DateTime::createFromFormat('Y-m-d', $dataLimiteStr) ?: null;
                }

                $hasEntrega = !empty($a['id_entrega']);
                $dataEntregaStr = (string)($a['data_entrega'] ?? '');

                $isLate = false;
                if ($deadline instanceof DateTime && !$hasEntrega) {
                    if ($today > $deadline) $isLate = true;
                }

                $submittedLate = false;
                if ($deadline instanceof DateTime && $dataEntregaStr !== '') {
                    $entregaDT = new DateTime($dataEntregaStr);
                    $deadlineEnd = (clone $deadline)->setTime(23, 59, 59);
                    if ($entregaDT > $deadlineEnd) $submittedLate = true;
                }
            ?>

            <div class="card" style="margin-bottom: 12px;">
                <div class="card-body">
                    <div style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                        <div>
                            <div class="muted"><?= htmlspecialchars((string)$a['disciplina'], ENT_QUOTES, 'UTF-8'); ?></div>

                            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                                <h3 style="margin: 4px 0;"><?= htmlspecialchars((string)$a['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>

                                <?php if ($isLate): ?>
                                    <span style="padding:4px 10px; border-radius:999px; background:#ff3b30; color:#fff; font-weight:700;">
                                        ATRASADO
                                    </span>
                                <?php endif; ?>

                                <?php if ($submittedLate): ?>
                                    <span style="padding:4px 10px; border-radius:999px; background:#ff9500; color:#111; font-weight:700;">
                                        ENTREGUE FORA DO PRAZO
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="muted"><?= htmlspecialchars((string)($a['descricao'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></div>
                            <div style="margin-top:8px;">
                                <strong>Data limite:</strong>
                                <?= htmlspecialchars((string)($a['data_limite'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        </div>

                        <div>
                            <div><strong>Status:</strong> <?= htmlspecialchars((string)($a['status'] ?? 'Não submetido'), ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php if (!empty($a['data_entrega'])): ?>
                                <div class="muted">Entregue em: <?= htmlspecialchars((string)$a['data_entrega'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($a['nota'])): ?>
                                <div><strong>Nota:</strong> <?= htmlspecialchars((string)$a['nota'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($a['feedback'])): ?>
                        <div class="card" style="margin-top: 10px;">
                            <div class="card-body">
                                <strong>Feedback:</strong>
                                <div class="muted"><?= htmlspecialchars((string)$a['feedback'], ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <hr style="opacity:.15; margin: 12px 0;">

                    <?php if (!empty($a['id_entrega']) && !empty($a['arquivo_nome_original'])): ?>
                        <div style="margin-bottom: 10px;">
                            <a class="btn btn-ghost" href="../../controllers/download_entrega.php?id_entrega=<?= (int)$a['id_entrega']; ?>">
                                Baixar arquivo
                            </a>
                        </div>
                    <?php endif; ?>

                    <form action="../../controllers/submeter_entrega.php" method="POST" enctype="multipart/form-data">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="id_atividade" value="<?= (int)$a['id_atividade']; ?>">

                        <div class="field">
                            <label>Comentário (opcional)</label>
                            <input type="text" name="comentario" placeholder="Ex: segue o pdf do trabalho">
                        </div>

                        <div class="field">
                            <label>Arquivo (opcional, máx 10MB)</label>
                            <input type="file" name="arquivo" accept=".pdf,.doc,.docx,.zip,.jpg,.jpeg,.png">
                            <?php if (!empty($a['arquivo_nome_original'])): ?>
                                <div class="muted" style="margin-top:6px;">
                                    Último arquivo enviado: <?= htmlspecialchars((string)$a['arquivo_nome_original'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <button class="btn btn-success" type="submit">
                            <?= !empty($a['id_entrega']) ? 'Atualizar entrega' : 'Submeter entrega'; ?>
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </main>
</div>
</body>
</html>
