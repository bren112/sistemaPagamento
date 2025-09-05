<?php
$config = require_once '../config.php';
$accesstoken = $config['accesstoken'];

$limit = 500;
$offset = 0;
$pagamentos_aprovados = [];

$data_minima = '2025-09-04';

do {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.mercadopago.com/v1/payments/search?limit=$limit&offset=$offset",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accesstoken
        ],
    ]);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        echo "Erro no cURL: " . curl_error($curl);
        curl_close($curl);
        exit;
    }
    curl_close($curl);

    $obj = json_decode($response);
    if (!isset($obj->results) || empty($obj->results)) {
        break;
    }

    foreach ($obj->results as $payment) {
        $descricao = isset($payment->description) ? strtolower($payment->description) : '';

        $palavras_bloqueadas = [
            'nada'
        ];

        $ignorar = false;
        foreach ($palavras_bloqueadas as $palavra) {
            if (stripos($descricao, $palavra) !== false) {
                $ignorar = true;
                break;
            }
        }

        if (
            !$ignorar &&
            $payment->status === 'approved' &&
            strtotime($payment->date_approved) >= strtotime($data_minima)
        ) {
            // Verifica se tem descrição válida (nome)
            if (!empty($payment->description)) {
                $desc = $payment->description;
                if (preg_match('/^(.*?)(\d+)$/', $desc, $matches)) {
                    $desc_texto = trim($matches[1]);
                } else {
                    $desc_texto = trim($desc);
                }
        
                if (!empty($desc_texto)) {
                    $pagamentos_aprovados[] = $payment;
                }
            }
        }
        
    }

    $offset += $limit;
} while (count($obj->results) === $limit);

// Contadores
$total_pessoas = count($pagamentos_aprovados);
$valor_total = 0;
foreach ($pagamentos_aprovados as $pagamento) {
    $valor_total += $pagamento->transaction_amount;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamentos Aprovados</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap');

        body {
            font-family: 'Orbitron', sans-serif;
            background-color: #0f0f0f;
            color: #f1f1f1;
            margin: 30px auto;
            max-width: 960px;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgb(255, 166, 0);
        }

        h2 {
            color:rgb(255, 166, 0);
            text-align: center;
            margin-bottom: 10px;
            text-shadow: 0 0 8px rgb(255, 166, 0);
            font-size: 2.5rem;
        }

        p.contadores {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 25px;
        }

        p.contadores strong {
            color:rgb(255, 166, 0);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #161616;
            border: 2px solid rgb(255, 166, 0);
            box-shadow: 0 0 15pxrgb(255, 166, 0);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid rgb(255, 166, 0);
            font-size: 1rem;
        }

        th {
            background-color: #121212;
            color: rgb(255, 166, 0);
            font-weight: bold;
            text-shadow: 0 0 5pxrgb(255, 166, 0);
        }

        td {
            color:rgb(255, 255, 255);
        }

        tr:hover {
            background-color: #1f1f1f;
        }

        input[type="text"] {
            width: 100%;
            padding: 14px;
            margin-bottom: 25px;
            background-color: #1a1a1a;
            border: 2px solidrgb(255, 166, 0);
            border-radius: 8px;
            color: rgb(255, 166, 0);
            font-size: 1.1rem;
            text-shadow: 0 0 3pxrgb(255, 166, 0);
        }

        @media (max-width: 600px) {
            h2 {
                font-size: 1.8rem;
                padding: 0 10px;
            }

            input[type="text"] {
                font-size: 1.2rem;
                padding: 16px;
            }

            table, thead, tbody, th, td, tr {
                display: block;
            }

            tr {
                margin-bottom: 20px;
                border: 1px solidrgb(255, 166, 0);
                border-radius: 10px;
                padding: 15px;
            }

            th {
                display: none;
            }

            td {
                position: relative;
                padding-left: 55%;
                font-size: 1.1rem;
                padding-top: 10px;
                padding-bottom: 10px;
            }

            td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                top: 10px;
                font-weight: bold;
                color:rgb(255, 166, 0);
                font-size: 1rem;
                text-shadow: 0 0 2px rgb(255, 166, 0);
            }
        }
    </style>
