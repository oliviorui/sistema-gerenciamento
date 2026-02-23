<?php
require_once '../config/bootstrap.php';

require_funcionario_or_admin($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/funcionario/entregas.php");
    exit();
}

csrf_verify_or_exit();

$id_entrega = (int)($_POST['id_entrega'] ?? 0);
$status = trim((string)($_POST['status'] ?? ''));
$feedback = trim((string)($_POST['feedback'] ?? ''));
$notaRaw = trim((string)($_POST['nota'] ?? ''));

if ($id_entrega <= 0) {
    header("Location: ../pages/funcionario/entregas.php");
    exit();
}

$allowedStatus = ['Pendente', 'Aprovado', 'Rejeitado'];
if (!in_array($status, $allowedStatus, true)) {
    echo "<p>Status inválido.</p>";
    exit();
}

$nota = null;
if ($notaRaw !== '') {
    $notaFloat = (float)$notaRaw;
    if ($notaFloat < 0 || $notaFloat > 20) {
        echo "<p>Nota inválida (0–20).</p>";
        exit();
    }
    $nota = $notaFloat;
}

$avaliado_por = (int)($_SESSION['usuario_id'] ?? 0);

$sql = "UPDATE entregas
        SET status = ?, feedback = ?, nota = ?, avaliado_por = ?, avaliado_em = NOW()
        WHERE id_entrega = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "<p>Erro ao preparar avaliação.</p>";
    exit();
}

// nota pode ser NULL: truque -> usar variável e bind como double, mas quando NULL: setar como null e usar 'd' não funciona bem.
// Solução simples: se nota for null, fazemos 2 queries.
$stmt->close();

if ($nota === null) {
    $sql2 = "UPDATE entregas
            SET status = ?, feedback = ?, nota = NULL, avaliado_por = ?, avaliado_em = NOW()
            WHERE id_entrega = ?";
    $stmt2 = $conn->prepare($sql2);
    if (!$stmt2) { echo "<p>Erro ao avaliar.</p>"; exit(); }
    $stmt2->bind_param("ssii", $status, $feedback, $avaliado_por, $id_entrega);
    $stmt2->execute();
    $stmt2->close();
} else {
    $sql3 = "UPDATE entregas
            SET status = ?, feedback = ?, nota = ?, avaliado_por = ?, avaliado_em = NOW()
            WHERE id_entrega = ?";
    $stmt3 = $conn->prepare($sql3);
    if (!$stmt3) { echo "<p>Erro ao avaliar.</p>"; exit(); }
    $stmt3->bind_param("ssdii", $status, $feedback, $nota, $avaliado_por, $id_entrega);
    $stmt3->execute();
    $stmt3->close();
}

// Log
$data_hora = date('Y-m-d H:i:s');
$descLog = "Avaliou uma entrega (#$id_entrega) -> $status";

$sqlLog = "INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Registro')";
$stmtLog = $conn->prepare($sqlLog);
if ($stmtLog) {
    $stmtLog->bind_param("iss", $avaliado_por, $data_hora, $descLog);
    $stmtLog->execute();
    $stmtLog->close();
}

header("Location: ../pages/funcionario/entregas.php");
exit();
