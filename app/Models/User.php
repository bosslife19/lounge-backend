<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    public function posts(){
        return $this->hasMany(Post::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

public function requests(){
    return $this->hasMany(Request::class);
}


public function organization()
{
    return $this->belongsTo(Organization::class);
}


public function contents(){
    return $this->hasMany(Content::class);
}
public function mentorRequest(){
    return $this->hasOne(MentorRequest::class);

}
public function mentorListing(){
    return $this->hasOne(MentorListing::class);
}
public function mentors()
{
    return $this->belongsToMany(\App\Models\User::class, 'mentor_user', 'user_id', 'mentor_id')
                ->withTimestamps();

}

// As a mentor, get my mentees
public function mentees()
{
    return $this->belongsToMany(\App\Models\User::class, 'mentor_user', 'mentor_id', 'user_id')
                ->withTimestamps();

}

// As a user, get mentors


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
