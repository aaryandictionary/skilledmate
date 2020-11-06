<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table ='projects';
    protected $primaryKey = 'project_id';

    protected $fillable=[
        'team_id',
        'project_title',
        'project_description',
        'project_image',
        'created_at',
        'updated_at',
    ];

    public function team(){
        return $this->belongsTo(Team::class);
    }
}
