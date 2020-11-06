<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseContent extends Model
{
    protected $table ='course_content';

    protected $fillable=[
        'content_title',
        'content_details',
        'content_time',
        'course_id',
        'created_at',
        'updated_at',
    ];
}
