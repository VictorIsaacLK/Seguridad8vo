<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;
use App\Notifications\ActivateAccount;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Registra un nuevo usuario en el sistema
     *
     * @param  \Illuminate\Http\Request  $request  Datos del usuario a registrar
     * @return \Illuminate\Http\JsonResponse  Respuesta json con el resultado del registro
     */
    public function store(Request $request)
    {
        // Definir claves para limitar numero de intentos por IP y email
        $keyIp = 'register-ip:' . $request->ip();
        $keyEmail = 'register-email:' . $request->email;

        // Verificar si se alcanzo el limite de intentos (correo e IP)
        if (RateLimiter::tooManyAttempts($keyIp, 5)) {
            Log::warning("Intentos excedidos desde IP: {$request->ip()}");
            return response()->json([
                'status' => 'error',
                'errors' => ['general' => 'Demasiadas solicitudes desde esta IP. Intenta de nuevo en ' . RateLimiter::availableIn($keyIp) . ' segundos.']
            ], 429);
        }
        if (RateLimiter::tooManyAttempts($keyEmail, 3)) {
            Log::warning("Demasiados intentos de registro con el correo: {$request->email}");
            return response()->json([
                'status' => 'error',
                'errors' => ['general' => 'Demasiados intentos con este correo. Intenta de nuevo en ' . RateLimiter::availableIn($keyEmail) . ' segundos.']
            ], 429);
        }

        // Validar datos y recaptcha
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|min:4|',
            'last_name' => 'required|string|max:50|min:4|',
            'email' => 'required|string|email|max:50|unique:users',
            'password' => 'required|string|min:8|max:14|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'phone_number' => 'required|regex:/^[0-9]{10}$/',
            'g-recaptcha-response' => 'required',
        ], [
            'required' => 'Este campo es obligatorio.',
            'string' => 'Este campo debe contener solo letras.',
            'max' => 'Este campo no puede tener más de :max caracteres.',
            'min' => 'Este campo debe tener al menos :min caracteres.',
            'email' => 'Debes ingresar un correo electrónico válido.',
            'unique' => 'Este correo electrónico ya está registrado.',
            'regex' => 'La contraseña debe tener al menos una letra mayúscula, una minúscula y un número.',
            'phone_number.regex' => 'El número de teléfono debe tener exactamente 10 dígitos.',
            'g-recaptcha-response.required' => 'Debes completar el reCAPTCHA para registrarte.',
        ]);

        // Si la validacion falla, devolver JSON
        if ($validator->fails()) {
            Log::error("Error de validaion en registro: ", $validator->errors()->toArray());
            RateLimiter::hit($keyIp, 60); // Bloquear IP por 1 minuto si falla
            RateLimiter::hit($keyEmail, 60); // Mismo con correo
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // Verificar rechaptcha con Google (Prevenir bots)
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        $recaptchaData = $response->json();

        if (!$recaptchaData['success']) {
            Log::error("Fallo en reCAPTCHA desde IP: {$request->ip()}");
            return response()->json([
                'status' => 400,
                'errors' => ['g-recaptcha-response' => 'Error en la verificación de reCAPTCHA.']
            ], 400);
        }

        // Crear usuario si lo anterior fue exitoso
        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone_number' => $request->phone_number,
        ]);


        // Enviar correo de activacion
        $user->notify(new ActivateAccount($user));
        Log::info("Usuario registrado exitosamente: {$user->email}");

        // Limpiar intentos fallidos tras un registro exitoso
        RateLimiter::clear($keyIp);
        RateLimiter::clear($keyEmail);

        return response()->json([
            'status' => 201,
            'msg' => 'Registro exitoso. Revisa tu correo para activar tu cuenta.'
        ], 201);
    }
    /**
     * Iniciar sesion y generar un codigo 2FA
     *
     * Esta funcion autentica a un usuario mediante su correo y contraseña,
     * valida el recaptcha (bots) y envia un código 2FA al correo
     * Limitación de intentos de inicio de sesión por IP y por correo
     * para ataques de fuerza bruta
     *
     * @param  \Illuminate\Http\Request  $request  Datos de inicio de sesion
     * @return \Illuminate\Http\JsonResponse  Respuesta json con el resultado del inicio
     */
    public function login(Request $request)
    {
        // Definir llaves para limitar por IP y por email
        $keyIp = 'login-ip:' . $request->ip();
        $keyEmail = 'login-email:' . $request->email;

        // Si se alcanzó el limite, devolver error
        if (RateLimiter::tooManyAttempts($keyIp, 5)) {
            Log::warning("Intentos fallidos excesivos desde IP: {$request->ip()}");
            return response()->json([
                'status' => 'error',
                'errors' => ['general' => 'Demasiados intentos. Intenta de nuevo en ' . RateLimiter::availableIn($keyIp) . ' segundos.']
            ], 429);
        }
        if (RateLimiter::tooManyAttempts($keyEmail, 5)) {
            Log::warning("Intentos fallidos excesivos con el correo: {$request->email}");
            return response()->json([
                'status' => 'error',
                'errors' => ['general' => 'Demasiados intentos con este correo. Intenta de nuevo en ' . RateLimiter::availableIn($keyEmail) . ' segundos.']
            ], 429);
        }

        // Validacion de los datos
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:50',
            'password' => 'required|string|min:8|max:14|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'g-recaptcha-response' => 'required'
        ], [
            'g-recaptcha-response.required' => 'Por favor, completa el reCAPTCHA.',
        ]);

        if ($validator->fails()) {
            Log::error("Error de validacion en login: ", $validator->errors()->toArray());
            return response()->json([
                'status' => 'error',
                'errors' => ['general' => 'Los datos ingresados son incorrectos.']
            ], 400);
        }

        // Verificar recaptcha con Google
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response')
        ]);

        $recaptchaData = $recaptchaResponse->json();

        if (!$recaptchaData['success']) {
            Log::error("Fallo en reCAPTCHA desde IP: {$request->ip()}");
            return response()->json([
                'status' => 'error',
                'errors' => ['ge' => 'Por favor, completa el reCAPTCHA correctamente.']
            ], 400);
        }

        // Buscar usuario
        $user = User::where('email', $request->email)->first();

        // Credenciales verificacion
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Aumentar contador de intentos fallidos
            RateLimiter::hit($keyIp, 60);
            RateLimiter::hit($keyEmail, 60);

            Log::error("Intento de inicio de sesión fallido con el correo: {$request->email}");


            return response()->json([
                'status' => 'error',
                'errors' => ['general' => 'Credenciales incorrectas.']
            ], 400);
        }

        // Verificar si el usuario esta activo (status == 1)
        if ($user->status !== 1) {
            Log::warning("Intento de inicio de sesión con cuenta inactiva: {$request->email}");
            return response()->json([
                'status' => 'error',
                'errors' => ['general' => 'Debes activar tu cuenta antes de iniciar sesión.']
            ], 403);
        }

        // Generar codigo 2FA y obtenerlo en texto plano (no codificado)
        $twoFactorCode = $user->generateTwoFactorCode();
        Log::info("codigo 2FA generado para usuario: {$user->email}");

        // Guardar el ID en sesión (cifrado)
        session(['two_factor_user_id' => Crypt::encryptString($user->id)]);

        // Limpiar intentos fallidos ya que ya se autentifico
        RateLimiter::clear($keyIp);
        RateLimiter::clear($keyEmail);

        // Enviar codigo en segundo plano
        dispatch(function () use ($user, $twoFactorCode) {
            try {
                Mail::to($user->email)->send(new TwoFactorCodeMail($twoFactorCode));
                Log::info("Código 2FA enviado al correo: {$user->email}");
            } catch (\Exception $e) {
                Log::error('Error al enviar correo 2FA: ' . $e->getMessage());
            }
        });

        // Responder con éxito y redireccionar al siguiente paso
        return response()->json([
            'status' => 'success',
            'message' => 'Se ha enviado un código a tu correo.',
            'redirect' => route('verify.code')
        ], 200);
    }
    /**
     * Cerrar sesión del usuario y revocar su token actual.
     *
     * @param  \Illuminate\Http\Request  $request //Solicitud HTTP
     * @return \Illuminate\Http\JsonResponse //Redirecion
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); // Cerrar sesion con el guard web

        $request->session()->invalidate(); // Invalidar la sesion actual
        $request->session()->regenerateToken(); // Regenerar el token csrf

        return redirect()->route('home'); // Redirigir a la pagina inicial
    }
}
