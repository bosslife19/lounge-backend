<?php

namespace App\Http\Controllers;

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
            $user->comments()->create([
                'post_id'=>$request->post_id,
                'body'=>$request->body,
            ]);

            return response()->json(['status'=>true, 'message'=>'Comment added successfully']);
        }catch (\Throwable $th) {
            return response()->json(['message'=>$th->getMessage()]);
        }
    }
}
