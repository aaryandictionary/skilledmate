<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPosition extends Model
{
    protected $table ='project_positions';
    protected $primaryKey = 'project_position_id';

    protected $fillable=[
        'project_id',
        'position_name',
        'created_at',
        'updated_at',
    ];
}