</head>
<body>
    <h2>Pagamentos Aprovados 💸💸</h2>
    <br>

    <?php if (!empty($pagamentos_aprovados)): ?>
        <p class="contadores">
            Total de Pessoas: <strong><?= $total_pessoas ?></strong><br>
            Valor Arrecadado: <strong>R$ <?= number_format($valor_total, 2, ',', '.') ?></strong>
        </p>
    <?php endif; ?>

    <input type="text" id="searchInput" placeholder="Buscar por nome ou telefone..." style="width: 20pc;">

    <script>
    document.getElementById('searchInput').addEventListener('input', function() {
        const filtro = this.value.toLowerCase();
        const linhas = document.querySelectorAll('tbody tr');

        linhas.forEach(tr => {
            const nome = tr.children[4]?.textContent.toLowerCase();
            const telefone = tr.children[5]?.textContent.toLowerCase();

            if (filtro === '' || nome.includes(filtro) || telefone.includes(filtro)) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    });
    </script>

    <?php if (!empty($pagamentos_aprovados)): ?>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Meio de Pagamento</th>
                    <th>Infos Pagante</th>
                    <!-- <th>Telefone</th> -->
                </tr>
            </thead>
            <tbody>
            <?php foreach ($pagamentos_aprovados as $payment): ?>
    <?php
        if (isset($payment->description)) {
            $desc = $payment->description;
            if (preg_match('/^(.*?)(\d+)$/', $desc, $matches)) {
                $desc_texto = trim($matches[1]);
                $desc_numero = $matches[2];
            } else {
                $desc_texto = $desc;
                $desc_numero = '';
            }
        } else {
            $desc_texto = '';
            $desc_numero = '';
        }
    ?>

    <?php if (!empty($desc_texto)): ?> <!-- só mostra se houver descrição -->
        <tr>
            <td data-label="Data"><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($payment->date_approved))) ?></td>
            <td data-label="Valor">R$ <?= number_format($payment->transaction_amount, 2, ',', '.') ?></td>
            <td data-label="Status"><?= htmlspecialchars($payment->status) ?></td>
            <td data-label="Meio de Pagamento"><?= htmlspecialchars($payment->payment_method_id) ?></td>
            <td data-label="Nome"><?= htmlspecialchars($desc_texto) ?></td>
            <td data-label="Telefone"><?= htmlspecialchars($desc_numero) ?></td>
        </tr>
    <?php endif; ?>
<?php endforeach; ?>

            </tbody>
        </table>
    <?php else: ?>
        <p style="text-<?php
$config = require_once '../config.php';
$accesstoken = $config['accesstoken'];

$limit = 500;
$offset = 0;
$pagamentos_aprovados = [];

$data_minima = '2025-09-04';

do {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.mercadopago.com/v1/payments/search?limit=$limit&offset=$offset",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $accesstoken
        ],
    ]);

    $response = curl_exec($curl);
    if (curl_errno($curl)) {
        echo "Erro no cURL: " . curl_error($curl);
        curl_close($curl);
        exit;
    }
    curl_close($curl);

    $obj = json_decode($response);
    if (!isset($obj->results) || empty($obj->results)) {
        break;
    }

    foreach ($obj->results as $payment) {
        $descricao = isset($payment->description) ? strtolower($payment->description) : '';

        $palavras_bloqueadas = [
            'nada'
        ];

        $ignorar = false;
        foreach ($palavras_bloqueadas as $palavra) {
            if (stripos($descricao, $palavra) !== false) {
                $ignorar = true;
                break;
            }
        }

        if (
            !$ignorar &&
            $payment->status === 'approved' &&
            strtotime($payment->date_approved) >= strtotime($data_minima)
        ) {
            // Verifica se tem descrição válida (nome)
            if (!empty($payment->description)) {
                $desc = $payment->description;
                if (preg_match('/^(.*?)(\d+)$/', $desc, $matches)) {
                    $desc_texto = trim($matches[1]);
                } else {
                    $desc_texto = trim($desc);
                }
        
                if (!empty($desc_texto)) {
                    $pagamentos_aprovados[] = $payment;
                }
            }
        }
        
    }

    $offset += $limit;
} while (count($obj->results) === $limit);

