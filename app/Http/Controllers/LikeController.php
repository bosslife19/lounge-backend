<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    //
    public function likePost(Request $request){
        $alreadyLiked = Like::where("user_id", $request->userId)->where("post_id", $request->postId)->first();
        if($alreadyLiked){
           $alreadyLiked->delete();
           return response()->json(['status'=>true]);
        }
        $post = Post::where('id', $request->postId)->first();
        $post->likes()->create(['user_id'=>$request->userId]);
        return response()->json(['status'=>true]);

    }
    public function getLikes(Request $request){
        $likes = Like::with('user', 'post')->latest()->get();
        return response()->json(['status'=>true, 'likes'=>$likes]);
    }
}
