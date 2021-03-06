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
        'conv_type',
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    public function users(){
        return $this->belongsToMany(User::class)->withPivot('role','last_active');
    }

    public function lastmessage(){
        return $this->hasOne(Message::class,'conversation_id','id')->orderBy('id','DESC');
    }
}
