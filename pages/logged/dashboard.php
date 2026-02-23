<?php
require_once '../../config/bootstrap.php';
require_login($conn);

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');
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
                        <p class="muted" style="margin:10px 0 0;">
                            As tuas notas aparecem na tabela e no gráfico ao lado.
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
                    <h3 style="margin: 0 0 12px;">Gráfico (média por disciplina)</h3>
                    <canvas id="meuGrafico" height="140"></canvas>
                    <div id="graficoMsg" class="muted" style="margin-top:10px;"></div>
                </div>
            </div>
        </section>

        <div class="card" style="margin-top: 16px;">
            <div class="card-body">
                <h3 style="margin: 0 0 12px;">Minhas notas</h3>
                <div id="tabelaNotas"></div>
            </div>
        </div>
    </main>
</div>

<script>
    let chartRef = null;

    function renderTabela(notas) {
        const $wrap = $("#tabelaNotas");

        if (!Array.isArray(notas) || notas.length === 0) {
            $wrap.html('<p class="muted">Ainda não tens notas registradas.</p>');
            return;
        }

        let html = `
            <table class="table">
                <thead>
                    <tr>
                        <th>Disciplina</th>
                        <th>Nota</th>
                        <th>Tipo</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
        `;

        notas.forEach(n => {
            html += `
                <tr>
                    <td>${escapeHtml(n.disciplina || '')}</td>
                    <td><strong>${escapeHtml(String(n.nota ?? ''))}</strong></td>
                    <td>${escapeHtml(n.tipo_avaliacao || '')}</td>
                    <td>${escapeHtml(n.data_avaliacao || '')}</td>
                </tr>
            `;
        });

        html += `</tbody></table>`;
        $wrap.html(html);
    }

    function buildMediaPorDisciplina(notas) {
        const map = {}; // disciplina -> {sum, count}
        notas.forEach(n => {
            const disc = (n.disciplina || 'Sem disciplina');
            const val = parseFloat(n.nota);
            if (!Number.isFinite(val)) return;

            if (!map[disc]) map[disc] = {sum: 0, count: 0};
            map[disc].sum += val;
            map[disc].count += 1;
        });

        const labels = Object.keys(map);
        const values = labels.map(l => {
            const obj = map[l];
            return obj.count ? (obj.sum / obj.count) : 0;
        });

        return {labels, values};
    }

    function renderGrafico(notas) {
        const ctx = document.getElementById("meuGrafico");
        const msg = document.getElementById("graficoMsg");

        if (!ctx) return;

        if (!Array.isArray(notas) || notas.length === 0) {
            msg.textContent = "Sem dados para mostrar no gráfico.";
            if (chartRef) {
                chartRef.destroy();
                chartRef = null;
            }
            return;
        }

        const {labels, values} = buildMediaPorDisciplina(notas);

        if (labels.length === 0) {
            msg.textContent = "Sem dados válidos para cálculo da média.";
            if (chartRef) {
                chartRef.destroy();
                chartRef = null;
            }
            return;
        }

        msg.textContent = "";

        if (chartRef) {
            chartRef.destroy();
            chartRef = null;
        }

        chartRef = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Média',
                    data: values
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 20
                    }
                }
            }
        });
    }

    function carregarDados() {
        const termo = $("#searchTerm").val() || '';

        $.ajax({
            url: '../../dados/get_dados.php',
            method: 'GET',
            dataType: 'json',
            data: { search: termo },
            success: function(resp) {
                const notas = resp.notas || [];
                renderTabela(notas);
                renderGrafico(notas);
            },
            error: function(xhr) {
                $("#tabelaNotas").html('<p class="muted">Erro ao carregar as notas.</p>');
                $("#graficoMsg").text('Erro ao carregar o gráfico.');
                if (chartRef) { chartRef.destroy(); chartRef = null; }
            }
        });
    }

    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    $(document).ready(function() {
        carregarDados();

        let t = null;
        $("#searchTerm").on("input", function() {
            clearTimeout(t);
            t = setTimeout(carregarDados, 250);
        });
    });
</script>

</body>
</html>
