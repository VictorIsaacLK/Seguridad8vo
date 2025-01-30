<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de Código</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            text-align: center;
            padding: 50px;
        }
        .container {
            max-width: 400px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        .error {
            color: red;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Verificación de Código</h1>
        <p>Introduce el código de 6 dígitos enviado a tu correo:</p>

        @if (session('message'))
            <p style="color: green;">{{ session('message') }}</p>
        @endif

        <form action="{{ route('verify.code.submit') }}" method="POST">
            @csrf
            <input type="text" name="two_factor_code" required maxlength="6">
            <button type="submit">Verificar</button>
        </form>

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <p class="error">{{ $error }}</p>
            @endforeach
        @endif
    </div>
</body>
</html>
