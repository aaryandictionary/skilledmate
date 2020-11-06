<?php

namespace App;

use App\Models\College;
use App\Models\Conversation;
use App\Models\Course;
use App\Models\Expertise;
use App\Models\Post;
use App\Models\Skill;
use App\Models\Tag;
use App\Models\Team;
use App\Models\TeamUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','college_id','phone','user_image','is_connected','seen_status','connection_id','token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function college(){
        return $this->belongsTo(College::class);
    }

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function expertises(){
        return $this->hasMany(Expertise::class);
    }

    public function teams(){
        return $this->belongsToMany(Team::class);
    }

    public function teammembers(){
        return $this->hasMany(TeamUser::class);
    }

    public function courses(){
        return $this->hasMany(Course::class);
    }

    public function tags(){
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function conversations(){
        return $this->belongsToMany(Conversation::class)->withPivot('role','last_active');
    }
}
