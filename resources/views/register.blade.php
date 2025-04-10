@if (Auth::check())
    <script>
        window.location.href = "{{ route('/') }}";
    </script>
@endif
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">

    <!-- Cargar CSS desde Public -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">

    <!----===== Iconscout CSS ===== -->
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">

    <style>
        /* ===== Google Font Import - Poppins ===== */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #4070f4;
        }

        .container {
            position: relative;
            max-width: 900px;
            width: 100%;
            border-radius: 6px;
            padding: 30px;
            margin: 0 15px;
            background-color: #fff;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        .container header {
            position: relative;
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        .container header::before {
            content: "";
            position: absolute;
            left: 0;
            bottom: -2px;
            height: 3px;
            width: 27px;
            border-radius: 8px;
            background-color: #4070f4;
        }

        .container form {
            position: relative;
            margin-top: 16px;
            min-height: 490px;
            background-color: #fff;
            overflow: hidden;
        }

        .container form .form {
            position: absolute;
            background-color: #fff;
            transition: 0.3s ease;
        }

        .container form .form.second {
            opacity: 0;
            pointer-events: none;
            transform: translateX(100%);
        }

        form.secActive .form.second {
            opacity: 1;
            pointer-events: auto;
            transform: translateX(0);
        }

        form.secActive .form.first {
            opacity: 0;
            pointer-events: none;
            transform: translateX(-100%);
        }

        .container form .title {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: 500;
            margin: 6px 0;
            color: #333;
        }

        .container form .fields {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        form .fields .input-field {
            display: flex;
            width: calc(100% / 3 - 15px);
            flex-direction: column;
            margin: 4px 0;
        }

        .input-field label {
            font-size: 12px;
            font-weight: 500;
            color: #2e2e2e;
        }

        .input-field input,
        select {
            outline: none;
            font-size: 14px;
            font-weight: 400;
            color: #333;
            border-radius: 5px;
            border: 1px solid #aaa;
            padding: 0 15px;
            height: 42px;
            margin: 8px 0;
        }

        .input-field input :focus,
        .input-field select:focus {
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.13);
        }

        .input-field select,
        .input-field input[type="date"] {
            color: #707070;
        }

        .input-field input[type="date"]:valid {
            color: #333;
        }

        .container form button,
        .backBtn {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 45px;
            max-width: 200px;
            width: 100%;
            border: none;
            outline: none;
            color: #fff;
            border-radius: 5px;
            margin: 25px 0;
            background-color: #4070f4;
            transition: all 0.3s linear;
            cursor: pointer;
        }

        .container form .btnText {
            font-size: 14px;
            font-weight: 400;
        }

        form button:hover {
            background-color: #265df2;
        }

        form button i,
        form .backBtn i {
            margin: 0 6px;
        }

        form .backBtn i {
            transform: rotate(180deg);
        }

        form .buttons {
            display: flex;
            align-items: center;
        }

        form .buttons button,
        .backBtn {
            margin-right: 14px;
        }

        @media (max-width: 750px) {
            .container form {
                overflow-y: scroll;
            }

            .container form::-webkit-scrollbar {
                display: none;
            }

            form .fields .input-field {
                width: calc(100% / 2 - 15px);
            }
        }

        @media (max-width: 550px) {
            form .fields .input-field {
                width: 100%;
            }
        }
    </style>

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: #4070f4;
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

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .error {
            color: red;
            font-size: 12px;
            text-align: left;
        }

        .btn-back:hover {
            background: #d32f2f;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-top: 10px;
        }
    </style>

    <!-- Script de Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body>
    <div class="container">
        <h1>Registro</h1>
        <form id="registerForm">
            @csrf
            <input type="text" name="name" placeholder="Nombre" required>
            <span class="error" id="nameError"></span>

            <input type="text" name="last_name" placeholder="Apellido" required>
            <span class="error" id="lastNameError"></span>

            <input type="email" name="email" placeholder="Email" required>
            <span class="error" id="emailError"></span>

            <input type="text" name="phone_number" placeholder="Número de Teléfono" required>
            <span class="error" id="phoneError"></span>

            <input type="password" name="password" placeholder="Contraseña" required>

            <span class="error" id="passwordError"></span>
            <small style="color: gray; font-size: 12px;">Debe tener entre 8 y 14 caracteres, incluir una mayúscula, una
                minúscula y un número.</small>

            <br>

            <!-- reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
            <span class="error" id="recaptchaError"></span>

            <div class="button-container">
                <button type="submit">Registrarse</button>
                <button class="btn-back" onclick="window.location.href='{{ url('/') }}'">Regresar</button>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('registerForm').addEventListener('submit', async function(event) {
            event.preventDefault();

            // Limpiar mensajes de error previos
            document.querySelectorAll('.error').forEach(el => el.textContent = '');

            // Mostrar alerta de carga
            Swal.fire({
                title: 'Registrando...',
                text: 'Por favor espera mientras procesamos tu registro.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Deshabilitar botón de registro
            let registerButton = document.querySelector('button[type="submit"]');
            registerButton.disabled = true;

            let formData = new FormData(this);
            formData.append('g-recaptcha-response', grecaptcha.getResponse());

            try {
                let response = await fetch("{{ route('register.submit') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                });

                let data = await response.json();

                // Si hay errores de validación, los mostramos y cerramos SweetAlert
                if (!response.ok) {
                    Swal.close();
                    registerButton.disabled = false;

                    if (response.status === 429) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Demasiadas solicitudes',
                            text: data.errors.general
                        });
                    } else {
                        Object.keys(data.errors).forEach(key => {
                            let errorMessage = data.errors[key][0];
                            if (key === 'name') document.getElementById('nameError').textContent =
                                errorMessage;
                            if (key === 'last_name') document.getElementById('lastNameError')
                                .textContent = errorMessage;
                            if (key === 'email') document.getElementById('emailError').textContent =
                                errorMessage;
                            if (key === 'phone_number') document.getElementById('phoneError')
                                .textContent = errorMessage;
                            if (key === 'password') document.getElementById('passwordError')
                                .textContent = errorMessage;
                            if (key === 'g-recaptcha-response') document.getElementById(
                                'recaptchaError').textContent = errorMessage;
                        });
                    }

                    return;
                }

                // Si todo está bien, mostrar alerta de éxito
                Swal.fire({
                    icon: 'success',
                    title: 'Registro exitoso',
                    text: 'Revisa tu correo para activar tu cuenta.',
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
                registerButton.disabled = false;
            }
        });
    </script>

</body>

</html>
