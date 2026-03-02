<?php
declare(strict_types=1);

function docente_tem_atribuicao(mysqli $conn, int $idDocente, int $idTurma, int $idDisciplina): bool
{
    $sql = "SELECT 1 FROM atribuicoes WHERE id_docente = ? AND id_turma = ? AND id_disciplina = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;

    $stmt->bind_param("iii", $idDocente, $idTurma, $idDisciplina);
    $stmt->execute();
    $res = $stmt->get_result();
    $ok = ($res && $res->num_rows > 0);
    $stmt->close();
    return $ok;
}

function buscar_turma_do_estudante(mysqli $conn, int $idEstudante): ?int
{
    $sql = "SELECT id_turma FROM usuarios WHERE id_usuario = ? AND tipo = 'estudante' LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;

    $stmt->bind_param("i", $idEstudante);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    if (!$row || $row['id_turma'] === null) return null;
    return (int)$row['id_turma'];
}