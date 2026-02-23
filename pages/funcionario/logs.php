<?php
require_once '../../config/bootstrap.php';
require_login($conn);

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logs - Sistema Acadêmico</title>
    <link rel="stylesheet" href="../../css/app.css">
    <script src="../../js/jquery.js"></script>
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
                <a href="/logs.php">Logs</a>
            </nav>

            <div class="divider"></div>

            <form action="../../controllers/logout.php" method="POST">
                <?= csrf_field(); ?>
                <button class="btn btn-danger logout" type="submit">Sair</button>
            </form>
        </aside>

        <header class="topbar">
            <h1>Logs</h1>
            <div class="actions">
                <span class="muted">Bem-vindo(a), <strong id="usuarioNome"><?= htmlspecialchars((string)$usuario_nome, ENT_QUOTES, 'UTF-8'); ?></strong></span>
            </div>
        </header>

        <main class="main">
            <h2 class="page-title">Atividades no sistema</h2>

            <table class="table">
                <thead>
                    <tr>
                        <th>Data e Hora</th>
                        <th>Descrição</th>
                        <th>Tipo de Atividade</th>
                    </tr>
                </thead>
                <tbody id="tabelaLogs">
                    <tr>
                        <td colspan="3">Carregando...</td>
                    </tr>
                </tbody>
            </table>
        </main>
    </div>

    <script>
        $(document).ready(function() {
            $.ajax({
                url: '../../dados/get_dados.php',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    let logs = response.logs_atividades || [];
                    let tabelaLogs = $("#tabelaLogs");
                    tabelaLogs.empty();

                    if (logs.length > 0) {
                        logs.forEach(log => {
                            tabelaLogs.append(`
                                <tr>
                                    <td>${log.data_hora}</td>
                                    <td>${log.descricao}</td>
                                    <td>${log.tipo_actividade}</td>
                                </tr>
                            `);
                        });
                    } else {
                        tabelaLogs.append('<tr><td colspan="3">Nenhuma atividade registrada.</td></tr>');
                    }
                },
                error: function() {
                    $("#tabelaLogs").html('<tr><td colspan="3">Erro ao carregar os logs.</td></tr>');
                }
            });
        });
    </script>
</body>
</html>
