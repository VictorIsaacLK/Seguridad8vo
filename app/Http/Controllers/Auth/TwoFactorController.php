<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class TwoFactorController extends Controller
{
    public function showVerifyForm()
    {
        return view('auth.verify_code');
    }

    public function verifyCode(Request $request)
    {
        $request->validate([
            'two_factor_code' => 'required|numeric|digits:6'
        ], [
            'two_factor_code.required' => 'El código es obligatorio.',
            'two_factor_code.numeric' => 'El código debe ser numérico.',
            'two_factor_code.digits' => 'El código debe tener 6 dígitos.'
        ]);

        // Obtener el ID del usuario desde la sesión
        $userId = session('two_factor_user_id');
        $user = User::find($userId);

        if (!$user) {
            return back()->withErrors(['two_factor_code' => 'Usuario no encontrado. Intente iniciar sesión nuevamente.']);
        }

        // Verificar si el código es correcto y no ha expirado
        if ($user->two_factor_code === $request->two_factor_code) {
            if (Carbon::now()->gt($user->two_factor_expires_at)) {
                return back()->withErrors(['two_factor_code' => 'El código ha expirado. Intenta nuevamente.']);
            }

            // Limpiar el código para que no pueda reutilizarse
            $user->update([
                'two_factor_code' => null,
                'two_factor_expires_at' => null
            ]);

            // Iniciar sesión del usuario y eliminar la sesión temporal del 2FA
            Auth::login($user);
            session()->forget('two_factor_user_id');

            return redirect()->route('home')->with('success', 'Inicio de sesión exitoso.');
        }

        return back()->withErrors(['two_factor_code' => 'El código ingresado es incorrecto.']);
    }
}
