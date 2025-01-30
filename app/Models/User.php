<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'status',
        'role_id',
        'two_factor_code',
        'two_factor_expires_at',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
    ];

    /**
     * Relación con la tabla roles.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * 🚀 Generar un nuevo código de autenticación de dos factores.
     */
    public function generateTwoFactorCode()
    {
        // Generar el código 2FA de 6 dígitos
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Guardar el código cifrado en la base de datos
        $this->forceFill([
            'two_factor_code' => Crypt::encryptString($code),
            'two_factor_expires_at' => now()->addMinutes(2)
        ])->save();

        // Retornar el código en texto plano
        return $code;
    }
}
