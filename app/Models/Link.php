<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'url',
        'short_code',
        'start_date',
        'redirect_count',
        'last_seen_date',
    ];

    public $timestamps = false;
}
