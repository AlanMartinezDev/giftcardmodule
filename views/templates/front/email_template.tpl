<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tarjeta Regalo</title>
    <style>
        .container {
            width: 100%;
            padding: 20px;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }
        .content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #dddddd;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .header img {
            width: 100px;
            height: auto;
        }
        .gift-card {
            border: 2px dashed #007bff;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
        }
        .gift-card-code {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        .gift-card-amount {
            font-size: 20px;
            color: #555;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <div class="header">
                <img src="{$module_dir}views/img/logo.png" alt="Gift Card Module Logo">
            </div>
            <p>Hola {firstname} {lastname},</p>
            <p>¡Gracias por tu compra! Aquí tienes tu código de tarjeta regalo:</p>
            <div class="gift-card">
                <p class="gift-card-code">{gift_card_code}</p>
                <p class="gift-card-amount">Importe: {gift_card_amount}€</p>
            </div>
            <p>Este código es válido hasta: {date_to}</p>
            <div class="footer">
                <p>Home Heavenly</p>
            </div>
        </div>
    </div>
</body>
</html>
