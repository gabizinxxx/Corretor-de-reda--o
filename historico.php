<?php
session_start();
require "conexao.php";

 //Verifica se o aluno está logado
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'aluno') {
    header("Location: index.php");
    exit;
}

$id_aluno = $_SESSION['id'];
$nomeAluno = $_SESSION['nome'];

// Busca todas as redações do aluno
$stmt = $conn->prepare("
    SELECT texto, imagem, comp1, comp2, comp3, comp4, comp5, nota_total, feedback, data_envio
    FROM redacoes
    WHERE id_aluno = ?
    ORDER BY data_envio DESC
");
$stmt->bind_param("i", $id_aluno);
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
<title>Histórico de Redações - <?= htmlspecialchars($nomeAluno) ?></title>
<style>
body { font-family: Arial, sans-serif; background-color: #0d1117; color: white; margin: 0; padding: 0; }
header {
    background-color: #161b22;
    padding: 15px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #30363d;
}
header h1 { margin: 0; color: #00ffff; }
main { padding: 30px; display: flex; flex-direction: column; align-items: center; gap: 20px; }
.redacao-box { background-color: #161b22; border: 1px solid #30363d; border-radius: 10px; padding: 20px; width: 90%; max-width: 800px; }
textarea { width: 100%; min-height: 100px; border-radius: 10px; border: 1px solid #30363d; background-color: #0d1117; color: #00ffff; padding: 10px; resize: vertical; }
img.redacao { max-width: 100%; border-radius: 10px; margin-top: 10px; }

   footer {
      text-align: center;
      padding: 15px;
      color: #888;
      font-size: 12px;
      padding-top: 40%;
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


</style>
</head>
<body>

<header>
    <h1>Histórico de Redações de <?= htmlspecialchars($nomeAluno) ?></h1>
    <a href="corretor.php">Voltar</a>
</header>

<main>
<?php if($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="redacao-box">
            <?php if($row['texto']): ?>
                <label>Redação (Texto):</label>
                <textarea readonly><?= htmlspecialchars($row['texto']) ?></textarea>
            <?php endif; ?>

            <?php if($row['imagem']): ?>
                <label>Redação (Imagem):</label>
                <img src="data:image/jpeg;base64,<?= $row['imagem'] ?>" class="redacao" alt="Redação do aluno">
            <?php endif; ?>

            <p><strong>Nota Total:</strong> <?= $row['nota_total'] ?? 'Ainda não corrigida' ?></p>
            <p><strong>Competências:</strong> 
                <?php
                    if($row['comp1'] !== null){
                        echo "C1: {$row['comp1']}, C2: {$row['comp2']}, C3: {$row['comp3']}, C4: {$row['comp4']}, C5: {$row['comp5']}";
                    } else {
                        echo "Sem avaliação";
                    }
                ?>
            </p>
            <p><strong>Feedback:</strong> <?= $row['feedback'] ?? 'Sem feedback' ?></p>
            <p><strong>Data de envio:</strong> <?= !empty($row['data_envio']) ? date("d/m/Y H:i", strtotime($row['data_envio'])) : '-' ?></p>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p>Nenhuma redação enviada ainda.</p>
<?php endif; ?>
</main>
<footer>
    Todos os direitos reservados © CETEP Medeiros Neto - BA 2025 |
</footer>
</body>
</html>
