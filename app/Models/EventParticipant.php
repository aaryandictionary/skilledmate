<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventParticipant extends Model
{
    protected $table ='event_participants';

    protected $fillable=[
        'user_id',
        'event_id',
        'created_at',
        'updated_at',
    ];

    public function event(){
        return $this->belongTo(Event::class,'event_id','id');
    }
}
