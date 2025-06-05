<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'icone',
        'nom',
        'description_courte',
        'details',
        'horaires',
    ];

    public function traitements()
{
    return $this->belongsToMany(Traitement::class);
}

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }


    public function rendezvouses()
    {
        return $this->hasMany(Rendezvous::class);
    }




}
