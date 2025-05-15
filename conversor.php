<?php
// Redireciona se não for POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.html");
    exit();
}

// Habilita exibição de erros (desativar em produção)
ini_set('display_errors', 1);
error_reporting(E_ALL);


$simbolos = [
    'BRL' => 'R$', 'USD' => 'US$', 'EUR' => '€', 'GBP' => '£', 'JPY' => '¥',
    'CAD' => 'C$', 'AUD' => 'A$', 'CNY' => '¥', 'CHF' => 'CHF', 'ARS' => '$'
];

$nomes_moedas = [
    'BRL' => 'Real Brasileiro', 'USD' => 'Dólar Americano', 'EUR' => 'Euro', 'GBP' => 'Libra Esterlina',
    'JPY' => 'Iene Japonês', 'CAD' => 'Dólar Canadense', 'AUD' => 'Dólar Australiano',
    'CNY' => 'Yuan Chinês', 'CHF' => 'Franco Suíço', 'ARS' => 'Peso Argentino'
];

// Filtra os dados
$valor = filter_input(INPUT_POST, 'valor', FILTER_VALIDATE_FLOAT);
$moeda_origem = $_POST['moeda_origem'] ?? '';
$moeda_destino = $_POST['moeda_destino'] ?? '';

// Validação
if ($valor === false || $valor <= 0 || !isset($simbolos[$moeda_origem]) || !isset($simbolos[$moeda_destino])) {
    $erro = "Dados inválidos enviados!";
} else {
    try {
        // Consulta a API de câmbio
        $api_url = "https://economia.awesomeapi.com.br/json/last/{$moeda_origem}-{$moeda_destino}";
        $response = @file_get_contents($api_url);
        $data = json_decode($response, true);

        if (!$data || !isset($data["{$moeda_origem}{$moeda_destino}"]['bid'])) {
            throw new Exception("Não foi possível obter a cotação.");
        }

        $cotacao = (float)$data["{$moeda_origem}{$moeda_destino}"]['bid'];
        $valor_convertido = $valor * $cotacao;

    } catch (Exception $e) {
        $erro = "Erro ao obter cotações: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado da Conversão</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #747677;
        }
        h1 { color: #2c3e50; text-align: center; }
        .result-box, .error-box {
            background: #f2f3f2;
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .error-box { background: #ffebee; color: #c62828; }
        .rate-info { font-size: 0.9em; color: #546e7a; margin-top: 10px; }
        .btn-voltar {
            display: inline-block;
            background: #5c6bc0;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn-voltar:hover { background: #3949ab; }
        footer {
            text-align: center;
            margin-top: 40px;
            color: #e0e0e0;
            font-size: 0.9em;
        }
        .currency-result {
            font-size: 1.5em;
            font-weight: bold;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <main>
        <h1>💱 Resultado da Conversão</h1>

        <?php if (isset($erro)): ?>
            <div class="error-box">
                <h2>Erro</h2>
                <p><?= htmlspecialchars($erro) ?></p>
            </div>
        <?php else: ?>
            <div class="result-box">
                <h2>Conversão de <?= $nomes_moedas[$moeda_origem] ?> para <?= $nomes_moedas[$moeda_destino] ?></h2>

                <div class="currency-result">
                    <?= $simbolos[$moeda_origem] ?> <?= number_format($valor, 2, ',', '.') ?> =
                    <?= $simbolos[$moeda_destino] ?> <?= number_format($valor_convertido, 2, ',', '.') ?>
                </div>

                <p class="rate-info">
                    Taxa de câmbio: 1 <?= $moeda_origem ?> = <?= number_format($cotacao, 6, ',', '.') ?> <?= $moeda_destino ?>
                </p>

                <p class="rate-info">
                    Atualizado em: <?= date('d/m/Y H:i') ?> (fonte: AwesomeAPI)
                </p>
            </div>
        <?php endif; ?>

        <a href="index.html" class="btn-voltar">← Nova Conversão</a>
    </main>

    <footer>
        <p>Desenvolvido por Tatiana Kami | © <?= date('Y') ?> Todos os direitos reservados</p>
    </footer>
</body>
</html>
