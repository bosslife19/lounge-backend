<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    //
    protected $guarded = [];

    public function creator(){
        return $this->belongsTo(User::class);
    }
}
