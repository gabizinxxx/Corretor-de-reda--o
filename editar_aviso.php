<?php
session_start();
require "conexao.php";

// Verifica se o ID foi passado na URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Aviso não especificado.");
}

$id = intval($_GET['id']);

// Busca o aviso no banco
$stmt = $conn->prepare("SELECT * FROM notificacoes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$aviso = $result->fetch_assoc();

if (!$aviso) {
    die("Aviso não encontrado.");
}

// Busca turmas e as que estão marcadas
$turmas = $conn->query("SELECT * FROM turmas ORDER BY nome_turma ASC")->fetch_all(MYSQLI_ASSOC);
$turmasMarcadas = [];
$res = $conn->prepare("SELECT id_turma FROM notificacoes_turmas WHERE id_notificacao = ?");
$res->bind_param("i", $id_aviso);
$res->execute();
$q = $res->get_result();
while ($row = $q->fetch_assoc()) $turmasMarcadas[] = $row['id'];
$res->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Aviso</title>
<style>
body { font-family: Arial, sans-serif; background-color: #0d1117; color: white; margin: 0; padding: 0; }
main { padding: 30px; max-width: 800px; margin: auto; background-color: #161b22; border-radius: 10px; }
h1 { color: #00ffff; }
input, textarea { width: 100%; padding: 10px; margin: 8px 0; border-radius: 8px; border: none; background-color: #0d1117; color: white; border: 1px solid #30363d; }
.checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; }
.checkbox-item { background: #0d1117; padding: 8px 12px; border-radius: 8px; border: 1px solid #30363d; }
button { background-color: #1f6feb; color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer;
margin-bottom: 10px;
}
button:hover { background-color: #00ffff; color: #0d1117; }
a { color: #00ffff; text-decoration: none; }

header { background-color: #161b22; padding: 15px 25px; display: flex; justify-content: space-between; align-items: center;  }
header h1 { margin: 0; color: #00ffff; }
header a { color: white; text-decoration: none; background-color: #1f6feb; padding: 8px 15px; border-radius: 20px; }
header a:hover { background-color: #00ffff; color: #0d1117; }
main { padding: 30px; max-width: 800px; margin: auto; }

</style>
</head>
<body>

<main>
    <header>
        <h1>Editar Aviso</h1>
<a class="voltar" href="avisos.php">Voltar</a>
    </header>

<form action="salvar_edicao.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $aviso['id']; ?>">
    <input type="text" name="titulo" value="<?php echo htmlspecialchars($aviso['titulo']); ?>" required>
    <textarea name="mensagem" rows="5" required><?php echo htmlspecialchars($aviso['mensagem']); ?></textarea>

    <h3>Selecione as turmas:</h3>
    <button type="button" onclick="selecionarTodas()">Selecionar todas</button>
    <div class="checkbox-group" id="checkboxGroup">
        <?php foreach ($turmas as $turma): ?>
            <label class="checkbox-item">
                <input type="checkbox" name="turmas[]" value="<?php echo $turma['id']; ?>" 
                    <?php if (in_array($turma['id'], $turmasMarcadas)) echo "checked"; ?>>
                <?php echo htmlspecialchars($turma['nome_turma']); ?>
            </label>
        <?php endforeach; ?>
    </div>

    <h3>Imagem (opcional):</h3>
    <input type="file" name="imagem" accept="image/*">
    <?php if (!empty($aviso['imagem'])): ?>
        <p>Imagem atual:</p>
        <img src="data:image/jpeg;base64,<?php echo $aviso['imagem']; ?>" alt="Imagem" style="max-width:200px;border-radius:8px;">
    <?php endif; ?>

    <br><br>
    <button type="submit">Salvar alterações</button>
    <a href="avisos.php">Cancelar</a>
</form>
</main>

<script>
function selecionarTodas() {
    document.querySelectorAll("#checkboxGroup input[type=checkbox]").forEach(cb => cb.checked = true);
}
</script>

</body>
</html>
