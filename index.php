<?php
session_start();
require "conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $tipo = $_POST['tipo'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    if ($tipo === "aluno") {
        $stmt = $conn->prepare("SELECT id, nome, senha FROM alunos WHERE email = ?");
    } else {
        $stmt = $conn->prepare("SELECT id, nome, senha FROM professores WHERE email = ?");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Se as senhas não estão criptografadas, troque essa linha por: if ($senha === $usuario['senha'])
        if (password_verify($senha, $usuario['senha'])) {
            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];
            $_SESSION['tipo'] = $tipo;

            if ($tipo === "aluno") {
                header("Location: corretor.php");
            } else {
                header("Location: professor.php");
            }
            exit;
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Usuário não encontrado!";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="icon.png" type="image/png">
<title>Login - Sistema de Redações</title>
<style>
body {
    background-color: #0d1117;
    color: white;
    font-family: Arial, sans-serif;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    margin: 0;
    min-height: 100vh;
}

/* ===== HEADER ===== */
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



/* ===== CONTAINER ===== */
.container {
    background: #161b22;
    padding: 30px;
    border-radius: 15px;
    width: 350px;
    box-shadow: 0 0 10px #00ffff33;
}

h2 {
    text-align: center;
    color: #00ffff;
}

select, input {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    border-radius: 8px;
    border: 1px solid #30363d;
    background-color: #0d1117;
    color: white;
}

button {
    width: 100%;
    background-color: #1f6feb;
    color: white;
    padding: 10px;
    border: none;
    border-radius: 8px;
    margin-top: 15px;
    cursor: pointer;
    font-size: 16px;
}

button:hover {
    background-color: #00ffff;
    color: #0d1117;
}

.erro {
    color: #ff5c5c;
    text-align: center;
    margin-top: 10px;
}

.cadastro-link {
    text-align: center;
    margin-top: 15px;
}

.cadastro-link a {
    color: #00ffff;
    text-decoration: none;
}

.cadastro-link a:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

<!-- ===== HEADER COM TÍTULO E LOGO ===== -->
<div class="header">
    <div class="title">LinIA</div>
    <div class="logo"><img src="logocetep.png" alt="Logo CETEP"></div>
</div>

<!-- ===== LOGIN ===== -->
<div class="container">
    <h2>Login</h2>
    <form method="POST">
        <label for="tipo">Entrar como:</label>
        <select name="tipo" id="tipo" required>
            <option value="aluno">Aluno</option>
            <option value="professor">Professor</option>
        </select>

        <label for="email">E-mail:</label>
        <input type="email" name="email" required>

        <label for="senha">Senha:</label>
        <input type="password" name="senha" required>

        <button type="submit">Entrar</button>

        <?php if ($erro): ?>
            <p class="erro"><?php echo $erro; ?></p>
        <?php endif; ?>
    </form>

    <div class="cadastro-link">
        <p>Não tem conta? <a href="cadastro.php">Crie uma agora</a></p>
        <a href="https://corretorderedacao.gt.tc/">Use sem login</a>
    </div>
</div>

</body>
</html>
