<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rendezvous extends Model
{
    protected $fillable=['user_id','service_id','date','heure','desciption'];



    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

}
