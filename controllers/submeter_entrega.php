<?php
require_once '../config/bootstrap.php';

require_login($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/logged/entregas.php");
    exit();
}

csrf_verify_or_exit();

$tipo = (string)($_SESSION['usuario_tipo'] ?? 'estudante');
if ($tipo !== 'estudante') {
    http_response_code(403);
    echo "Apenas estudantes podem submeter entregas.";
    exit();
}

$id_estudante = (int)($_SESSION['usuario_id'] ?? 0);
$id_atividade = (int)($_POST['id_atividade'] ?? 0);
$comentario = trim((string)($_POST['comentario'] ?? ''));

if ($id_atividade <= 0 || $id_estudante <= 0) {
    echo "<p>Atividade inválida.</p>";
    exit();
}

// Verifica se atividade existe
$stmtAt = $conn->prepare("SELECT id_atividade FROM atividades WHERE id_atividade = ? LIMIT 1");
if (!$stmtAt) {
    echo "<p>Erro ao validar atividade.</p>";
    exit();
}
$stmtAt->bind_param("i", $id_atividade);
$stmtAt->execute();
$resAt = $stmtAt->get_result();
$stmtAt->close();

if (!$resAt || $resAt->num_rows !== 1) {
    echo "<p>Atividade não encontrada.</p>";
    exit();
}

// Upload (opcional)
$allowedExt = ['pdf', 'doc', 'docx', 'zip', 'jpg', 'jpeg', 'png'];
$maxBytes = 10 * 1024 * 1024;

$origName = null;
$serverName = null;
$mime = null;
$size = null;

if (isset($_FILES['arquivo']) && is_array($_FILES['arquivo']) && ($_FILES['arquivo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
    $err = (int)($_FILES['arquivo']['error'] ?? UPLOAD_ERR_OK);
    if ($err !== UPLOAD_ERR_OK) {
        echo "<p>Erro no upload do arquivo.</p>";
        exit();
    }

    $tmp = (string)$_FILES['arquivo']['tmp_name'];
    $origName = (string)$_FILES['arquivo']['name'];
    $size = (int)$_FILES['arquivo']['size'];

    if ($size <= 0 || $size > $maxBytes) {
        echo "<p>Arquivo muito grande. Máx: 10MB.</p>";
        exit();
    }

    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        echo "<p>Formato não permitido. Use: pdf, doc, docx, zip, jpg, png.</p>";
        exit();
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp) ?: 'application/octet-stream';

    $serverName = bin2hex(random_bytes(16)) . '.' . $ext;

    $destDir = __DIR__ . '/../uploads/entregas/';
    if (!is_dir($destDir)) {
        echo "<p>Pasta de upload não existe: uploads/entregas/</p>";
        exit();
    }

    $destPath = $destDir . $serverName;

    if (!move_uploaded_file($tmp, $destPath)) {
        echo "<p>Falha ao salvar o arquivo.</p>";
        exit();
    }
}

// Insere ou atualiza (se já entregou antes, substitui)
$sqlCheck = "SELECT id_entrega FROM entregas WHERE id_atividade = ? AND id_estudante = ? LIMIT 1";
$stmtC = $conn->prepare($sqlCheck);

$existingId = null;

if ($stmtC) {
    $stmtC->bind_param("ii", $id_atividade, $id_estudante);
    $stmtC->execute();
    $resC = $stmtC->get_result();
    if ($resC && $resC->num_rows === 1) {
        $row = $resC->fetch_assoc();
        if (is_array($row)) {
            $existingId = (int)($row['id_entrega'] ?? 0);
        }
    }
    $stmtC->close();
}

if ($existingId) {
    // Atualiza entrega existente e volta status para Pendente
    $sqlUp = "UPDATE entregas
              SET comentario = ?,
                  arquivo_nome_original = ?,
                  arquivo_nome_servidor = ?,
                  arquivo_mime = ?,
                  arquivo_tamanho = ?,
                  data_entrega = NOW(),
                  status = 'Pendente',
                  feedback = NULL,
                  nota = NULL,
                  avaliado_por = NULL,
                  avaliado_em = NULL
              WHERE id_entrega = ?";

    $stmtUp = $conn->prepare($sqlUp);
    if (!$stmtUp) {
        echo "<p>Erro ao atualizar entrega.</p>";
        exit();
    }

    $stmtUp->bind_param("ssssiii", $comentario, $origName, $serverName, $mime, $size, $existingId);
    $stmtUp->execute();
    $stmtUp->close();
} else {
    $sqlIns = "INSERT INTO entregas
        (id_atividade, id_estudante, comentario, arquivo_nome_original, arquivo_nome_servidor, arquivo_mime, arquivo_tamanho)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmtIns = $conn->prepare($sqlIns);
    if (!$stmtIns) {
        echo "<p>Erro ao preparar entrega.</p>";
        exit();
    }

    $stmtIns->bind_param("iissssi", $id_atividade, $id_estudante, $comentario, $origName, $serverName, $mime, $size);
    $stmtIns->execute();
    $stmtIns->close();
}

// Log
$data_hora = date('Y-m-d H:i:s');
$descLog = "Submeteu uma entrega (atividade #$id_atividade)";

$sqlLog = "INSERT INTO logs_atividades (id_usuario, data_hora, descricao, tipo_actividade) VALUES (?, ?, ?, 'Registro')";
$stmtLog = $conn->prepare($sqlLog);
if ($stmtLog) {
    $stmtLog->bind_param("iss", $id_estudante, $data_hora, $descLog);
    $stmtLog->execute();
    $stmtLog->close();
}

header("Location: ../pages/logged/entregas.php");
exit();
