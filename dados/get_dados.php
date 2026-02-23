<?php
require_once '../config/bootstrap.php';

require_login($conn);

header('Content-Type: application/json; charset=utf-8');

$id_usuario = (int)($_SESSION['usuario_id'] ?? 0);
$tipo_usuario = (string)($_SESSION['usuario_tipo'] ?? 'usuario');
$nome_usuario = (string)($_SESSION['usuario_nome'] ?? 'Usuário');

$searchTerm = trim((string)($_GET['search'] ?? ''));
$like = '%' . $searchTerm . '%';

$dados = [];
$dados['current_user'] = [
    'id_usuario' => $id_usuario,
    'nome' => $nome_usuario,
    'tipo' => $tipo_usuario,
];

/**
 * 1) Usuários (SÓ ADMIN)
 */
if ($tipo_usuario === 'admin') {
    $usuarios = [];
    $sqlUsers = "SELECT id_usuario, nome, email, data_cadastro, tipo FROM usuarios ORDER BY nome ASC";
    $resUsers = $conn->query($sqlUsers);
    if ($resUsers) {
        while ($row = $resUsers->fetch_assoc()) {
            $usuarios[] = $row;
        }
    }
    $dados['usuarios'] = $usuarios;
} else {
    $dados['usuarios'] = []; // mantém compatibilidade, mas vazio
}

/**
 * 2) Disciplinas (com filtro)
 */
$disciplinas = [];
$sqlDis = "SELECT id_disciplina, nome, codigo, descricao
           FROM disciplinas
           WHERE (? = '' OR nome LIKE ? OR descricao LIKE ?)
           ORDER BY nome ASC";

$stmtDis = $conn->prepare($sqlDis);
if ($stmtDis) {
    $empty = ($searchTerm === '') ? '' : 'x'; // truque simples pra controlar o OR
    $stmtDis->bind_param("sss", $empty, $like, $like);
    $stmtDis->execute();
    $resDis = $stmtDis->get_result();

    if ($resDis) {
        while ($row = $resDis->fetch_assoc()) {
            $disciplinas[] = $row;
        }
    }
    $stmtDis->close();
}
$dados['disciplinas'] = $disciplinas;

/**
 * 3) Notas do usuário logado (com filtro)
 */
$notas = [];
$sqlNotas = "SELECT n.id_nota, d.nome AS disciplina, n.nota, n.tipo_avaliacao, n.data_avaliacao
             FROM notas n
             INNER JOIN disciplinas d ON n.id_disciplina = d.id_disciplina
             WHERE n.id_usuario = ?
               AND (? = '' OR d.nome LIKE ? OR CAST(n.nota AS CHAR) LIKE ?)
             ORDER BY n.data_avaliacao DESC";

$stmtNotas = $conn->prepare($sqlNotas);
if ($stmtNotas) {
    $stmtNotas->bind_param("isss", $id_usuario, $searchTerm, $like, $like);
    $stmtNotas->execute();
    $resNotas = $stmtNotas->get_result();

    if ($resNotas) {
        while ($row = $resNotas->fetch_assoc()) {
            $notas[] = $row;
        }
    }
    $stmtNotas->close();
}
$dados['notas'] = $notas;

/**
 * 4) Logs do usuário logado
 */
$logs = [];
$sqlLogs = "SELECT id_log, data_hora, descricao, tipo_actividade
            FROM logs_atividades
            WHERE id_usuario = ?
            ORDER BY data_hora DESC";

$stmtLogs = $conn->prepare($sqlLogs);
if ($stmtLogs) {
    $stmtLogs->bind_param("i", $id_usuario);
    $stmtLogs->execute();
    $resLogs = $stmtLogs->get_result();

    if ($resLogs) {
        while ($row = $resLogs->fetch_assoc()) {
            $logs[] = $row;
        }
    }
    $stmtLogs->close();
}
$dados['logs_atividades'] = $logs;

echo json_encode($dados);
exit();
