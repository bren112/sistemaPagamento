<?php
// Carregar configuração com o access token
$config = require_once '../config.php';
$accesstoken = $config['accesstoken'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = "FESTFY";
    $email = "festfy@example.com";
    $cpf = $_POST['cpf'];
    $descricao = $_POST['descricao'];
    $cidade = $_POST['cidade']; // Novo campo cidade
    $idempotency_key = uniqid('idempotency_', true);

    $data = [
        "description" => $descricao . " - " . $cidade, // Concatenando cidade aqui
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
        "transaction_amount" => 41
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
        $ticket_url = $obj->point_of_interaction->transaction_data->ticket_url ?? null;
        if ($ticket_url) {
            header("Location: $ticket_url");
            exit;
        } else {
            echo "Link externo não disponível.<br/>";
        }
    } else {
        echo "Erro: Não foi possível gerar o pagamento PIX. Verifique a resposta da API.";
    }
} else {
    echo '
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>FESTFY - Pagamento PIX</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="background-animation">
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
                <div class="shape shape-4"></div>
            </div>
        </div>
        
        <div class="container">
            <div class="form-container">
                <div class="header">
                    <div class="logo-container">
                        <img src="./hallow.jpg" alt="FESTFY" class="logo">
                    </div>
                    <h1 class="title">
                        <i class="fas fa-qrcode"></i>
                        Pagamento PIX
                    </h1>
                    <p class="subtitle">Complete seus dados para finalizar o pagamento</p>
                </div>
                
                <form method="POST" action="" class="payment-form">
                    <div class="input-group">
                        <label for="cpf">
                            <i class="fas fa-id-card"></i>
                            CPF
                        </label>
                        <input 
                            type="text" 
                            id="cpf" 
                            name="cpf" 
                            required 
                            placeholder="000.000.000-00" 
                            autocomplete="off" 
                            inputmode="numeric"
                            class="input-field"
                        >
                    </div>

                    <div class="input-group">
                        <label for="descricao">
                            <i class="fas fa-user"></i>
                            Nome Completo & Telefone
                        </label>
                        <textarea 
                            id="descricao" 
                            name="descricao" 
                            required 
                            placeholder="Ex: João da Silva 11999998888" 
                            rows="2" 
                            class="input-field textarea-field"
                        ></textarea>
                    </div>

                    <div class="input-group">
                        <label for="cidade">
                            <i class="fas fa-map-marker-alt"></i>
                            Sua Cidade
                        </label>
                        <input 
                            type="text" 
                            id="cidade" 
                            name="cidade" 
                            required 
                            placeholder="Ex: São Paulo" 
                            autocomplete="off"
                            class="input-field"
                        >
                    </div>

                    <button type="submit" class="submit-btn">
                        <i class="fab fa-pix"></i>
                        Realizar Pagamento PIX
                        <span class="btn-shine"></span>
                    </button>
                </form>

                <div class="security-info">
                    <i class="fas fa-shield-alt"></i>
                    <span>Pagamento 100% seguro via Mercado Pago</span>
                </div>
            </div>
        </div>
    </body>
    </html>';
}
?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: #0a0a0a;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    position: relative;
    overflow-x: hidden;
}

.background-animation {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0f0f0f 100%);
    z-index: -2;
}

.background-animation::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 80%, rgba(120, 119, 198, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(120, 219, 255, 0.1) 0%, transparent 50%);
    animation: backgroundPulse 8s ease-in-out infinite alternate;
}

@keyframes backgroundPulse {
    0% { opacity: 0.3; }
    100% { opacity: 0.6; }
}

.floating-shapes {
    position: absolute;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: -1;
}

.shape {
    position: absolute;
    border-radius: 50%;
    background: linear-gradient(45deg, rgba(233, 200, 13, 0.1), rgba(255, 119, 198, 0.1));
    animation: float 6s ease-in-out infinite;
}

.shape-1 {
    width: 80px;
    height: 80px;
    top: 10%;
    left: 10%;
    animation-delay: 0s;
}

