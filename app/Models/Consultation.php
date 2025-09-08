<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Photo;
use App\Models\User;
use App\Models\Traitement;
use App\Models\Stock;
use App\Models\Paiement;
use App\Models\Antecedent;

class Consultation extends Model
{
    protected $fillable = [
        'user_id',
        'date_consultation',
        'nb_seances',
        'seancerestant',
        'total',
        'observation',
        'temperature',
        'tension',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function traitements()
    {
        return $this->belongsToMany(Traitement::class, 'consultation_traitement')
                    ->withPivot('prix')
                    ->withTimestamps();
    }

    public function produits()
    {
        return $this->belongsToMany(Stock::class, 'consultation_stock')
                    ->withPivot('prix')
                    ->withTimestamps();
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function antecedents()
    {
        return $this->belongsToMany(Antecedent::class, 'consultation_antecedent')
                    ->withTimestamps();
    }
    public function photos()
    {
        return $this->morphMany(Photo::class, 'photoable');
    }

}
