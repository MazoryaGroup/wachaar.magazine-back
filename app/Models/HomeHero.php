<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeHero extends Model
{
    protected $table = 'home_hero';

    protected $fillable = [
        'video',
        'production_image',
        'magazine_image',
    ];
}
