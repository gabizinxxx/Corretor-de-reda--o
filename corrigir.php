<?php
if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST["msg"])) {
    $redacao = trim($_POST["msg"]);
    $numPalavras = str_word_count($redacao);

    if ($numPalavras < 10) {
        $texto = "Sua mensagem é muito curta para ser analisada. Envie algo mais detalhado.";
    } else {
        $apiKey = "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";

        $data = [
            "model" => "gpt-4o-mini",
            "messages" => [
                ["role" => "system", "content" => "Você é um corretor de redações do  ENEM. Avalie, dê nota pelas 5 competências (cada uma de 0 a 200), ajude a melhorar e dê nota a final de 0 a 1000 (somando as notas obtidas em cada competencia)."],
                ["role" => "user", "content" => $redacao]
            ]
        ];

        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer $apiKey"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $resposta = json_decode($response, true);
        $texto = $resposta["choices"][0]["message"]["content"] ?? "Erro ao gerar resposta.";
    }

    // Redireciona de volta para index.php com a resposta na URL
    header("Location: index.php?resposta=" . urlencode($texto));
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>
