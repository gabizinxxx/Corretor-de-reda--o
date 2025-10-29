<?php
session_start();
require "conexao.php";

// Verifica se o professor está logado

if (!isset($_SESSION["id"])) {
    header("Location: index.php");
    exit;
}

// Verifica se id_turma foi passado
if (!isset($_GET['id_turma'])) {
    header("Location: professor.php");
    exit;
}

$id_turma = intval($_GET['id_turma']);

// Consulta o nome da turma
$stmt = $conn->prepare("SELECT nome_turma FROM turmas WHERE id = ?");
$stmt->bind_param("i", $id_turma);
$stmt->execute();
$result_turma = $stmt->get_result();
$turma = $result_turma->fetch_assoc();
$stmt->close();

// Consulta os alunos e suas redações
$stmt = $conn->prepare("
    SELECT a.id AS id_aluno, a.nome AS nome_aluno,
           r.texto, r.imagem, r.nota_total, r.comp1, r.comp2, r.comp3, r.comp4, r.comp5, r.feedback, r.data_envio
    FROM alunos a
    LEFT JOIN redacoes r ON a.id = r.id_aluno
    WHERE a.id_turma = ?
    ORDER BY a.nome ASC
");
$stmt->bind_param("i", $id_turma);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="icon.png" type="image/png">
<title>Alunos da Turma <?php echo htmlspecialchars($turma['nome_turma']); ?></title>
<style>
body {
    font-family: Arial, sans-serif;
    background-color: #0d1117;
    color: white;
    margin: 0;
    padding: 0;
}
header {
    background-color: #161b22;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #30363d;
}
header h1 {
    margin: 0;
    font-size: 22px;
    color: #00ffff;
}
header a {
    color: white;
    text-decoration: none;
    background-color: #1f6feb;
    padding: 8px 15px;
    border-radius: 20px;
    transition: 0.3s;
}
header a:hover {
    background-color: #00ffff;
    color: #0d1117;
}
main {
    padding: 30px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
}
.aluno {
    background-color: #161b22;
    border: 1px solid #30363d;
    border-radius: 10px;
    padding: 20px;
    width: 90%;
    max-width: 800px;
}
.aluno h3 {
    margin-top: 0;
    color: #00ffff;
}
textarea {
    width: 100%;
    min-height: 100px;
    border-radius: 10px;
    border: 1px solid #30363d;
    background-color: #0d1117;
    color: #dbe7e7ff;
    padding: 10px;
    resize: vertical;
}
img.redacao {
    max-width: 100%;
    border-radius: 10px;
    margin-top: 10px;
}
footer {
    text-align: center;
    padding: 15px;
    color: #888;
    font-size: 12px;
}
p {
    margin: 5px 0;
}

input{
    background-color: #161b22;
    color: aliceblue;
    border: none;
}

button{
    background-color: #1f6feb;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: #00ffff;
    color: #0d1117;
}

</style>
</head>
<body>

<header>
    <h1>Alunos da Turma <?php echo htmlspecialchars($turma['nome_turma']); ?></h1>
    <a href="professor.php">Voltar</a>
</header>

<main>
<?php if($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="aluno">
            <h3><?php echo htmlspecialchars($row['nome_aluno']); ?></h3>

            <?php if($row['texto'] || $row['imagem']): ?>
                <?php if($row['texto']): ?>
                    <label>Redação (Texto):</label>
                    <textarea readonly><?php echo htmlspecialchars($row['texto']); ?></textarea>
                <?php endif; ?>

                <?php if($row['imagem']): ?>
    <label>Redação (Imagem):</label>
    <img src="data:image/jpeg;base64,<?= $row['imagem'] ?>" class="redacao" alt="Redação do aluno">
<?php endif; ?>



                <form method="POST" action="atualizar_redacao.php">
    <input type="hidden" name="id_aluno" value="<?= $row['id_aluno'] ?>">


    <p>
        <label>Nota Total:</label>
        <input type="number" name="nota_total" value="<?= $row['nota_total'] ?? 0 ?>" min="0" max="1000" required>
    </p>
    <p>
        <label>C1:</label><input type="number" name="comp1" value="<?= $row['comp1'] ?? 0 ?>" min="0" max="200">
        <label>C2:</label><input type="number" name="comp2" value="<?= $row['comp2'] ?? 0 ?>" min="0" max="200">
        <label>C3:</label><input type="number" name="comp3" value="<?= $row['comp3'] ?? 0 ?>" min="0" max="200">
        <label>C4:</label><input type="number" name="comp4" value="<?= $row['comp4'] ?? 0 ?>" min="0" max="200">
        <label>C5:</label><input type="number" name="comp5" value="<?= $row['comp5'] ?? 0 ?>" min="0" max="200">
    </p>
    <p>
        <label>Feedback:</label>
        <textarea name="feedback"><?= htmlspecialchars($row['feedback'] ?? '') ?></textarea>
    </p>

    <button type="submit">Atualizar</button>
</form>
<hr>

                <?php if(!empty($row['data_envio'])): ?>
                    <p><strong>Data de envio:</strong> <?php echo date("d/m/Y H:i", strtotime($row['data_envio'])); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <p>O aluno ainda não enviou redação.</p>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>Nenhum aluno cadastrado nesta turma.</p>
<?php endif; ?>
</main>

<footer>
    Todos os direitos reservados © CETEP Medeiros Neto - BA 2025 |
</footer>

</body>
</html>
