<?php
$config = require_once '../config.php';
$accesstoken = $config['accesstoken'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = "FESTFY";
    $email = "festfy@example.com";

    $cpf = $_POST['cpf'];
    $descricao = $_POST['descricao'];
    $idempotency_key = uniqid('idempotency_', true);

    $data = [
        "description" => $descricao,
        "external_reference" => "MP0001",
        "payer" => [
            "email" => $email,
            "identification" => [
                "type" => "CPF",
                "number" => $cpf
            ],
            "first_name" => $nome
        ],
        "payment_method_id" => "pix",
        "transaction_amount" => 0.01
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.mercadopago.com/v1/payments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accesstoken,
            'X-Idempotency-Key: ' . $idempotency_key
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

    if (isset($obj->id) && isset($obj->point_of_interaction)) {
        $qr_base64 = $obj->point_of_interaction->transaction_data->qr_code_base64 ?? null;
        $qr_text = $obj->point_of_interaction->transaction_data->qr_code ?? null;

        if ($qr_base64 && $qr_text) {
            echo '<div class="pix-container">';
            echo '<h2>Escaneie o QR Code ou copie o código Pix</h2>';
            echo '<img src="data:image/png;base64,' . $qr_base64 . '" alt="QR Code Pix" class="pix-qr">';
            echo '<p><strong>Copia e Cola:</strong></p>';
            echo '<textarea id="pixCode" readonly>' . htmlspecialchars($qr_text) . '</textarea><br>';
            echo '<button onclick="copiarPix()">Copiar Código</button>';
            echo '</div>';
        } else {
            echo "<p class='error'>QR Code ou código Pix não disponível.</p>";
        }
    } else {
        echo "<p class='error'>Erro: Não foi possível gerar o pagamento PIX. Verifique a resposta da API.</p>";
    }
} else {
    echo '<div class="form-container">
            <div class="bandeirinhas">
                <span>🎉</span><span>🎊</span><span>🎈</span><span>🌽</span><span>🔥</span><span>🎆</span>
            </div>
            <h2>Pagamento via PIX - Festa Junina</h2>
            <form method="POST" action="">
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" required placeholder="Seu CPF"><br>

                <label for="descricao">Nome completo + telefone:</label>
                <textarea id="descricao" name="descricao" required placeholder="Ex: João da Silva 11999998888" rows="2" cols="40"></textarea><br>

                <input type="submit" value="Realizar Pagamento">
            </form>
            <div class="fogueira"></div>
        </div>';
}
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Baloo+2&display=swap');

body {
    font-family: 'Baloo 2', cursive, Arial, sans-serif;
    background: linear-gradient(135deg, #FFEDBC 0%, #FFAA5A 100%);
    margin: 0;
    padding: 0;
    text-align: center;
    color: #5D2E0F;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.form-container, .pix-container {
    background: #FCEBB6;
    border: 6px solid #C54E1E;
    border-radius: 20px;
    padding: 40px 30px;
    max-width: 480px;
    width: 90%;
    box-shadow: 0 10px 15px rgba(197, 78, 30, 0.7);
    position: relative;
}

/* Bandeirinhas animadas */
.bandeirinhas {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-bottom: 25px;
    font-size: 2rem;
    animation: swing 3s ease-in-out infinite alternate;
}

@keyframes swing {
    0% { transform: rotate(-8deg); }
    100% { transform: rotate(8deg); }
}

h2 {
    font-size: 2rem;
    margin-bottom: 30px;
    color: #B8440B;
    text-shadow: 1px 1px 0 #FFDBA3;
}

/* Inputs */
form label {
    display: block;
    font-weight: 700;
    margin-bottom: 8px;
    font-size: 1.1rem;
    color: #A64B00;
    text-align: left;
}

input[type="text"], textarea {
    width: 100%;
    padding: 12px;
    font-size: 1.1rem;
    border-radius: 12px;
    border: 3px solid #E35B00;
    font-weight: 600;
    font-family: 'Baloo 2', cursive;
    resize: none;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus, textarea:focus {
    border-color: #D14000;
    outline: none;
    box-shadow: 0 0 8px 2px #FF7733;
}

input[type="submit"] {
    background: linear-gradient(45deg, #C54E1E, #FF6F00);
    color: #FFF6E5;
    font-weight: 900;
    font-size: 1.25rem;
    border: none;
    border-radius: 20px;
    padding: 15px 0;
    cursor: pointer;
    box-shadow: 0 5px 10px rgba(255, 111, 0, 0.6);
    transition: background 0.3s ease;
    margin-top: 20px;
}

input[type="submit"]:hover {
    background: linear-gradient(45deg, #FF8121, #FF8C3D);
    box-shadow: 0 8px 15px rgba(255, 139, 61, 0.8);
}

/* PIX container */
.pix-container {
    text-align: center;
}

.pix-qr {
    max-width: 260px;
    border: 8px solid #D96B1F;
    border-radius: 18px;
    box-shadow: 0 0 15px #FFA439;
    margin-bottom: 20px;
}

.pix-container textarea {
    width: 100%;
    max-width: 400px;
    height: 100px;
    font-size: 1.1rem;
    padding: 15px;
    border-radius: 15px;
    border: 3px solid #E35B00;
    resize: none;
    font-family: 'Baloo 2', cursive;
    font-weight: 700;
    color: #5D2E0F;
    margin-bottom: 15px;
    background: #FFF5E1;
}

.pix-container button {
    background: #D54C0F;
    color: #FFF6E5;
    font-weight: 800;
    font-size: 1.15rem;
    padding: 12px 30px;
    border: none;
    border-radius: 18px;
    cursor: pointer;
    box-shadow: 0 5px 10px rgba(213, 76, 15, 0.8);
    transition: background 0.3s ease;
}

.pix-container button:hover {
    background: #FF7722;
    box-shadow: 0 8px 18px rgba(255, 119, 34, 0.9);
}

/* Error message */
.error {
    color: #D13B00;
    font-weight: 700;
    font-size: 1.2rem;
    background: #FFDDC1;
    padding: 12px 20px;
    border-radius: 15px;
    margin-top: 20px;
    box-shadow: 0 0 8px #D13B00;
}

/* Fogueira estilizada */
.fogueira {
    position: absolute;
    bottom: -70px;
    left: 50%;
    transform: translateX(-50%);
    width: 160px;
    height: 100px;
    background: linear-gradient(45deg, #FF4500, #FFA500);
    border-radius: 50% 50% 40% 40% / 70% 70% 30% 30%;
    box-shadow: 0 0 40px 10px #FF7A00;
    animation: flicker 1.5s infinite alternate;
    filter: drop-shadow(0 0 6px #FF9B00);
}

@keyframes flicker {
    0% { transform: translateX(-50%) scale(1); opacity: 1; }
    100% { transform: translateX(-48%) scale(1.05); opacity: 0.85; }
}
</style>

<script>
function copiarPix() {
    const texto = document.getElementById("pixCode");
    texto.select();
    texto.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("Código Pix copiado com sucesso!");
}
</script>
