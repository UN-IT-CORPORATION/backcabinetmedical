<?php

namespace App\Models;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\VerifyApiEmail;

class User extends Authenticatable implements CanResetPassword, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'prenom',
        'email',
        'password',
        'numeroTelephone',
        'date_naissance',
        'adresse',
        'specialité',
        'emploi',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for arrays and JSON serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_naissance' => 'date',
        'password' => 'hashed',
    ];

    /**
     * Send the custom email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyApiEmail());
    }

    public function role()
{
    return $this->belongsTo(Role::class);
}
public function consultations()
{
    return $this->hasMany(Consultation::class);
}
}