<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    protected $table ='post_comments';
    protected $primaryKey = 'post_comment_id';

    protected $fillable=[
        'post_id',
        'commenter_id',
        'comment',
        'created_at',
        'updated_at',
    ];

    public function post(){
        return $this->belongsTo(Post::class);
    }
}
