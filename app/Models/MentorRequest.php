<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentorRequest extends Model
{
 protected $guarded =[];

 public function user(){
    return $this->belongsTo(User::class);
 }
}
