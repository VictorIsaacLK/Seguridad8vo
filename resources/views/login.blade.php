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

        .error {
            color: red;
            font-size: 12px;
            text-align: left;
            margin-top: -5px;
            margin-bottom: 10px;
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
    </style>

    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <h1>Iniciar Sesión</h1>
        <h2>Servidor: {{ env('APP_PROJECT') }}</h2>

        <form id="loginForm">
            @csrf
            <input type="email" name="email" placeholder="Email" required>
            <span class="error" id="emailError"></span>

            <input type="password" name="password" placeholder="Contraseña" required>
            <span class="error" id="passwordError"></span>

            <!-- reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
            <span class="error" id="recaptchaError"></span>

            <button type="submit" class="btn-login">Iniciar sesión</button>

            <div class="link-container">
                <a href="#" class="disabled-link">¿Olvidaste tu contraseña?</a>
                <a href="{{ url('/register') }}">Registrarse</a>
            </div>

            <button class="btn-back" onclick="window.location.href='{{ url('/') }}'">Regresar</button>

        </form>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            console.log("➡️ Evento submit disparado");

            let tokenInput = document.querySelector('input[name="_token"]');
            console.log("🔑 Token CSRF encontrado en el DOM:", tokenInput);
            console.log("🔑 Valor del token:", tokenInput?.value);

            // Limpiar errores anteriores
            document.querySelectorAll('.error').forEach(el => el.textContent = '');

            // Mostrar alerta de carga
            Swal.fire({
                title: 'Iniciando sesión...',
                text: 'Por favor espera mientras verificamos tu información.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Deshabilitar botón de login
            let loginButton = document.querySelector('button[type="submit"]');
            loginButton.disabled = true;

            let formData = new FormData(this);
            formData.append('g-recaptcha-response', grecaptcha.getResponse());

            console.log("📦 FormData construido:");
            for (let pair of formData.entries()) {
                console.log(`${pair[0]}: ${pair[1]}`);
            }

            // DEBUG de ruta
            const endpoint = "{{ route('login.submit') }}";
            console.log("🌐 URL a donde se enviará el POST:", endpoint);

            try {
                let response = await fetch(endpoint, {
                    method: "POST",
                    headers: {
                        'Accept': 'application/json', // Asegura que Laravel devuelva JSON
                        'X-CSRF-TOKEN': tokenInput?.value
                    },
                    body: formData,
                    credentials: 'include'
                });

                console.log("📨 Respuesta recibida del servidor:");
                console.log("✅ Status:", response.status);
                console.log("✅ OK:", response.ok);

                let textData = await response.text(); // primero obtenlo como texto por si no es JSON válido
                console.log("📄 Cuerpo de la respuesta:", textData);

                let data;
                try {
                    data = JSON.parse(textData);
                } catch (jsonError) {
                    console.error("❌ No se pudo parsear el JSON:", jsonError);
                    throw new Error("Respuesta no es JSON válido");
                }

                if (!response.ok) {
                    Swal.close();
                    loginButton.disabled = false;
                    if (data.errors?.general) {
                        if (response.status === 429) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Demasiadas solicitudes',
                                text: data.errors.general
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.errors.general
                            });
                        }
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error desconocido.'
                        });
                    }
                    return;
                }

                // Si todo va bien
                Swal.fire({
                    icon: 'success',
                    title: 'Código enviado',
                    text: 'Revisa tu correo para ingresar el código de verificación.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = data.redirect;
                });

            } catch (error) {
                console.error("❌ Error en la solicitud:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error en el servidor',
                    text: 'Hubo un problema, intenta nuevamente más tarde.'
                });
                loginButton.disabled = false;
            }
        });
    </script>

</body>

</html>