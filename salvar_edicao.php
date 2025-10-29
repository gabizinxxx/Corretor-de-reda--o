<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

require "conexao.php";

$id_professor = $_SESSION['id'];
$id = intval($_POST['id']);
$titulo = $_POST['titulo'];
$mensagem = $_POST['mensagem'];
$turmas = $_POST['turmas'] ?? [];

// Verifica se o aviso pertence ao professor
$stmt = $conn->prepare("SELECT id FROM notificacoes WHERE id = ? AND id_professor = ?");
$stmt->bind_param("ii", $id, $id_professor);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    die("Aviso não encontrado ou sem permissão.");
}
$stmt->close();

// Atualiza imagem (opcional)
if (!empty($_FILES['imagem']['tmp_name'])) {
    $imagem_base64 = base64_encode(file_get_contents($_FILES['imagem']['tmp_name']));
    $stmt = $conn->prepare("UPDATE notificacoes SET titulo=?, mensagem=?, imagem=? WHERE id=?");
    $stmt->bind_param("sssi", $titulo, $mensagem, $imagem_base64, $id);
} else {
    $stmt = $conn->prepare("UPDATE notificacoes SET titulo=?, mensagem=? WHERE id=?");
    $stmt->bind_param("ssi", $titulo, $mensagem, $id);
}
$stmt->execute();
$stmt->close();

// Atualiza turmas
$conn->query("DELETE FROM notificacoes_turmas WHERE id_notificacao = $id");
foreach ($turmas as $turma_id) {
    $stmt = $conn->prepare("INSERT INTO notificacoes_turmas (id_notificacao, id_turma) VALUES (?, ?)");
    $stmt->bind_param("ii", $id, $turma_id);
    $stmt->execute();
}
$stmt->close();

header("Location: avisos.php");
exit;
?>
