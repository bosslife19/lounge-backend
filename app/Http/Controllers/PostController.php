<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostController extends Controller
{
    public function uploadPost(Request $request){
        try {
            $request->validate(['body'=>'required']);

            $user = $request->user();
            $user->posts()->create([
                'body'=>$request->body,
                'post_image'=>$request->image,
            ]);

            return response()->json(['status'=>true, 'message'=>'Post uploaded successfully']);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message'=>$th->getMessage()]);
        }
    }

    public function getAllPosts(){
        $posts = \App\Models\Post::with(['user', 'comments'])->latest()->get();
        return response()->json(['status'=>true, 'posts'=>$posts]);
    }
}
