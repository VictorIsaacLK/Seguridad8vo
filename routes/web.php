<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\ActivationController;
use App\Http\Controllers\Auth\TwoFactorController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Página de inicio (bienvenida)
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Grupo de rutas accesibles solo si el usuario NO está autenticado
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return view('login');
    })->name('login');

    Route::post('/login', [UserController::class, 'login'])->name('login.submit');

    Route::get('/register', function () {
        return view('register');
    })->name('register.form');


    Route::post('/register', [UserController::class, 'store'])->name('register.submit');
});

// Ruta para cerrar sesión (logout) - Solo si el usuario está autenticado
Route::post('/logout', [UserController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/activate-account/{id}', [ActivationController::class, 'activateAccount'])
    ->name('activate.account')
    ->middleware('signed'); // La url firmada




Route::get('/verify-code', [TwoFactorController::class, 'showVerifyForm'])->name('verify.code')->middleware('signed');
Route::post('/verify-code', [TwoFactorController::class, 'verifyCode'])->name('verify.code.submit');
Route::post('/resend-code', [TwoFactorController::class, 'resendCode'])->name('resend.code');






















//test
Route::get('/test-session', function () {
    if (Auth::check()) {
        return 'Usuario autenticado: ' . Auth::user()->name;
    }
    return 'No hay usuario autenticado.';
});


// Test para verifficar las sesiones
Route::get('/session-test', function (Request $request) {
    session(['test' => 'Sesion de Laravel funcionando']);
    return 'Sesión almacenada';
});

Route::get('/session-check', function (Request $request) {
    return session('test', 'No hay sesion activa');
});

Route::get('/test-auth', function () {
    return Auth::check() ? 'Usuario autenticado: ' . Auth::user()->name : 'No hay usuario autenticado';
});
















Route::get('/set-session', function () {
    session(['user_id' => 2]);
    return 'Session set!';
});

Route::get('/get-session', function () {
    return session('user_id', 'No session found!');
});
