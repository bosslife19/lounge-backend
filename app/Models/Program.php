<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $guarded = [];

    public function sections(){
        return $this->hasMany(Section::class);
    }
    public function speakerHighlights(){
        return $this->hasMany(SpeakerHighlight::class);
    }
}
