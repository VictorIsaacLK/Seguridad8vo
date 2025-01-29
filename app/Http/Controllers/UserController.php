<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ActivateAccount;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Obtener todos los usuarios
        $users = User::with('role')->get();
        return response()->json($users);
        // return response()->json(['message' => 'Listado de usuarios no disponible todavia'], 405);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //  Temporal en lo que se crea la interacion por medio de Blade
        return response()->json(['message' => 'Formulario de creación no disponible toadavia'], 405);
    }

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
            'password' => bcrypt($request->password), // Cifrar contraseña
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Obtener un usuario por ID
        $user = User::with('role')->find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // Aun no disponible
        return response()->json(['message' => 'Formulario de edición no disponible'], 405);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validar los datos
        $validatedData = $request->validate([
            'name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|max:14|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'phone_number' => 'required|regex:/^[0-9]{10}$/',
        ]);

        // Buscar el usuario por ID
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // Actualizar los datos del usuario
        $user->update(array_filter([
            'name' => $validatedData['name'] ?? null,
            'last_name' => $validatedData['last_name'] ?? null,
            'email' => $validatedData['email'] ?? null,
            'password' => isset($validatedData['password']) ? bcrypt($validatedData['password']) : null,
            'phone_number' => $validatedData['phone_number'] ?? null,
        ]));

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Buscar el usuario por ID
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // Cambiar el campo status a 0
        $user->status = 0;
        $user->save();

        return response()->json(['message' => 'El usuario ha sido desactivado (status cambiado a 0)']);
    }

    /**
     * Iniciar sesión y obtener un token de autenticación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        // Validación de los campos
        $credentials = $request->validate([
            'email' => 'required|email|max:50',
            'password' => 'required|string|min:8|max:14|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'g-recaptcha-response' => 'required' // Asegura que el reCAPTCHA esté presente
        ], [
            'g-recaptcha-response.required' => 'Por favor, completa el reCAPTCHA.', // Mensaje específico para reCAPTCHA
        ]);

        // Verificar reCAPTCHA con Google
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response')
        ]);

        $recaptchaData = $recaptchaResponse->json();


        if (!$recaptchaData['success']) {
            return back()->withErrors(['g-recaptcha-response' => 'Por favor, completa el reCAPTCHA correctamente.'])->withInput();
        }

        // Si reCAPTCHA es válido, continuar con la autenticación
        $userCredentials = $request->only('email', 'password'); // Solo usar email y password

        if (Auth::guard('web')->attempt($userCredentials)) {
            $request->session()->regenerate(); // Regenerar la sesión
            return redirect()->route('home')->with('success', 'Inicio de sesión exitoso.');
        }

        // Mensaje genérico para credenciales incorrectas
        return back()->withErrors([
            'general' => 'Credenciales incorrectas.',
        ]);
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
