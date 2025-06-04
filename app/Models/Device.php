<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'brand',
        'model',
        'storage',
        'normalized_name',
        'slug',
        'source_slug',
        'type', // e.g., phone, tablet, watch

    ];

}
