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
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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
            <!-- reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
            <span class="error" id="recaptchaError"></span>
        </form>

        <button class="resend-btn" id="resendCode">Reenviar Código</button>

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <p class="error">{{ $error }}</p>
            @endforeach
        @endif
    </div>

    <script>
        document.getElementById('verifyForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            // Limpiar mensajes de error previos
            document.querySelectorAll('.error').forEach(el => el.textContent = '');

            // Mostrar alerta de carga
            Swal.fire({
                title: 'Verificando código...',
                text: 'Por favor espera mientras verificamos tu información.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Deshabilitar el botón de verificación
            let verifyButton = document.querySelector('button[type="submit"]');
            verifyButton.disabled = true;

            let formData = new FormData(this);
            formData.append('g-recaptcha-response', grecaptcha.getResponse());

            try {
                let response = await fetch("{{ route('verify.code.submit') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                });

                let data = await response.json();

                if (!response.ok) {
                    Swal.close();
                    verifyButton.disabled = false;
                    if (data.errors.recaptcha) {
                        Swal.fire({
                            icon: 'error',
                            title: 'reCAPTCHA inválido',
                            text: data.errors.recaptcha
                        });
                    } else if (data.errors.general) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.errors.general
                        });
                    }
                    return;
                }

                // Si el código es correcto, redirigir
                Swal.fire({
                    icon: 'success',
                    title: 'Código verificado',
                    text: 'Acceso concedido. Redirigiendo...',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = "{{ route('home') }}";
                });

            } catch (error) {
                console.error("Error en la solicitud:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el servidor',
                    text: 'Hubo un problema, intenta nuevamente más tarde.'
                });
                verifyButton.disabled = false;
            }
        });
    </script>
</body>
</html>
