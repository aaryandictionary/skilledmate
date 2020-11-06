<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table ='posts';

    protected $fillable=[
        'user_id',
        'post_content',
        'post_type',
        'event_id',
        'team_id',
        'post_image',
        'created_at',
        'updated_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }


    public function likes(){
        return $this->hasMany(PostLike::class);
    }

    public function comments(){
        return $this->hasMany(PostComment::class);
    }

    public function images(){
        return $this->hasMany(PostImage::class);
    }

    public function tags(){
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function team(){
        return $this->hasOne(Team::class,'id','team_id')
                        ->select('team_title','team_icon','team_tagline','id');
    }
}
