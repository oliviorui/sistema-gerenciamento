<?php
require_once 'protecao_admin.php';

// Buscar disciplinas
$disciplinas = mysqli_query($conn, "
    SELECT * FROM disciplinas
    ORDER BY nome ASC
");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gerir Disciplinas</title>
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
            <a href="dashboard_admin.php">Dashboard</a>
            <a href="usuarios.php">Gerir Usuários</a>
            <a href="disciplinas.php">Gerir Disciplinas</a>
        </nav>

        <div class="divider"></div>

        <form action="../../controllers/logout.php" method="POST">
            <?= csrf_field(); ?>
            <button class="btn btn-danger logout" type="submit">Sair</button>
        </form>
    </aside>

    <header class="topbar">
        <h1>Gestão de Disciplinas</h1>
        <div class="actions">
            <a class="btn btn-ghost" href="dashboard_admin.php">← Voltar</a>
        </div>
    </header>

    <main class="main">
        <div class="card" style="margin-bottom: 16px;">
            <div class="card-body">
                <h3 style="margin: 0 0 12px;">Adicionar disciplina</h3>

                <form method="POST" action="../../controllers/adicionar_disciplina.php" class="grid-2">
                    <?= csrf_field(); ?>

                    <div class="field">
                        <label>Nome</label>
                        <input type="text" name="nome" placeholder="Ex: Matemática" required>
                    </div>

                    <div class="field">
                        <label>Código</label>
                        <input type="text" name="codigo" placeholder="Ex: MAT101" required>
                    </div>

                    <div class="field" style="grid-column: 1 / -1;">
                        <label>Descrição</label>
                        <input type="text" name="descricao" placeholder="Opcional">
                    </div>

                    <div style="grid-column: 1 / -1;">
                        <button class="btn btn-success" type="submit">Adicionar</button>
                    </div>
                </form>
            </div>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Código</th>
                    <th>Descrição</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($d = mysqli_fetch_assoc($disciplinas)): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$d['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)$d['codigo'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)$d['descricao'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <form action="../../controllers/excluir_disciplina.php" method="POST"
                              onsubmit="return confirm('Excluir disciplina?');" style="display:inline;">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= (int)$d['id_disciplina']; ?>">
                            <button class="btn btn-danger" type="submit">Excluir</button>
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
