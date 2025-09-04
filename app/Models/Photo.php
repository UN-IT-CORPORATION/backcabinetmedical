<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'photoable_id',
        'photoable_type',
        'category',
        'photo_type',
        'file_path',
        'upload_date',
        'description',
    ];

    public function photoable()
    {
        return $this->morphTo();
    }
}
