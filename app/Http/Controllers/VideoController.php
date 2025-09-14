<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function getVideos(){
        $videos = Video::latest()->get();
        return response()->json(['status'=>true, 'videos'=>$videos]);
    }
}
