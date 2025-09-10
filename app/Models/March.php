<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class March extends Model
{
    protected $guarded = [];
     public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Mentor in the match (also a User, but with is_mentor = true)
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }
}
