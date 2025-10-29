<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

require "conexao.php";

$id_professor = $_SESSION["id"] ?? null;
date_default_timezone_set('America/Bahia');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo'] ?? '');
    $mensagem = trim($_POST['mensagem'] ?? '');
    $turmas = $_POST['turmas'] ?? [];
    
    if (empty($titulo) || empty($mensagem) || empty($turmas)) {
        die("Erro: Preencha todos os campos obrigatórios e selecione pelo menos uma turma.");
    }

    // Processa imagem (opcional)
    $imagemBase64 = null;
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === 0) {
        $caminhoTemp = $_FILES['imagem']['tmp_name'];
        $tipo = mime_content_type($caminhoTemp);
        $imagemBase64 = base64_encode(file_get_contents($caminhoTemp));
        $imagemBase64 = "$imagemBase64";
    }

    $data_envio = date("Y-m-d H:i:s");

    // Inserir notificação
    $stmt = $conn->prepare("INSERT INTO notificacoes (id_professor, titulo, mensagem, imagem, data_envio) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $id_professor, $titulo, $mensagem, $imagemBase64, $data_envio);
    $stmt->execute();
    $id_notificacao = $stmt->insert_id;
    $stmt->close();

    // Associar notificação às turmas selecionadas
    $stmtTurma = $conn->prepare("INSERT INTO notificacoes_turmas (id_notificacao, id_turma) VALUES (?, ?)");
    foreach ($turmas as $id_turma) {
        $id_turma = (int)$id_turma;
        $stmtTurma->bind_param("ii", $id_notificacao, $id_turma);
        $stmtTurma->execute();
    }
    $stmtTurma->close();

    $conn->close();

    // Redireciona de volta para o painel
    header("Location: professor.php?success=1");
    exit;
} else {
    header("Location: professor.php");
    exit;
}
