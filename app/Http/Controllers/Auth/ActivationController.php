<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class ActivationController extends Controller
{
    /**
     * Activa la cuenta de un usuario.
     *
     * @param \Illuminate\Http\Request $request La instancia de la solicitud entrante.
     * @param int $id El ID del usuario a activar.
     * @return \Illuminate\Http\RedirectResponse Redirige a la ruta de inicio de sesion con un mensaje de éxito o de cuenta ya activada.
     */
    public function activateAccount(Request $request)
    {
        // Descifrar el id
        $decryptedId = Crypt::decryptString($request->id);

        // Buscar el usuario en la base de datos
        $user = User::findOrFail($decryptedId);

        if ($user->status == 1) {
            return redirect()->route('login')->with('message', 'Tu cuenta ya está activada.');
        }

        $user->update(['status' => 1]); // Activar cuenta

        return redirect()->route('login')->with('message', 'Cuenta activada con éxito. Ahora puedes iniciar sesión.');
    }
}
