<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class brands extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'image',
        'featured',
        'status',
        'admin_id',
        'slug',
    ];
}
