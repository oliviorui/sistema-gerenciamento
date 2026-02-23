<?php
require_once 'protecao_admin.php';

$usuarios = mysqli_query($conn, "
    SELECT id_usuario, nome, email, tipo, data_cadastro
    FROM usuarios
    ORDER BY nome ASC
");
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Gerir Usuários</title>
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
        <h1>Gestão de Usuários</h1>
        <div class="actions">
            <a class="btn btn-ghost" href="dashboard_admin.php">← Voltar</a>
        </div>
    </header>

    <main class="main">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th>Cadastro</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($u = mysqli_fetch_assoc($usuarios)): ?>
                <tr>
                    <td><?= htmlspecialchars((string)$u['nome'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)$u['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars(strtoupper((string)$u['tipo']), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars((string)$u['data_cadastro'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">

                        <!-- Alterar tipo -->
                        <form action="../../controllers/alterar_tipo_usuario.php" method="POST" style="display:flex; gap:8px; align-items:center;">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= (int)$u['id_usuario']; ?>">

                            <select name="tipo" required>
                                <option value="estudante" <?= $u['tipo']==='estudante'?'selected':''; ?>>Estudante</option>
                                <option value="funcionario" <?= $u['tipo']==='funcionario'?'selected':''; ?>>Funcionário</option>
                                <option value="admin" <?= $u['tipo']==='admin'?'selected':''; ?>>Admin</option>
                            </select>

                            <button type="submit" class="btn btn-success">Salvar</button>
                        </form>

                        <!-- Excluir -->
                        <?php if ((int)$u['id_usuario'] !== (int)($_SESSION['usuario_id'] ?? 0)): ?>
                            <form action="../../controllers/excluir_usuario.php" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= (int)$u['id_usuario']; ?>">
                                <button type="submit" class="btn btn-danger">Excluir</button>
                            </form>
                        <?php endif; ?>

                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
</div>

</body>
</html>
