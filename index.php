<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - PagFestfy</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f6fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        #logo {
            width: 300px;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 25px;
            color: #333;
        }

        #cont {
            width: 100%;
            max-width: 300px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        a {
            display: inline-block;
            text-decoration: none;
            background-image: linear-gradient(45deg, #f39c12, #e67e22);
            color: white;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 18px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        a:hover {
            background-image: linear-gradient(45deg, #e67e22, #f39c12);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        /* Responsividade */
        @media (max-width: 480px) {
            h1 {
                font-size: 22px;
            }

            a {
                font-size: 16px;
                padding: 10px 20px;
            }

            #cont {
                max-width: 90%;
            }

            .container {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="./source/logo.png" alt="Logo" id="logo">
        <h1>pagfestfy 👻🎃</h1>
        <img id="cont" src="./source/imgPag.png" alt="Imagem Pagamento">
        <a href="./api/pix.php">Adquirir Entrada</a>
    </div>
</body>
</html>
