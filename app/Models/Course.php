<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $table ='courses';

    protected $fillable=[
        'user_id',
        'course_title',
        'course_details',
        'course_duration',
        'course_fee',
        'course_image',
        'created_at',
        'updated_at',
    ];

    public function tags(){
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function users(){
        return $this->belongsToMany(User::class);
    }
}
