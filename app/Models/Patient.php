<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Le modèle Patient devrait ressembler à ceci:
class Patient extends Model
{
    protected $fillable = [
        'nom', 'adresse', 'telephone', 'email', 'date_naissance'
    ];
}

