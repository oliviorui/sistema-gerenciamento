<?php
require_once 'protecao_funcionario.php';

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Funcionário';

// Disciplinas
$disciplinas = [];
$resDis = mysqli_query($conn, "SELECT id_disciplina, nome FROM disciplinas ORDER BY nome ASC");
if ($resDis) {
    while ($row = mysqli_fetch_assoc($resDis)) {
        $disciplinas[] = $row;
    }
}

// Atividades
$sqlAt = "SELECT a.id_atividade, d.nome AS disciplina, a.titulo, a.descricao, a.data_limite, a.criado_em
          FROM atividades a
          INNER JOIN disciplinas d ON d.id_disciplina = a.id_disciplina
          ORDER BY a.criado_em DESC";
$resAt = mysqli_query($conn, $sqlAt);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atividades - Funcionário</title>
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
        <h1>Atividades</h1>
        <div class="actions">
            <span class="muted">Bem-vindo(a), <strong><?= htmlspecialchars((string)$usuario_nome, ENT_QUOTES, 'UTF-8'); ?></strong></span>
        </div>
    </header>

    <main class="main">
        <h2 class="page-title">Criar nova atividade</h2>

        <div class="card" style="margin-bottom: 16px;">
            <div class="card-body">
                <form action="../../controllers/criar_atividade.php" method="POST" class="grid-2">
                    <?= csrf_field(); ?>

                    <div class="field">
                        <label>Disciplina</label>
                        <select name="id_disciplina" required>
                            <option value="">Selecione</option>
                            <?php foreach ($disciplinas as $d): ?>
                                <option value="<?= (int)$d['id_disciplina']; ?>">
                                    <?= htmlspecialchars((string)$d['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label>Data limite</label>
                        <input type="date" name="data_limite">
                    </div>

                    <div class="field" style="grid-column: 1 / -1;">
                        <label>Título</label>
                        <input type="text" name="titulo" placeholder="Ex: Trabalho 1" required>
                    </div>

                    <div class="field" style="grid-column: 1 / -1;">
                        <label>Descrição</label>
                        <input type="text" name="descricao" placeholder="Opcional">
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <button class="btn btn-success" type="submit">Criar atividade</button>
                    </div>
                </form>
            </div>
        </div>

        <h2 class="page-title">Lista de atividades</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th>Título</th>
                    <th>Data limite</th>
                    <th>Criado em</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($resAt): ?>
                    <?php while ($a = mysqli_fetch_assoc($resAt)): ?>
                        <tr>
                            <td><?= htmlspecialchars((string)$a['disciplina'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <strong><?= htmlspecialchars((string)$a['titulo'], ENT_QUOTES, 'UTF-8'); ?></strong><br>
                                <span class="muted"><?= htmlspecialchars((string)($a['descricao'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                            </td>
                            <td><?= htmlspecialchars((string)($a['data_limite'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars((string)$a['criado_em'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4">Nenhuma atividade.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
</body>
</html>