<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["tipo"] !== "aluno") {
    header("Location: index.php");
    exit();
}

$nomeAluno = $_SESSION["nome"];

require "conexao.php";

$id_aluno = $_SESSION["id"];
$stmt = $conn->prepare("SELECT nome FROM alunos WHERE id = ?");
$stmt->bind_param("i", $id_aluno);
$stmt->execute();
$result = $stmt->get_result();
$aluno = $result->fetch_assoc();
$nome_aluno = $aluno ? $aluno['nome'] : 'Aluno';
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
<link rel="icon" href="icon.png" type="image/png">
<title>Corretor de redações</title>
<style>
body {
  margin: 0;
  padding: 0;
  background-color: #0d1117;
  font-family: Arial, sans-serif;
  color: white;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* ===== HEADER ===== */
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px 25px;
  background-color: #0d1117;
  flex-wrap: wrap;
  border-bottom: 1px solid #30363d;
  position: relative;
}

.header .title {
  background-color: white;
  color: #0d1117;
  padding: 10px 20px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 25px;
}

.header .chat {
  display: flex;
  align-items: center;
  gap: 15px;
}

.header .chat img {
  width: 60px;
  height: 60px;
  border-radius: 50%;
}

.header .chat .message {
  background-color: #d1d5da;
  color: #000;
  padding: 10px 15px;
  border-radius: 20px;
  font-weight: bold;
  font-size: 15px;
}

/* MENU ☰ */
.menu-toggle {
  font-size: 28px;
  cursor: pointer;
  color: white;
  user-select: none;
}

.menu {
  display: none;
  flex-direction: column;
  position: absolute;
  top: 70px;
  right: 25px;
  background-color: #161b22;
  padding: 10px;
  border-radius: 10px;
  border: 1px solid #30363d;
  z-index: 1000;
}

.menu a {
  color: white;
  text-decoration: none;
  background-color: #1f6feb;
  padding: 8px 15px;
  border-radius: 20px;
  margin-bottom: 8px;
  font-weight: bold;
  transition: 0.3s;
}

.menu a:hover {
  background-color: #00ffff;
  color: #0d1117;
}

/* ===== CONTEÚDO ===== */
.content {
  flex: 1;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  flex-wrap: wrap;
  padding: 30px;
}

.area {
  flex: 1;
  min-height: 450px;
  background-color: #161b22;
  color: #ffffffff;
  border-radius: 20px;
  padding: 20px;
  max-width: 700px;
  overflow-y: auto;
  border: 1px solid #30363d;
  box-shadow: 0 0 10px #0d1117;
}

/* ===== FOOTER ===== */
.footer {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  background-color: #0d1117;
  flex-direction: column;
  gap: 10px;
}

.modo-envio {
  text-align: center;
}

.modo-envio label {
  font-weight: bold;
  margin-right: 10px;
}

.modo-envio select {
  background-color: #161b22;
  color: white;
  border: 1px solid #30363d;
  border-radius: 20px;
  padding: 8px 15px;
  font-size: 15px;
  outline: none;
  cursor: pointer;
  transition: 0.3s;
}

.modo-envio select:hover {
  border-color: #00ffff;
}

.input-box {
  display: flex;
  align-items: center;
  background-color: white;
  padding: 10px 15px;
  border-radius: 20px;
  width: 90%;
  max-width: 800px;
  box-sizing: border-box;
  gap: 10px;
}

.input-box input[type="text"] {
  border: none;
  outline: none;
  flex: 1;
  font-size: 16px;
}

.input-box button {
  background-color: #0d1117;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 20px;
  cursor: pointer;
  transition: 0.3s;
}

.input-box button:hover {
  background-color: #1f6feb;
}

.file-label {
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #0d1117;
  color: white;
  font-weight: bold;
  font-size: 18px;
  width: 35px;
  height: 35px;
  border-radius: 50%;
  cursor: pointer;
  transition: 0.3s;
}

.file-label:hover {
  background-color: #1f6feb;
}

.footer small {
  color: #888;
  font-size: 12px;
  text-align: center;
}

.header .logo {
  width: 50px;
  margin-top: 10px;
}

/* ===== ANIMAÇÃO DE CORREÇÃO ===== */
#loading-overlay {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(13, 17, 23, 0.95);
  backdrop-filter: blur(3px);
  justify-content: center;
  align-items: center;
  flex-direction: column;
  z-index: 9999;
}

