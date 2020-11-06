<?php

namespace App\Models;
use App\User;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $table ='conversations';

    protected $fillable=[
        'conv_title',
        'conv_icon',
        'conv_desc',
        'last_msg',
        'conv_type',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function users(){
        return $this->belongsToMany(User::class)->withPivot('role','last_active');
    }
}
