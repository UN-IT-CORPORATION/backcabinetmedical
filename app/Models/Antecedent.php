<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Antecedent extends Model
{
    protected $fillable = ['user_id', 'titre', 'description'];

    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function consultations()
    {
        return $this->belongsToMany(Consultation::class, 'consultation_antecedent')
                    ->withTimestamps();
    }
}