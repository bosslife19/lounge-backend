<?php

namespace App\Http\Controllers;

use App\Http\Services\MentorMatchingService;
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
        $user = $request->user();
        $post->likes()->create(['user_id'=>$request->userId]);
                (new MentorMatchingService())->sendNotify($post->user_id, "Your post has a new like.", "$user->first_name Liked your Post");
        return response()->json(['status'=>true]);

    }
    public function getLikes(Request $request){
        $likes = Like::with('user', 'post')->latest()->get();
        return response()->json(['status'=>true, 'likes'=>$likes]);
    }
}
