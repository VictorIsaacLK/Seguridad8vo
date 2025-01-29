<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ActivationController extends Controller
{
    /**
     * Activa la cuenta de un usuario.
     *
     * @param \Illuminate\Http\Request $request La instancia de la solicitud entrante.
     * @param int $id El ID del usuario a activar.
     * @return \Illuminate\Http\RedirectResponse Redirige a la ruta de inicio de sesión con un mensaje de éxito o de cuenta ya activada.
     */
    public function activateAccount(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->status == 1) {
            return redirect()->route('login')->with('message', 'Tu cuenta ya está activada.');
        }

        $user->update(['status' => 1]); // Activar cuenta

        return redirect()->route('login')->with('message', 'Cuenta activada con éxito. Ahora puedes iniciar sesión.');
    }
}
