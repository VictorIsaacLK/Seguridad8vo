<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bienvenido</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f7fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-login {
            background-color: #4CAF50;
            color: white;
        }
        .btn-logout {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        @auth
            <h1>Bienvenido, {{ Auth::user()->name }}!</h1>
            <h2>Servidor: {{ env('APP_PROJECT') }}</h2>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-logout">Cerrar sesión</button>
            </form>
        @else
            <h1>Bienvenido a la aplicación</h1>
            <h2>Servidor: {{ env('APP_PROJECT') }}</h2>

            <a href="{{ route('login') }}" class="btn btn-login">Iniciar sesión</a>
        @endauth
    </div>
</body>
</html>