.shape-2 {
    width: 60px;
    height: 60px;
    top: 70%;
    right: 10%;
    animation-delay: 2s;
}

.shape-3 {
    width: 100px;
    height: 100px;
    bottom: 20%;
    left: 20%;
    animation-delay: 4s;
}

.shape-4 {
    width: 40px;
    height: 40px;
    top: 30%;
    right: 30%;
    animation-delay: 1s;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

.container {
    width: 100%;
    max-width: 480px;
    z-index: 1;
}

.form-container {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 24px;
    padding: 2.5rem;
    box-shadow: 
        0 25px 50px rgba(0, 0, 0, 0.5),
        inset 0 1px 0 rgba(255, 255, 255, 0.1);
    position: relative;
    overflow: hidden;
}

.form-container::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
}

.header {
    text-align: center;
    margin-bottom: 2rem;
}

.logo-container {
    margin-bottom: 1.5rem;
}

.logo {
    width: 120px;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.title {
    font-size: 1.75rem;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.title i {
    color: rgb(255, 166, 0);
    font-size: 1.5rem;
}

.subtitle {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.95rem;
    font-weight: 400;
}

.payment-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.input-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.input-group label {
    color:rgb(255, 255, 255);
    font-weight: 500;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.input-group label i {
    color:rgb(255, 166, 0);
    width: 16px;
}

.input-field {
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    padding: 1rem 1.25rem;
    color:rgb(255, 166, 0);
    font-size: 1rem;
    font-family: inherit;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    backdrop-filter: blur(10px);
}

.input-field::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

.input-field:focus {
    outline: none;
    border-color:rgb(255, 166, 0);
    background: rgba(255, 255, 255, 0.12);
    box-shadow: 
        0 0 0 3px rgba(0, 212, 170, 0.1),
        0 8px 25px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

.textarea-field {
    min-height: 80px;
    resize: vertical;
    font-family: inherit;
}

.submit-btn {
    background: linear-gradient(135deg,rgb(212, 155, 0) 0%,rgb(228, 187, 8) 100%);
    border: none;
    border-radius: 12px;
    padding: 1.25rem 2rem;
    color: #ffffff;
    font-size: 1.1rem;
    font-weight: 600;
    font-family: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 
        0 8px 25px rgba(255, 166, 0, 0.49);
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
    margin-top: 0.5rem;
}

.submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 
        0 12px 35px rgba(255, 166, 0, 0.56);
        inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.submit-btn:active {
    transform: translateY(0);
}

.btn-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.submit-btn:hover .btn-shine {
    left: 100%;
}

.submit-btn i {
    font-size: 1.2rem;
}

.security-info {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.85rem;
}

.security-info i {
    color: rgb(255, 166, 0);
}

/* Responsive Design */
@media (max-width: 480px) {
    .form-container {
        padding: 2rem 1.5rem;
        margin: 1rem;
    }
    
    .title {
        font-size: 1.5rem;
    }
    
    .input-field {
        padding: 0.875rem 1rem;
    }
    
    .submit-btn {
        padding: 1rem 1.5rem;
        font-size: 1rem;
    }
    
    .logo {
        width: 100px;
    }
}

@media (max-width: 360px) {
    .form-container {
        padding: 1.5rem 1rem;
    }
    
    .title {
        font-size: 1.25rem;
        flex-direction: column;
        gap: 0.25rem;
    }
}

/* Loading Animation */
.submit-btn.loading {
    pointer-events: none;
    opacity: 0.8;
}

.submit-btn.loading::after {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    border: 2px solid transparent;
    border-top: 2px solid #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
// Máscara para CPF
document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = value;
});

// Animação de loading no botão
document.querySelector('.payment-form').addEventListener('submit', function() {
    const btn = document.querySelector('.submit-btn');
    btn.classList.add('loading');
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
});

// Animação de entrada
document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.form-container');
    container.style.opacity = '0';
    container.style.transform = 'translateY(30px)';
    
    setTimeout(() => {
        container.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
        container.style.opacity = '1';
        container.style.transform = 'translateY(0)';
    }, 100);
});
</script>