.loader {
  border: 6px solid #30363d;
  border-top: 6px solid #00ffff;
  border-radius: 50%;
  width: 70px;
  height: 70px;
  animation: spin 1s linear infinite;
}

.loading-text {
  color: #00ffff;
  font-size: 18px;
  font-weight: bold;
  margin-top: 20px;
  animation: pulse 1.5s infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@keyframes pulse {
  0%, 100% { opacity: 0.6; }
  50% { opacity: 1; }
}

@media (max-width: 768px) {
  .header { gap: 10px; }
  .header .logo { margin-right: 140px; }
  .header .chat img { width: 70px; height: 70px; }
  .header .chat .message { font-size: 14px; }
  .area { max-width: 100%; font-size: 15px;
  min-height: 300px; }
  .footer .input-box { flex-direction: column; align-items: stretch; gap: 10px; }
  .footer .input-box button { width: 100%; }
}

/* ===== RESPONSIVIDADE ===== */
@media (max-width: 1100px) {
  .header { gap: 10px; }
  .header .logo { margin-right: 140px; }
  .header .chat img { width: 70px; height: 70px; }
  .header .chat .message { font-size: 14px; }
  .area { max-width: 100%; font-size: 15px;
  min-height: 320px; }
  .footer .input-box { flex-direction: column; align-items: stretch; gap: 10px; }
  .footer .input-box button { width: 100%; }
}
</style>
</head>
<body>
<form method="POST" action="corrigir.php" enctype="multipart/form-data">
  <div class="header">
    <div class="title">LinIA</div>
    <div class="logo"><img src="logocetep.png" alt=""></div>
    <div class="chat">
      <img src="https://previews.123rf.com/images/goodzone95/goodzone951803/goodzone95180300023/96668201-chatbot-icon-cute-robot-working-behind-laptop-modern-bot-sign-design-smiling-customer-service.jpg" alt="Chat bot" />
      <div class="message">Olá, <?php echo htmlspecialchars($nome_aluno); ?>! Em que posso ajudar?</div>
    </div>

    <!-- Ícone do menu ☰ -->
    <div class="menu-toggle" onclick="toggleMenu()">☰</div>

    <!-- Menu oculto -->
    <div class="menu">
      <a href="historico.php">Histórico de Redações</a>
      <a href="notificacoes.php">🔔 Avisos</a>
      <a href="logout.php">Sair</a>
    </div>
  </div>

  <div class="content">
    <div id="output" class="area">
      <?php 
        if (isset($_GET['resposta'])) {
            echo nl2br(htmlspecialchars($_GET['resposta']));
        } else {
            echo "A correção aparecerá aqui.";
        }
      ?>
    </div>
  </div>

  <div class="footer">
    <div class="modo-envio">
      <label for="modo_envio">Modo:</label>
      <select name="modo_envio" id="modo_envio">
        <option value="ia">Correção/Dúvidas (não enviar para o professor (a))</option>
        <option value="professora">Enviar para o professor (a)</option>
      </select>
    </div>

    <div class="input-box">
      <input id="msg" name="msg" placeholder="Escreva sua redação ou envie foto" type="text"/>
      <label for="redacaoFile" class="file-label">+</label>
      <input type="file" id="redacaoFile" name="redacao" accept="image/*" style="display:none;">
      <button type="submit" id="submit">Enviar</button>
    </div>
    <small>Todos os direitos reservados © CETEP Medeiros Neto - BA 2025 |</small>
  </div> 
</form>

<!-- ANIMAÇÃO DE CORREÇÃO -->
<div id="loading-overlay">
  <div class="loader"></div>
  <p class="loading-text">A Lin está corrigindo sua redação...</p>
</div>

<script>
// Abre/fecha menu
function toggleMenu() {
  const menu = document.querySelector('.menu');
  menu.style.display = (menu.style.display === 'flex') ? 'none' : 'flex';
}

// Fecha menu se clicar fora dele
document.addEventListener('click', function(event) {
  const menu = document.querySelector('.menu');
  const toggle = document.querySelector('.menu-toggle');
  if (!menu.contains(event.target) && !toggle.contains(event.target)) {
    menu.style.display = 'none';
  }
});

// Mostra animação de carregamento ao enviar formulário
const form = document.querySelector("form");
const overlay = document.getElementById("loading-overlay");
form.addEventListener("submit", () => {
  overlay.style.display = "flex";
});
</script>
</body>
</html>
