<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $table ='tags';
    public $timestamps = false;
    protected $hidden = ['pivot'];

    protected $fillable=[
        'name',
        'is_skill',
        ];

    public function teams(){
        return $this->morphedByMany(Team::class, 'taggable');
    }

    public function posts(){
        return $this->morphedByMany(Post::class, 'taggable');
    }

    public function courses(){
        return $this->morphedByMany(Course::class, 'taggable');
    }

    public function users(){
        return $this->morphedByMany(User::class, 'taggable');
    }

    public function events(){
        return $this->morphedByMany(Event::class, 'taggable');
    }
}
