<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'nom',
        'quantite_total',
        'quantite_carton',
        'service_id',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function scopeQuantiteMin($query, $min)
    {
        return $query->where('quantite_total', '>=', $min);
    }
}