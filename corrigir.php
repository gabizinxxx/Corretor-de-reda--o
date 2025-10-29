<?php
session_start();

// Garante que o aluno está logado
if (!isset($_SESSION["id"])) {
    die("Erro: aluno não autenticado.");
}

$id_aluno = $_SESSION["id"];
date_default_timezone_set('America/Bahia');

// Conexão com o banco
$conn = new mysqli("sql310.infinityfree.com", "if0_40253039", "pu1OMADnsKH", "if0_40253039_sistema_redacoes");
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


// API Key OpenAI
$apiKey = "";

// Captura dados do formulário
$textoAluno = trim($_POST["msg"] ?? "");
$modoEnvio = $_POST["modo_envio"] ?? "professora"; // 'professora' ou 'ia'

$temTexto = !empty($textoAluno);
$temImagem = isset($_FILES["redacao"]) && $_FILES["redacao"]["error"] === 0;

if (!$temTexto && !$temImagem) {
    $textoResposta = "Envie uma redação em texto ou imagem para correção.";
} else {
    $mensagemUsuario = [];

    // Se enviar imagem, pega Base64
    $imagemBase64 = null;
    if ($temImagem) {
        $caminhoTemp = $_FILES["redacao"]["tmp_name"];
        $imagemBase64 = base64_encode(file_get_contents($caminhoTemp));
        $tipo = mime_content_type($caminhoTemp);

        $mensagemUsuario[] = [
            "type" => "image_url",
            "image_url" => [
                "url" => "data:$tipo;base64,$imagemBase64"
            ]
        ];
    }

    if ($temTexto) {
        $mensagemUsuario[] = [
            "type" => "text",
            "text" => $textoAluno
        ];
    }

    // Corpo da requisição GPT
    $data = [
        "model" => "gpt-4o-mini",
        "messages" => [
            [
                "role" => "system",
                "content" => "Você é a Lin, um corretor de redações do ENEM. Avalie a redação do aluno e retorne apenas um JSON válido com os campos:
{
  \"comp1\": 0,
  \"comp2\": 0,
  \"comp3\": 0,
  \"comp4\": 0,
  \"comp5\": 0,
  \"nota_total\": 0,
  \"feedback\": \"comentário detalhado sobre como melhorar\"
}
Cada competência deve ser de 0 a 200 e a nota_total de 0 a 1000."
            ],
            [
                "role" => "user",
                "content" => $mensagemUsuario
            ]
        ]
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: " . "Bearer $apiKey"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $response = curl_exec($ch);
    curl_close($ch);

    $respostaGPT = json_decode($response, true);
    $conteudoGPT = $respostaGPT["choices"][0]["message"]["content"] ?? "";

    preg_match('/\{.*\}/s', $conteudoGPT, $matches);
    $json = isset($matches[0]) ? json_decode($matches[0], true) : null;

    if ($json) {
        $comp1 = (int)($json["comp1"] ?? 0);
        $comp2 = (int)($json["comp2"] ?? 0);
        $comp3 = (int)($json["comp3"] ?? 0);
        $comp4 = (int)($json["comp4"] ?? 0);
        $comp5 = (int)($json["comp5"] ?? 0);
        $nota_total = (int)($json["nota_total"] ?? 0);
        $feedback = $json["feedback"] ?? "";
    } else {
        $comp1 = $comp2 = $comp3 = $comp4 = $comp5 = $nota_total = 0;
        $feedback = $conteudoGPT;
    }

    $data_envio = date("Y-m-d H:i:s");

    // **Salvar no banco apenas se modo_envio for professora**
    if ($modoEnvio === "professora") {
        $sqlCheck = "SELECT id FROM redacoes WHERE id_aluno = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param("i", $id_aluno);
        $stmtCheck->execute();
        $stmtCheck->store_result();

        if ($stmtCheck->num_rows > 0) {
            $sqlUpdate = "UPDATE redacoes SET texto=?, imagem=?, comp1=?, comp2=?, comp3=?, comp4=?, comp5=?, nota_total=?, feedback=?, data_envio=? WHERE id_aluno=?";
            $stmt = $conn->prepare($sqlUpdate);
            $stmt->bind_param(
                "ssiiiiisssi",
                $textoAluno,
                $imagemBase64,
                $comp1,
                $comp2,
                $comp3,
                $comp4,
                $comp5,
                $nota_total,
                $feedback,
                $data_envio,
                $id_aluno
            );
        } else {
            $sqlInsert = "INSERT INTO redacoes (id_aluno, texto, imagem, comp1, comp2, comp3, comp4, comp5, nota_total, feedback, data_envio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sqlInsert);
            $stmt->bind_param(
                "issiiiiisss",
                $id_aluno,
                $textoAluno,
                $imagemBase64,
                $comp1,
                $comp2,
                $comp3,
                $comp4,
                $comp5,
                $nota_total,
                $feedback,
                $data_envio
            );
        }
        $stmt->execute();
        $stmt->close();
        $stmtCheck->close();
    }

    // Monta a resposta de acordo com o modo
    if ($modoEnvio === "ia") {
        // Modo IA: apenas feedback puro
        $textoResposta = $feedback;
    } else {
        // Modo professora: feedback + competências + nota
        $textoResposta = "Feedback:\n$feedback\n\n";
        $textoResposta .= "Competências:\n";
        $textoResposta .= "C1 (domínio da norma): $comp1\nC2 (compreensão do tema): $comp2\nC3 (coerência e coesão): $comp3\nC4 (argumentação): $comp4\nC5 (proposta de intervenção): $comp5\n";
        $textoResposta .= "Nota final: $nota_total";
    }
}

$conn->close();

// Retorna para o corretor
header("Location: corretor.php?resposta=" . urlencode($textoResposta));
exit;
?>
