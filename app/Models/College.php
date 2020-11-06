<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    protected $table ='colleges';

    protected $fillable=[
        'college_name',
        'college_code',
        'college_lat',
        'college_lng',
        'college_address',
        'created_at',
        'updated_at',
    ];


    public function users(){
        return $this->hasMany(User::class);
    }

}
