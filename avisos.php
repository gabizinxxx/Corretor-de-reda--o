<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit;
}

require "conexao.php";

$id_professor = $_SESSION['id'];
$nomeProfessor = $_SESSION['nome'] ?? "Professor";

// Excluir aviso
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    $stmt = $conn->prepare("DELETE FROM notificacoes WHERE id = ? AND id_professor = ?");
    $stmt->bind_param("ii", $id_excluir, $id_professor);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM notificacoes_turmas WHERE id_notificacao = ?");
    $stmt->bind_param("i", $id_excluir);
    $stmt->execute();
    $stmt->close();

    header("Location: avisos.php");
    exit;
}

// Consulta avisos
$stmt = $conn->prepare("
    SELECT n.*, GROUP_CONCAT(t.nome_turma SEPARATOR ', ') AS turmas 
    FROM notificacoes n
    LEFT JOIN notificacoes_turmas nt ON n.id = nt.id_notificacao
    LEFT JOIN turmas t ON nt.id_turma = t.id
    WHERE n.id_professor = ?
    GROUP BY n.id
    ORDER BY n.data_envio DESC
");
$stmt->bind_param("i", $id_professor);
$stmt->execute();
$avisos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Consulta turmas
$turmas = $conn->query("SELECT * FROM turmas ORDER BY nome_turma ASC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gerenciar Avisos</title>
<style>
body { font-family: Arial, sans-serif; background-color: #0d1117; color: white; margin: 0; padding: 0; }
header { background-color: #161b22; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #30363d; }
header h1 { margin: 0; color: #00ffff; }
header a { color: white; text-decoration: none; background-color: #1f6feb; padding: 8px 15px; border-radius: 20px; }
header a:hover { background-color: #00ffff; color: #0d1117; }
main { padding: 30px; max-width: 800px; margin: auto; }
.aviso { background-color: #161b22; border: 1px solid #30363d; border-radius: 10px; padding: 15px; margin-bottom: 15px; position: relative; }
.aviso h3 { margin-top: 0; color: #00ffff; }
.aviso img { max-width: 100%; margin-top: 10px; border-radius: 8px; }
.excluir, .editar { position: absolute; top: 10px; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer; }
.excluir { right: 10px; background: #e63946; color: white; }
.excluir:hover { background: #ff0000; }
.editar { right: 90px; background: #ffb703; color: black; }
.editar:hover { background: #ffd166; }
button, .btn { background-color: #1f6feb; color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; transition: 0.3s;
margin-bottom: 10px; }
button:hover, .btn:hover { background-color: #00ffff; color: #0d1117; }
.formulario { display: none; background-color: #161b22; border: 1px solid #30363d; border-radius: 10px; padding: 20px; margin-top: 20px; }
input, textarea { width: 100%; padding: 10px; margin: 8px 0; border-radius: 8px; border: none; background-color: #0d1117; color: white; border: 1px solid #30363d; }
.checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
.checkbox-item { background: #0d1117; padding: 8px 12px; border-radius: 8px; border: 1px solid #30363d; cursor: pointer; }
footer { text-align: center; padding: 15px; color: #888; font-size: 12px; padding-top: 20px; }
</style>
</head>
<body>

<header>
  <h1>Avisos do Professor</h1>
  <a href="professor.php">Voltar</a>
</header>

<main>
    <h2 style="margin-top:20px;">Seus avisos</h2>
<?php if (empty($avisos)): ?>
    <p>Nenhum aviso enviado ainda.</p>
    <button onclick="mostrarFormulario()">Criar primeiro aviso</button>
<?php else: ?>
    <button onclick="mostrarFormulario()">Criar novo aviso</button>
    <?php foreach ($avisos as $aviso): ?>
        <div class="aviso">
            <button class="editar" onclick="window.location.href='editar_aviso.php?id=<?php echo $aviso['id']; ?>'">Editar</button>
            <button class="excluir" onclick="excluirAviso(<?php echo $aviso['id']; ?>)">Excluir</button>
            <h3><?php echo htmlspecialchars($aviso['titulo']); ?></h3>
            <p><?php echo nl2br(htmlspecialchars($aviso['mensagem'])); ?></p>
            <?php if (!empty($aviso['imagem'])): ?>
                <img src="data:image/jpeg;base64,<?php echo $aviso['imagem']; ?>" alt="Imagem do aviso">
            <?php endif; ?>
            <small><b>Turmas:</b> <?php echo htmlspecialchars($aviso['turmas'] ?? 'Não informado'); ?></small><br>
            <small>Enviado em: <?php echo htmlspecialchars($aviso['data_envio']); ?></small>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="formulario" id="formularioAviso">
    <h2>Criar novo aviso</h2>
    <form action="enviar_notificacao.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="titulo" placeholder="Título do aviso" required>
        <textarea name="mensagem" placeholder="Digite a mensagem..." rows="5" required></textarea>

        <h3>Selecione as turmas:</h3>
        <button type="button" class="btn" onclick="selecionarTodas()">Selecionar todas</button>
        <div class="checkbox-group" id="checkboxGroup">
            <?php foreach ($turmas as $turma): ?>
                <label class="checkbox-item">
                    <input type="checkbox" name="turmas[]" value="<?php echo $turma['id']; ?>"> 
                    <?php echo htmlspecialchars($turma['nome_turma']); ?>
                </label>
            <?php endforeach; ?>
        </div>

        <h3>Imagem (opcional):</h3>
        <input type="file" name="imagem" accept="image/*">

        <button type="submit">Enviar aviso</button>
    </form>
</div>
</main>

<footer>
    Todos os direitos reservados © CETEP Medeiros Neto - BA 2025 |
</footer>

<script>
function mostrarFormulario() {
    document.getElementById('formularioAviso').style.display = 'block';
    window.scrollTo(0, document.body.scrollHeight);
}

function excluirAviso(id) {
    if (confirm("Tem certeza que deseja excluir este aviso?")) {
        window.location.href = "avisos.php?excluir=" + id;
    }
}

function selecionarTodas() {
    document.querySelectorAll("#checkboxGroup input[type=checkbox]").forEach(cb => cb.checked = true);
}
</script>

</body>
</html>
