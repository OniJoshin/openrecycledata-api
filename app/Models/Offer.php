<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    protected $fillable = [
        'device_id',
        'merchant',
        'price',
        'condition',
        'network',
        'source',
        'timestamp',
    ];

}
