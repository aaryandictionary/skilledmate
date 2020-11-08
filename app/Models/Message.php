<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table ='messages';

    protected $fillable=[
        'conversation_id',
        'sender_id',
        'text_msg',
        'content',
        'content_type',
        'created_at',
        'updated_at',
    ];

    public function conversation(){
        return $this->belongsTo(Conversation::class);
    }
}
