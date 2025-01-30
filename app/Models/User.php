<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Carbon\Carbon;

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
        $this->forceFill([
            'two_factor_code' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'two_factor_expires_at' => Carbon::now()->addMinutes(2)
        ])->save();
    }
}
