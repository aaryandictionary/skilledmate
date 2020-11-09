<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table ='events';

    protected $fillable=[
        'user_id',
        'team_id',
        'event_details',
        'event_organiser',
        'event_time',
        'event_title',
        'event_image',
        'event_deadline',
        'event_privacy',
        'conversation_id',
        'created_at',
        'updated_at',
    ];

    public function tags(){
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function team(){
        return $this->belongsTo(Team::class);
    }

    public function participants(){
        return $this->hasMany(EventParticipant::class);
    }
}