// Contadores
$total_pessoas = count($pagamentos_aprovados);
$valor_total = 0;
foreach ($pagamentos_aprovados as $pagamento) {
    $valor_total += $pagamento->transaction_amount;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamentos Aprovados</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@500&display=swap');

        body {
            font-family: 'Orbitron', sans-serif;
            background-color: #0f0f0f;
            color: #f1f1f1;
            margin: 30px auto;
            max-width: 960px;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgb(255, 166, 0);
        }

        h2 {
            color:rgb(255, 166, 0);
            text-align: center;
            margin-bottom: 10px;
            text-shadow: 0 0 8px rgb(255, 166, 0);
            font-size: 2.5rem;
        }

        p.contadores {
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 25px;
        }

        p.contadores strong {
            color:rgb(255, 166, 0);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #161616;
            border: 2px solid rgb(255, 166, 0);
            box-shadow: 0 0 15pxrgb(255, 166, 0);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid rgb(255, 166, 0);
            font-size: 1rem;
        }

        th {
            background-color: #121212;
            color: rgb(255, 166, 0);
            font-weight: bold;
            text-shadow: 0 0 5pxrgb(255, 166, 0);
        }

        td {
            color:rgb(255, 255, 255);
        }

        tr:hover {
            background-color: #1f1f1f;
        }

        input[type="text"] {
            width: 100%;
            padding: 14px;
            margin-bottom: 25px;
            background-color: #1a1a1a;
            border: 2px solidrgb(255, 166, 0);
            border-radius: 8px;
            color: rgb(255, 166, 0);
            font-size: 1.1rem;
            text-shadow: 0 0 3pxrgb(255, 166, 0);
        }

        @media (max-width: 600px) {
            h2 {
                font-size: 1.8rem;
                padding: 0 10px;
            }

            input[type="text"] {
                font-size: 1.2rem;
                padding: 16px;
            }

            table, thead, tbody, th, td, tr {
                display: block;
            }

            tr {
                margin-bottom: 20px;
                border: 1px solidrgb(255, 166, 0);
                border-radius: 10px;
                padding: 15px;
            }

            th {
                display: none;
            }

            td {
                position: relative;
                padding-left: 55%;
                font-size: 1.1rem;
                padding-top: 10px;
                padding-bottom: 10px;
            }

            td::before {
                content: attr(data-label);
                position: absolute;
                left: 10px;
                top: 10px;
                font-weight: bold;
                color:rgb(255, 166, 0);
                font-size: 1rem;
                text-shadow: 0 0 2px rgb(255, 166, 0);
            }
        }
    </style>
</head>
<body>
    <h2>Pagamentos Aprovados 💸💸</h2>
    <br>

    <?php if (!empty($pagamentos_aprovados)): ?>
        <p class="contadores">
            Total de Pessoas: <strong><?= $total_pessoas ?></strong><br>
            Valor Arrecadado: <strong>R$ <?= number_format($valor_total, 2, ',', '.') ?></strong>
        </p>
    <?php endif; ?>

    <input type="text" id="searchInput" placeholder="Buscar por nome ou telefone..." style="width: 20pc;">

    <script>
    document.getElementById('searchInput').addEventListener('input', function() {
        const filtro = this.value.toLowerCase();
        const linhas = document.querySelectorAll('tbody tr');

        linhas.forEach(tr => {
            const nome = tr.children[4]?.textContent.toLowerCase();
            const telefone = tr.children[5]?.textContent.toLowerCase();

            if (filtro === '' || nome.includes(filtro) || telefone.includes(filtro)) {
                tr.style.display = '';
            } else {
                tr.style.display = 'none';
            }
        });
    });
    </script>

    <?php if (!empty($pagamentos_aprovados)): ?>
        <table>
        <thead>
    <tr>
        <th>Data</th>
        <th>Valor</th>
        <th>Status</th>
        <th>Meio de Pagamento</th>
        <th>Nome</th>
        <th>Telefone</th>
        <th>Ação</th>
    </tr>
</thead>
<tbody>
<?php foreach ($pagamentos_aprovados as $payment): ?>
<?php
    if (isset($payment->description)) {
        $desc = $payment->description;
        if (preg_match('/^(.*?)(\d+)$/', $desc, $matches)) {
            $desc_texto = trim($matches[1]);
            $desc_numero = $matches[2];
        } else {
            $desc_texto = $desc;
            $desc_numero = '';
        }
    } else {
        $desc_texto = '';
        $desc_numero = '';
    }
?>

<?php if (!empty($desc_texto)): ?>
    <tr>
        <td data-label="Data"><?= htmlspecialchars(date('Y-m-d H:i:s', strtotime($payment->date_approved))) ?></td>
        <td data-label="Valor">R$ <?= number_format($payment->transaction_amount, 2, ',', '.') ?></td>
        <td data-label="Status"><?= htmlspecialchars($payment->status) ?></td>
        <td data-label="Meio de Pagamento"><?= htmlspecialchars($payment->payment_method_id) ?></td>
        <td data-label="Nome"><?= htmlspecialchars($desc_texto) ?></td>
        <td data-label="Telefone"><?= htmlspecialchars($desc_numero) ?></td>
        <td><button class="btn-log">Logar</button></td>
    </tr>
<?php endif; ?>
<?php endforeach; ?>
</tbody>

        </table>
    <?php else: ?>
        <p style="text-align:center; color:#f66;">Nenhum pagamento aprovado encontrado a partir de <?= $data_minima ?>.</p>
    <?php endif; ?>
</body>
</html>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-log').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            const dados = [];
            row.querySelectorAll('td').forEach((td, index) => {
                // ignora a última coluna (o botão)
                if (index < row.children.length - 1) {
                    dados.push(td.textContent.trim());
                }
            });
            console.log("Linha completa:", dados);
        });
    });
});
</script>
align:center; color:#f66;">Nenhum pagamento aprovado encontrado a partir de <?= $data_minima ?>.</p>
    <?php endif; ?>
</body>
</html>
