<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
    }

    .header .title {
      background-color: white;
      color: #0d1117;
      padding: 10px 20px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 25px;
    }

    /* ===== CHAT NO CABEÇALHO ===== */
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
      min-height: 550px;
      background-color: #161b22;
      color: #ffffffff;
      border-radius: 20px;
      padding: 20px;
      max-width: 700px;
      overflow-y: auto;
      border: 1px solid #30363d;
      box-shadow: 0 0 10px #0d1117;
    }

    /* ===== RODAPÉ ===== */
    .footer {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
      background-color: #0d1117;
      flex-direction: column;
      gap: 5px;
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
    }

    .input-box input {
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
      margin-left: 10px;
      cursor: pointer;
      transition: 0.3s;
    }

    .input-box button:hover {
      background-color: #1f6feb;
    }

    .footer small {
      color: #888;
      font-size: 12px;
      text-align: center;
    }

    .header .logo{
    width: 50px;
    margin-top: 10px;

    }

    /* ===== RESPONSIVIDADE ===== */
    @media (max-width: 768px) {
      .header {
        flex-direction: column;
        gap: 10px;
      }


            .header .logo{

        margin-right: 140px;
      }

      .header .chat img {
        width: 70px;
        height: 70px;
      }

      .header .chat .message {
        font-size: 14px;
      }

      .area {
        max-width: 100%;
        font-size: 15px;
      }

      .footer .input-box {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
      }

      .footer .input-box button {
        width: 100%;
      }
    }
  </style>
</head>

<body>
  <form method="POST" action="corrigir.php">
    <div class="header">
      <div class="title">CHAT BOT</div>
      <div class="logo"><img src="logocetep.png" alt=""></div>
      <div class="chat">
        <img src="https://previews.123rf.com/images/goodzone95/goodzone951803/goodzone95180300023/96668201-chatbot-icon-cute-robot-working-behind-laptop-modern-bot-sign-design-smiling-customer-service.jpg" alt="Chat bot" />
        <div class="message">OLÁ, EM QUE POSSO AJUDAR?</div>
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
      <div class="input-box">
        <input id="msg" name="msg" placeholder="Escreva sua redação" type="text"/>
        <button type="submit" id="submit">Enviar</button>
      </div>
      <small>Todos os direitos reservados © CETEP Medeiros Neto - BA 2025 |</small>
    </div> 
  </form>
</body>
</html>
