<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationUser extends Model
{
    protected $table ='conversation_user';
    public $timestamps = false;
    // protected $hidden = ['pivot'];

    protected $fillable=[
        'user_id',
        'conversation_id',
        'start_at',
        'last_active',
        'role',
        ];
}
