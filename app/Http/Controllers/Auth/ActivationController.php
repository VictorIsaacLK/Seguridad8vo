<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class ActivationController extends Controller
{
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
