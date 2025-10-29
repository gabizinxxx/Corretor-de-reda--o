<?php
require "conexao.php";
$erro = "";
$sucesso = "";

// Pega as turmas disponíveis (para alunos)
$sqlTurmas = "SELECT id, nome_turma FROM turmas ORDER BY nome_turma ASC";
$resTurmas = $conn->query($sqlTurmas);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tipo = $_POST["tipo"];
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    if ($tipo === "aluno") {
        $id_turma = intval($_POST["id_turma"]);
        $stmt = $conn->prepare("INSERT INTO alunos (nome, email, senha, id_turma) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nome, $email, $senha, $id_turma);
    } else {
        $stmt = $conn->prepare("INSERT INTO professores (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $email, $senha);
    }

    if ($stmt->execute()) {
        $sucesso = "Cadastro realizado com sucesso! Agora faça login.";
    } else {
        $erro = "Erro ao cadastrar. Verifique se o e-mail já foi usado.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="icon.png" type="image/png">
<title>Cadastro - Sistema de Redações</title>
<style>
body {
    background-color: #0d1117;
    color: white;
    font-family: Arial, sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
}
.container {
    background: #161b22;
    padding: 30px;
    border-radius: 15px;
    width: 380px;
    box-shadow: 0 0 10px #00ffff33;
}
h2 {
    text-align: center;
    color: #00ffff;
}
input, select, button {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border-radius: 8px;
    border: 1px solid #30363d;
    background-color: #0d1117;
    color: white;
    font-size: 14px;
}
button {
    background-color: #1f6feb;
    border: none;
    margin-top: 15px;
    cursor: pointer;
}
button:hover {
    background-color: #00ffff;
    color: #0d1117;
}
.msg {
    text-align: center;
    margin-top: 10px;
}
a {
    color: #00ffff;
    text-decoration: none;
}

.header {
    position: absolute;
    top: 20px;
    left: 0;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 15px;
}

.header .title {
    font-size: 25px;
    font-weight: bold;
    color: #0d1117;
    background-color: white;
    padding: 10px 20px;
    border-radius: 20px;
}

</style>
</head>
<body>

<div class="header">
    <div class="title">LinIA</div>
    <div class="logo"><img src="logocetep.png" alt="Logo CETEP"></div>
</div>

<div class="container">
    <h2>Criar Conta</h2>
    <form method="POST" action="">
        <label for="tipo">Tipo de usuário:</label>
        <select name="tipo" id="tipo" onchange="toggleTurma()" required>
            <option value="aluno">Aluno</option>
            <option value="professor">Professor</option>
        </select>

        <label for="nome">Nome:</label>
        <input type="text" name="nome" id="nome" required>

        <label for="email">E-mail:</label>
        <input type="email" name="email" id="email" required>

        <label for="senha">Senha:</label>
        <input type="password" name="senha" id="senha" required>

        <div id="turmaSelect">
            <label for="id_turma">Selecione sua turma:</label>
            <select name="id_turma" id="id_turma">
                <option value="">Selecione...</option>
                <?php while ($t = $resTurmas->fetch_assoc()): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome_turma']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <button type="submit">Cadastrar</button>

        <?php if($erro): ?><p class="msg" style="color:red;"><?= $erro ?></p><?php endif; ?>
        <?php if($sucesso): ?><p class="msg" style="color:lime;"><?= $sucesso ?></p><?php endif; ?>

        <p class="msg"><a href="index.php">Já tenho conta</a></p>
    </form>
</div>

<script>
function toggleTurma() {
    const tipo = document.getElementById('tipo').value;
    document.getElementById('turmaSelect').style.display = (tipo === 'aluno') ? 'block' : 'none';
}
toggleTurma();
</script>
</body>
</html>
