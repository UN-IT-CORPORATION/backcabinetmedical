<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medecin extends Model
{
    use HasFactory;

    protected $table = 'medecins'; // Assure-toi que c'est bien défini

    protected $fillable = ['nom', 'specialite', 'telephone', 'email', 'password'];

    protected $hidden = ['password']; // Optionnel pour cacher le mot de passe
}