<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    // Champs remplissables (à adapter selon ta table)
    protected $fillable = [
        'name',
        'email',
        'phone',
        'birthdate',
        // ajoute d'autres champs selon ta table patients
    ];

    /**
     * Relation polymorphique : toutes les photos liées à ce patient
     */
    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');
    }

    /**
     * Relation polymorphique : photos médicales uniquement
     */
    public function medicalPhotos()
    {
        return $this->morphMany(Photo::class, 'photoable')
                    ->where('category', 'medical');
    }
}
