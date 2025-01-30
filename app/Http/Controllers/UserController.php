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
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request  The request containing user details
     *  A JSON response with the created user or validation errors
     */
    public function store(Request $request)
    {
        // Validar datos y reCAPTCHA
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

        // Si la validación falla, devolver JSON
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        // Verificar reCAPTCHA con Google
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        $recaptchaData = $response->json();

        if (!$recaptchaData['success']) {
            return response()->json([
                'status' => 400,
                'errors' => ['g-recaptcha-response' => 'Error en la verificación de reCAPTCHA.']
            ], 400);
        }

        // Crear usuario si el reCAPTCHA es válido
        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone_number' => $request->phone_number,
        ]);


        // Enviar correo de activación
        $user->notify(new ActivateAccount($user));

        return response()->json([
            'status' => 201,
            'msg' => 'Registro exitoso. Revisa tu correo para activar tu cuenta.'
        ], 201);
    }


    /**
     * Iniciar sesión y obtener un token de autenticación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validación de los datos
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:50',
            'password' => 'required|string|min:8|max:14|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'g-recaptcha-response' => 'required'
        ], [
            'g-recaptcha-response.required' => 'Por favor, completa el reCAPTCHA.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => ['general' => 'Los datos ingresados son incorrectos.']
            ], 400);
        }

        // Verificar reCAPTCHA con Google
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response')
        ]);

        $recaptchaData = $recaptchaResponse->json();

        if (!$recaptchaData['success']) {
            return response()->json([
                'status' => 'error',
                'errors' => ['ge' => 'Por favor, completa el reCAPTCHA correctamente.']
            ], 400);
        }

        // Buscar usuario
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'errors' => ['general' => 'Credenciales incorrectas.']
            ], 400);
        }

        // Verificar si el usuario está activo (status == 1)
        if ($user->status !== 1) {
            return response()->json([
                'status' => 'error',
                'errors' => ['general' => 'Debes activar tu cuenta antes de iniciar sesión.']
            ], 403);
        }

        // Generar código 2FA y obtenerlo en texto plano
        $twoFactorCode = $user->generateTwoFactorCode();

        // Guardar el ID en sesión (cifrado)
        session(['two_factor_user_id' => Crypt::encryptString($user->id)]);

        // Enviar código en segundo plano
        dispatch(function () use ($user, $twoFactorCode) {
            try {
                Mail::to($user->email)->send(new TwoFactorCodeMail($twoFactorCode));
            } catch (\Exception $e) {
                Log::error('Error al enviar correo 2FA: ' . $e->getMessage());
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha enviado un código a tu correo.',
            'redirect' => route('verify.code')
        ], 200);
    }
    /**
     * Cerrar sesión del usuario y revocar su token actual.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout(); // Cerrar sesión correctamente con el guard web

        $request->session()->invalidate(); // Invalidar la sesión
        $request->session()->regenerateToken(); // Regenerar el token CSRF

        return redirect()->route('home'); // Redirigir a la página principal
    }
}
