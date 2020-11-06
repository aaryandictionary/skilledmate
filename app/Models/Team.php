<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $table ='teams';

    protected $fillable=[
        'team_title',
        'user_id',
        'team_tagline',
        'team_icon',
        'team_description',
        'created_at',
        'updated_at',
    ];

    public function users(){
        return $this->belongsToMany(User::class);
    }

    public function projects(){
        return $this->hasMany(Project::class);
    }

    public function images(){
        return $this->hasMany(TeamImage::class);
    }

    public function members(){
        return $this->hasMany(TeamUser::class);
    }

    public function followers(){
        return $this->hasMany(TeamUser::class);
    }

    public function tags(){
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function events(){
        return $this->hasMany(Event::class);
    }
}
