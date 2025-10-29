<?php
$host = "sql310.infinityfree.com";
$usuario = "if0_40253039"; 
$senha = "pu1OMADnsKH";       
$banco = "if0_40253039_sistema_redacoes";

$conn = new mysqli($host, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


?>
