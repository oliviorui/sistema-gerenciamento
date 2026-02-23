<?php
require_once '../../config/bootstrap.php';
require_login($conn);

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');

// Disciplinas (para o gráfico e visualização)
$query_disciplinas = "SELECT id_disciplina, nome FROM disciplinas";
$result_disciplinas = mysqli_query($conn, $query_disciplinas);

$disciplinas = [];
if ($result_disciplinas) {
    while ($row = mysqli_fetch_assoc($result_disciplinas)) {
        $disciplinas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel - Sistema Acadêmico</title>
    <link rel="stylesheet" href="../../css/app.css">
    <script src="../../js/jquery.js"></script>
    <script src="../../js/chart.js"></script>
    <script src="../../js/validate.js"></script>
    <script src="../../js/valida_form.js"></script>
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

            <?php if ($tipo === 'funcionario' || $tipo === 'admin'): ?>
                <a href="../funcionario/dashboard_funcionario.php">Painel Funcionário</a>
            <?php endif; ?>

            <?php if ($tipo === 'admin'): ?>
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
        <h1>Painel</h1>
        <div class="actions">
            <span class="muted">Bem-vindo(a), <strong><?= htmlspecialchars((string)$usuario_nome, ENT_QUOTES, 'UTF-8') ?></strong></span>
        </div>
    </header>

    <main class="main">
        <h2 class="page-title">Notas & Desempenho</h2>

        <div class="helper-row">
            <div class="field" style="min-width: 260px; max-width: 420px; margin: 0;">
                <label for="searchTerm">Pesquisar notas</label>
                <input type="text" id="searchTerm" name="search" placeholder="Digite a disciplina ou a nota">
            </div>
        </div>

        <section class="grid-2">
            <div class="card">
                <div class="card-body">
                    <h3 style="margin: 0 0 12px;">Registar Nota</h3>

                    <?php if ($tipo === 'estudante'): ?>
                        <p class="muted" style="margin:0;">
                            Apenas <strong>Funcionário</strong> ou <strong>Admin</strong> podem lançar notas.
                        </p>
                    <?php else: ?>
                        <p class="muted" style="margin:0;">
                            Vá ao <strong>Painel Funcionário</strong> para lançar notas para estudantes.
                        </p>
                        <div style="margin-top: 12px;">
                            <a class="btn btn-success" href="../funcionario/dashboard_funcionario.php">Abrir Painel Funcionário</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h3 style="margin: 0 0 12px;">Gráficos</h3>
                    <canvas id="meuGrafico"></canvas>
                </div>
            </div>
        </section>

        <div id="tabelaNotas"></div>
    </main>
</div>
</body>
</html>
