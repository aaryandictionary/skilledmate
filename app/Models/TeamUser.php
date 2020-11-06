<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TeamUser extends Model
{
    protected $table ='team_user';

    protected $fillable=[
        'team_id',
        'user_id',
        'role',
        'role_title',
        'created_at',
        'updated_at',
    ];

    public function team(){
        return $this->belongsTo(Team::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
