<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\TwoFactorCodeMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class TwoFactorController extends Controller
{
    /**
     * Muestra el formulario de verificacion 2FA
     *
     * @return \Illuminate\View\View
     */
    public function showVerifyForm()
    {
        return view('auth.verify_code');
    }

    /**
     * Verifica codigo 2FA ingresado por el usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verifyCode(Request $request)
    {
        // Verificación del código 2FA y reCAPTCHA
        $request->validate([
            'two_factor_code' => 'required|numeric|digits:6',
            'g-recaptcha-response' => 'required',
        ], [
            'two_factor_code.required' => 'El código es obligatorio.',
            'two_factor_code.numeric' => 'El código debe ser numérico.',
            'two_factor_code.digits' => 'El código debe tener 6 dígitos.',
            'g-recaptcha-response.required' => 'Por favor, completa el reCAPTCHA.',
        ]);

        // Verificar reCAPTCHA
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response')
        ]);

        $recaptchaData = $recaptchaResponse->json();

        if (!$recaptchaData['success']) {
            return back()->withErrors(['recaptcha' => 'Por favor, completa el reCAPTCHA correctamente.']);
        }

        // Obtener el ID del usuario desde sesión
        $userId = Crypt::decryptString(session('two_factor_user_id'));
        $user = User::findOrFail($userId);

        if (!$user) {
            return back()->withErrors(['two_factor_code' => 'Usuario no encontrado. Intente iniciar sesión nuevamente.']);
        }

        // Desencriptar el código 2FA
        $decryptedCode = Crypt::decryptString($user->two_factor_code);

        // Verificar si el código es correcto y no ha expirado
        if ($decryptedCode === $request->two_factor_code) {
            if (Carbon::now()->gt($user->two_factor_expires_at)) {
                return back()->withErrors(['two_factor_code' => 'El código ha expirado. Intenta nuevamente.']);
            }

            // Limpiar el código para que no pueda reutilizarse
            $user->update([
                'two_factor_code' => null,
                'two_factor_expires_at' => null
            ]);

            // Autenticamos al usuario
            Auth::login($user);
            session()->forget('two_factor_user_id');

            return redirect()->route('home')->with('success', 'Inicio de sesión exitoso.');
        }

        return back()->withErrors(['two_factor_code' => 'El código ingresado es incorrecto.']);
    }


    /**
     * Envia un nuevo codigo 2FA al usuario
     *
     * Recupera al usuario desde sesion, genera un codigo 2FA
     * y lo envia
     *
     * @return \Illuminate\Http\JsonResponse Estado del reenvío.
     */
    public function resendCode()
    {
        // Obtener el ID del usuario desde la sesion
        $userId = Crypt::decryptString(session('two_factor_user_id'));
        $user = User::findOrFail($userId);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no encontrado. Intenta iniciar sesión nuevamente.'
            ], 400);
        }

        // Generar codigo 2FA
        $twoFactorCode = $user->generateTwoFactorCode();

        // Enviar el codigo en segundo plano
        try {
            Mail::to($user->email)->send(new TwoFactorCodeMail($twoFactorCode));
        } catch (\Exception $e) {
            Log::error('Error al enviar correo 2FA: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Se ha enviado un nuevo código a tu correo.'
        ]);
    }
}
