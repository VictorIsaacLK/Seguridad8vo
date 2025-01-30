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
        .resend-btn {
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .resend-btn:hover {
            background-color: #0056b3;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

        <button class="resend-btn" id="resendCode">Reenviar Código</button>

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <p class="error">{{ $error }}</p>
            @endforeach
        @endif
    </div>

    <script>
        document.getElementById("resendCode").addEventListener("click", function() {
            Swal.fire({
                title: "Reenviando código...",
                text: "Por favor espera mientras generamos un nuevo código.",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch("{{ route('resend.code') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value,
                    "Content-Type": "application/json"
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.fire({
                    icon: data.status === "success" ? "success" : "error",
                    title: data.status === "success" ? "Código reenviado" : "Error",
                    text: data.message
                });
            })
            .catch(error => {
                Swal.fire({
                    icon: "error",
                    title: "Error en el servidor",
                    text: "Hubo un problema al reenviar el código. Inténtalo más tarde."
                });
            });
        });
    </script>
</body>
</html>
