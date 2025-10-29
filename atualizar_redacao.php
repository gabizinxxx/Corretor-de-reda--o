<?php
session_start();
require "conexao.php";

// Verifica se professor está logado
//if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'professor') {
   // header("Location: index.php");
    //exit;
//}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_aluno = intval($_POST['id_aluno']);
    $nota_total = intval($_POST['nota_total']);
    $comp1 = intval($_POST['comp1']);
    $comp2 = intval($_POST['comp2']);
    $comp3 = intval($_POST['comp3']);
    $comp4 = intval($_POST['comp4']);
    $comp5 = intval($_POST['comp5']);
    $feedback = trim($_POST['feedback']);
    $data_envio = date("Y-m-d H:i:s");

    $sql = "UPDATE redacoes 
            SET nota_total=?, comp1=?, comp2=?, comp3=?, comp4=?, comp5=?, feedback=?, data_envio=? 
            WHERE id_aluno=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiisssi", $nota_total, $comp1, $comp2, $comp3, $comp4, $comp5, $feedback, $data_envio, $id_aluno);

    if ($stmt->execute()) {
        header("Location: alunos.php");
    } else {
        echo "Erro ao atualizar: " . $stmt->error;
    }
}
?>
