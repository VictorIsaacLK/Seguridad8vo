<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::resources([
//     'users' => UserController::class,
// ]);

// Crear un nuevo usuario (POST)
Route::post('/users', [UserController::class, 'store']);
// Obtener todos los usuarios (GET)
Route::get('/get-users', [UserController::class, 'index']);
// Loguear una sesion (POST)
Route::post('/login', [UserController::class, 'login']);
// Cerrar una sesion (POST)

Route::middleware([EnsureFrontendRequestsAreStateful::class, 'auth:sanctum'])->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
});

