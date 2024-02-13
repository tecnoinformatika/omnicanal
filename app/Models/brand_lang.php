<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class brand_lang extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'lang',
        'brand_id',
    ];
}
