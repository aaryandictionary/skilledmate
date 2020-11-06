<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamImage extends Model
{
    protected $table ='team_images';

    protected $fillable=[
        'team_id',
        'team_img',
        'created_at',
        'updated_at',
    ];

    public function team(){
        return $this->belongsTo(Team::class);
    }

}
