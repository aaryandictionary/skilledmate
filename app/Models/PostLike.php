<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    protected $table ='post_likes';
    protected $primaryKey = 'post_like_id';

    protected $fillable=[
        'post_id',
        'liker_id',
        'created_at',
        'updated_at',
    ];

    public function post(){
        return $this->belongsTo(Post::class);
    }
}
