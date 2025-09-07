<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mentor extends Model
{
    protected $guarded = [];

    public function users(){
        return $this->belongsToMany(User::class, 'mentor_user');
    }
}
