<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

require "conexao.php";

// Pega o ID e nome da sessão
$id_professor = $_SESSION["id"] ?? null;
$nomeProfessor = $_SESSION["nome"] ?? "Professor"; // valor padrão

// Consulta no banco para garantir que existe
if ($id_professor) {
    $stmt = $conn->prepare("SELECT nome FROM professores WHERE id = ?");
    $stmt->bind_param("i", $id_professor);
    $stmt->execute();
    $resultProf = $stmt->get_result();
    $professor = $resultProf->fetch_assoc();
    $nomeProfessor = $professor ? $professor['nome'] : $nomeProfessor;
    $stmt->close();
}

// Consulta as turmas
$result = $conn->query("SELECT * FROM turmas ORDER BY nome_turma ASC");
if (!$result) $result = [];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="icon.png" type="image/png">
<title>Painel do Professor</title>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #0d1117;
    color: white;
    margin: 0;
    padding: 0;
}

/* ===== HEADER ===== */
header {
 display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 25px;
  background-color: #0d1117;
  flex-wrap: wrap;
  border-bottom: 1px solid #30363d;
  position: relative;
}

header h1 {
    margin: 0;
    font-size: 22px;
    color: #00ffff;
}

header a, .noti {
    color: white;
    text-decoration: none;
    background-color: #1f6feb;
    padding: 8px 15px;
    border-radius: 20px;
    transition: 0.3s;
    border: none;
}

header a:hover, .noti:hover {
    background-color: #00ffff;
    color: #0d1117;
}

/* ===== MAIN ===== */
main {
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

/* Turmas */
.turma {
    background-color: #161b22;
    border: 1px solid #30363d;
    border-radius: 10px;
    padding: 20px;
    width: 90%;
    max-width: 700px;
    margin-bottom: 20px;
    transition: 0.3s;
}

.turma:hover {
    border-color: #00ffff;
    box-shadow: 0 0 10px #00ffff55;
}

.turma a {
    color: #00ffff;
    font-size: 20px;
    text-decoration: none;
    font-weight: bold;
}

/* Menu de notificações */
#notificacao-menu {
    display: none;
    flex-direction: column;
    gap: 10px;
    background-color: #161b22;
    border: 1px solid #30363d;
    border-radius: 10px;
    padding: 20px;
    width: 90%;
    max-width: 700px;
    margin-bottom: 20px;
}

#notificacao-menu input, #notificacao-menu textarea {
    width: 100%;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #30363d;
    background-color: #0d1117;
    color: white;
    margin-bottom: 10px;
}

#notificacao-menu button {
    background-color: #1f6feb;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 16px;
    transition: 0.3s;
}

#notificacao-menu button:hover {
    background-color: #00ffff;
    color: #0d1117;
}

#notificacao-menu .checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 10px;
}

#notificacao-menu .checkbox-group label {
    display: flex;
    align-items: center;
    gap: 5px;
    background-color: #161b22;
    border: 1px solid #30363d;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
}

footer {
    text-align: center;
    padding: 15px;
    color: #888;
    font-size: 12px;
    padding-top: 20%;
}
</style>
</head>
<body>

<header>
    <h1>Painel do Professor</h1>
    <h1>Bem-vindo, <?php echo htmlspecialchars($nomeProfessor); ?>!</h1>
    <a href="avisos.php">🔔 Avisos</a>
    <a href="logout.php">Sair</a>
</header>

<main>
    <h2>Turmas disponíveis</h2>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="turma">
                <a href="alunos.php?id_turma=<?php echo $row['id']; ?>">
                    <?php echo htmlspecialchars($row['nome_turma']); ?>
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>Nenhuma turma cadastrada.</p>
    <?php endif; ?>

    <div id="notificacao-menu">
        <form method="POST" action="enviar_notificacao.php" enctype="multipart/form-data">
            <label for="titulo">Título:</label>
            <input type="text" id="titulo" name="titulo" required>

            <label for="mensagem">Mensagem:</label>
            <textarea id="mensagem" name="mensagem" rows="4" required></textarea>

            <label>Selecione a(s) turma(s):</label>
            <div class="checkbox-group">
                <?php
                $result->data_seek(0); // reinicia ponteiro do resultado
                while ($row = $result->fetch_assoc()):
                ?>
                    <label>
                        <input type="checkbox" name="turmas[]" value="<?php echo $row['id']; ?>">
                        <?php echo htmlspecialchars($row['nome_turma']); ?>
                    </label>
                <?php endwhile; ?>
            </div>

            <label for="imagem">Enviar imagem (opcional):</label>
            <input type="file" id="imagem" name="imagem" accept="image/*">

            <button type="submit">Enviar</button>
        </form>
    </div>
</main>

<footer>
    Todos os direitos reservados © CETEP Medeiros Neto - BA 2025 |
</footer>

<script>
function toggleNotificacaoMenu() {
    const menu = document.getElementById('notificacao-menu');
    menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
}
</script>

</body>
</html>
