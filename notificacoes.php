<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

require "conexao.php";

$id_aluno = $_SESSION['id'];
$nomeAluno = $_SESSION['nome'] ?? "Aluno";

// Pega a turma do aluno
$stmt = $conn->prepare("SELECT id_turma FROM alunos WHERE id = ?");
$stmt->bind_param("i", $id_aluno);
$stmt->execute();
$result = $stmt->get_result();
$aluno = $result->fetch_assoc();
$turma_id = $aluno['id_turma'] ?? null;
$stmt->close();

// Busca notificações da turma do aluno usando tabela notificacoes_turma
$notificacoes = [];
if ($turma_id) {
    $sql = "SELECT n.*, p.nome AS nome_professor
        FROM notificacoes n
        JOIN notificacoes_turmas nt ON n.id = nt.id_notificacao
        JOIN professores p ON n.id_professor = p.id
        WHERE nt.id_turma = ?
        ORDER BY n.data_envio DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $turma_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $notificacoes[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Notificações</title>
<style>
body { font-family: Arial, sans-serif; background-color: #0d1117; color: white; margin: 0; padding: 0; }
header { background-color: #161b22; padding: 15px 25px; display: flex; position: relative;justify-content: space-between; align-items: center; border-bottom: 1px solid #30363d; }
header h1 { margin: 0; color: #00ffff; }
header a { color: white; text-decoration: none; background-color: #1f6feb; padding: 8px 15px; border-radius: 20px; }
header a:hover { background-color: #00ffff; color: #0d1117; }
main { padding: 30px; max-width: 800px; margin: auto; }
.noti { background-color: #161b22; border: 1px solid #30363d; border-radius: 10px; padding: 15px; margin-bottom: 15px; }
.noti img { max-width: 100%; margin-top: 10px; border-radius: 8px; }
footer { text-align: center; padding: 15px; color: #888; font-size: 12px; padding-top: 20px; }
p{
    text-align: center;
}
</style>
</head>
<body>

<header>
    <h1>Notificações</h1>
    
    <a href="corretor.php">Voltar</a>
</header>

<main>
    <?php if (!empty($notificacoes)): ?>
        <?php foreach ($notificacoes as $noti): ?>
            <div class="noti">
                <h3><?php echo htmlspecialchars($noti['titulo']); ?></h3>
                <p><?php echo nl2br(htmlspecialchars($noti['mensagem'])); ?></p>
               <?php if (!empty($noti['imagem'])): ?>
    <img src="data:image/png;base64,<?php echo $noti['imagem']; ?>" alt="Imagem do aviso">
<?php endif; ?>

                <small>
                Enviado por: <strong><?php echo htmlspecialchars($noti['nome_professor']); ?></strong>    
                em: <?php echo htmlspecialchars($noti['data_envio']); ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma notificação para sua turma.</p>
    <?php endif; ?>
</main>

<footer>
    Todos os direitos reservados © CETEP Medeiros Neto - BA 2025 |
</footer>

</body>
</html>
