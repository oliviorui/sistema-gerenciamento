<?php
require_once 'protecao_funcionario.php';

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Funcionário';

// Lista entregas (todas) com data limite
$sql = "
SELECT
    e.id_entrega,
    e.status,
    e.data_entrega,
    e.comentario,
    e.arquivo_nome_original,
    e.nota,
    e.feedback,

    a.titulo AS atividade,
    a.data_limite,
    d.nome AS disciplina,
    u.nome AS estudante,
    u.email AS email_estudante
FROM entregas e
INNER JOIN atividades a ON a.id_atividade = e.id_atividade
INNER JOIN disciplinas d ON d.id_disciplina = a.id_disciplina
INNER JOIN usuarios u ON u.id_usuario = e.id_estudante
ORDER BY e.data_entrega DESC
";

$res = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entregas - Funcionário</title>
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
            <a href="dashboard_funcionario.php">Painel Funcionário</a>
            <a href="atividades.php">Atividades</a>
            <a href="entregas.php">Entregas</a>
            <a href="../logged/logs.php">Logs</a>
            <?php if (($_SESSION['usuario_tipo'] ?? '') === 'admin'): ?>
                <a href="../admin/dashboard_admin.php">Admin</a>
            <?php endif; ?>
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
        <h2 class="page-title">Entregas recebidas</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Estudante</th>
                    <th>Disciplina / Atividade</th>
                    <th>Data</th>
                    <th>Status</th>
                    <th>Avaliar</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($res && mysqli_num_rows($res) > 0): ?>
                <?php while ($e = mysqli_fetch_assoc($res)): ?>
                    <?php
                        $deadlineStr = (string)($e['data_limite'] ?? '');
                        $deliveredAtStr = (string)($e['data_entrega'] ?? '');
                        $lateBadge = false;

                        if ($deadlineStr !== '' && $deliveredAtStr !== '') {
                            $deadline = DateTime::createFromFormat('Y-m-d', $deadlineStr) ?: null;
                            if ($deadline instanceof DateTime) {
                                $deadlineEnd = (clone $deadline)->setTime(23, 59, 59);
                                $deliveredAt = new DateTime($deliveredAtStr);
                                if ($deliveredAt > $deadlineEnd) {
                                    $lateBadge = true;
                                }
                            }
                        }
                    ?>

                    <tr>
                        <td>
                            <strong><?= htmlspecialchars((string)$e['estudante'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                            <span class="muted"><?= htmlspecialchars((string)$e['email_estudante'], ENT_QUOTES, 'UTF-8'); ?></span>
                        </td>

                        <td>
                            <div class="muted"><?= htmlspecialchars((string)$e['disciplina'], ENT_QUOTES, 'UTF-8'); ?></div>

                            <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                                <strong><?= htmlspecialchars((string)$e['atividade'], ENT_QUOTES, 'UTF-8'); ?></strong>

                                <?php if ($lateBadge): ?>
                                    <span style="padding:4px 10px; border-radius:999px; background:#ff9500; color:#111; font-weight:700;">
                                        ENTREGUE FORA DO PRAZO
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="muted" style="margin-top:6px;">
                                Data limite: <?= htmlspecialchars(($deadlineStr !== '' ? $deadlineStr : '-'), ENT_QUOTES, 'UTF-8'); ?>
                            </div>

                            <?php if (!empty($e['comentario'])): ?>
                                <div class="muted" style="margin-top:6px;">
                                    Comentário: <?= htmlspecialchars((string)$e['comentario'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($e['arquivo_nome_original'])): ?>
                                <div style="margin-top:8px;">
                                    <a class="btn btn-ghost" href="../../controllers/download_entrega.php?id_entrega=<?= (int)$e['id_entrega']; ?>">
                                        Baixar arquivo
                                    </a>
                                    <div class="muted" style="margin-top:6px;">
                                        Arquivo: <?= htmlspecialchars((string)$e['arquivo_nome_original'], ENT_QUOTES, 'UTF-8'); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td><?= htmlspecialchars((string)$e['data_entrega'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><strong><?= htmlspecialchars((string)$e['status'], ENT_QUOTES, 'UTF-8'); ?></strong></td>

                        <td style="min-width: 320px;">
                            <form action="../../controllers/avaliar_entrega.php" method="POST">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="id_entrega" value="<?= (int)$e['id_entrega']; ?>">

                                <div class="field">
                                    <label>Status</label>
                                    <select name="status" required>
                                        <option value="Pendente" <?= $e['status']==='Pendente'?'selected':''; ?>>Pendente</option>
                                        <option value="Aprovado" <?= $e['status']==='Aprovado'?'selected':''; ?>>Aprovado</option>
                                        <option value="Rejeitado" <?= $e['status']==='Rejeitado'?'selected':''; ?>>Rejeitado</option>
                                    </select>
                                </div>

                                <div class="field">
                                    <label>Nota (0–20, opcional)</label>
                                    <input type="number" step="0.01" name="nota" value="<?= htmlspecialchars((string)($e['nota'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                </div>

                                <div class="field">
                                    <label>Feedback (opcional)</label>
                                    <input type="text" name="feedback" value="<?= htmlspecialchars((string)($e['feedback'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: bom trabalho, só melhorar a conclusão">
                                </div>

                                <button class="btn btn-success" type="submit">Salvar</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">Nenhuma entrega ainda.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>
