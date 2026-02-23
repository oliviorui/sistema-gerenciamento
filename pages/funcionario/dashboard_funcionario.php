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

// Estudantes
$estudantes = [];
$stmtEst = $conn->prepare("SELECT id_usuario, nome, email FROM usuarios WHERE tipo = 'estudante' ORDER BY nome ASC");
if ($stmtEst) {
    $stmtEst->execute();
    $resEst = $stmtEst->get_result();
    if ($resEst) {
        while ($row = $resEst->fetch_assoc()) {
            $estudantes[] = $row;
        }
    }
    $stmtEst->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Funcionário</title>
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
        <h1>Painel do Funcionário</h1>
        <div class="actions">
            <span class="muted">Bem-vindo(a), <strong><?= htmlspecialchars((string)$usuario_nome, ENT_QUOTES, 'UTF-8'); ?></strong></span>
        </div>
    </header>

    <main class="main">
        <h2 class="page-title">Lançamento de Notas</h2>

        <section class="grid-2">
            <div class="card">
                <div class="card-body">
                    <h3 style="margin:0 0 12px;">Registar Nota</h3>

                    <form action="../../controllers/adiciona_nota.php" method="POST">
                        <?= csrf_field(); ?>

                        <div class="field">
                            <label for="id_usuario_alvo">Estudante</label>
                            <select name="id_usuario_alvo" id="id_usuario_alvo" required>
                                <option value="">Selecione o estudante</option>
                                <?php foreach ($estudantes as $e): ?>
                                    <option value="<?= (int)$e['id_usuario']; ?>">
                                        <?= htmlspecialchars((string)$e['nome'], ENT_QUOTES, 'UTF-8'); ?> —
                                        <?= htmlspecialchars((string)$e['email'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="disciplina">Disciplina</label>
                            <select name="disciplina" id="disciplina" required>
                                <option value="">Selecione uma disciplina</option>
                                <?php foreach ($disciplinas as $d): ?>
                                    <option value="<?= (int)$d['id_disciplina']; ?>">
                                        <?= htmlspecialchars((string)$d['nome'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label for="nota">Nota</label>
                            <input type="number" step="0.01" name="nota" id="nota" placeholder="Ex: 14.5" required>
                        </div>

                        <div class="field">
                            <label for="tipo_avaliacao">Tipo</label>
                            <select name="tipo_avaliacao" id="tipo_avaliacao" required>
                                <option value="Prova">Prova</option>
                                <option value="Trabalho">Trabalho</option>
                                <option value="Exame">Exame</option>
                            </select>
                        </div>

                        <div class="field">
                            <label for="data_avaliacao">Data</label>
                            <input type="date" name="data_avaliacao" id="data_avaliacao">
                        </div>

                        <button class="btn btn-success" type="submit">Registar</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</div>
</body>
</html>
