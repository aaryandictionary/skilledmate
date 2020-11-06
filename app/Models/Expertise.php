<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Expertise extends Model
{
    protected $table ='expertises';

    protected $fillable=[
        'user_id',
        'expertise_title',
        'expertise_description',
        'expertise_image',
        'is_free',
        'rate',
        'rate_unit',
        'active',
        'created_at',
        'updated_at',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
