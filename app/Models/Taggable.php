<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taggable extends Model
{
    protected $table ='taggables';
    public $timestamps = false;

    protected $fillable=[
        'tag_id',
        'taggable_id',
        'taggable_type',
        ];
}
