<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Verificación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            max-width: 400px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        p {
            font-size: 16px;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Tu Código de Verificación</h1>
        <p>Usa el siguiente código para completar tu inicio de sesión:</p>
        <p class="code">{{ $code }}</p>
        <p>Este código expirará en 2 minutos.</p>
        <p>Si no intentaste iniciar sesión, ignora este mensaje.</p>
    </div>
</body>
</html>
