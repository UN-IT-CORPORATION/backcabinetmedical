<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Traitement extends Model
{
    use HasFactory;

    protected $fillable = ['nom', 'prix'];

    /* Relations */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    /* Scopes d'exemple */
    public function scopePrixMin(Builder $query, float $min): Builder
    {
        return $query->where('prix', '>=', $min);
    }

    public function scopePrixMax(Builder $query, float $max): Builder
    {
        return $query->where('prix', '<=', $max);
    }

    public function consultations()
{
    return $this->belongsToMany(Consultation::class, 'consultation_traitement')
                ->withPivot('prix')
                ->withTimestamps();
}
}
