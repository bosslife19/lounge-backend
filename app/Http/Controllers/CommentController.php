<?php

namespace App\Http\Controllers;

use App\Http\Services\MentorMatchingService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function addComment(Request $request){
                try {
            $request->validate([
                'post_id'=>'required|exists:posts,id',
                'body'=>'required'
            ]);

            $user = $request->user();
            $post = \App\Models\Post::where('id', $request->post_id)->first();

            $user->comments()->create([
                'post_id'=>$request->post_id,
                'body'=>$request->body,
                'user_name'=>$user->first_name.' '.$user->last_name,
                'user_profile_picture'=>$user->profile_picture,
            ]);
             if($user->role !='admin'){
                $user->points = $user->points +2;
                $user->pointHistories()->create([
                    'title'=>'Posts Comment',
                    'description'=>'You Commented on a post',
                    'addition'=>'+3',
                ]);
                $user->save();
            }
            (new MentorMatchingService())->sendNotify($post->user_id, "Your post has a new comment.", "$user->first_name commented on your Post");

            return response()->json(['status'=>true, 'message'=>'Comment added successfully']);
        }catch (\Throwable $th) {
            return response()->json(['message'=>$th->getMessage()]);
        }
    }

   
}
