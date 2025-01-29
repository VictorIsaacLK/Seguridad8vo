@if (Auth::check())
    <script>
        window.location.href = "{{ route('/') }}";
    </script>
@endif
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">

    <!-- Iconos -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #DCDDDF;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            width: 400px;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-login {
            width: 100%;
            padding: 10px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-login:hover {
            background: #45a049;
        }

        .link-container {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }

        .disabled-link {
            color: gray;
            pointer-events: none;
            cursor: default;
            text-decoration: none;
            opacity: 0.6;
        }

        .btn-back {
            width: 100%;
            padding: 10px;
            background: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }

        .btn-back:hover {
            background: #d32f2f;
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>

<body>
    <div class="container">
        <h1>Iniciar Sesión</h1>

        <!-- Mostrar mensajes de error -->
        @if ($errors->any())
            <div class="error-message">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif


        <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            <input type="email" name="email" placeholder="Email" required />
            <input type="password" name="password" placeholder="Contraseña" required />

            <!-- reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>

            <button type="submit" class="btn-login">Iniciar sesión</button>

            <div class="link-container">
                <a href="#" class="disabled-link">¿Olvidaste tu contraseña?</a>
                <a href="{{ url('/register') }}">Registrarse</a>
            </div>

            <button class="btn-back" onclick="window.location.href='{{ url('/') }}'">Regresar</button>
        </form>
    </div>
</body>

</html>
