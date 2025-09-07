<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function uploadPost(Request $request){
        try {
            $request->validate(['body'=>'required']);

            $user = $request->user();
           $post =  $user->posts()->create([
                'body'=>$request->body,
                'post_image'=>$request->image,
            ]);

            return response()->json(['status'=>true, 'message'=>'Post uploaded successfully', 'post'=>$post]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message'=>$th->getMessage()]);
        }
    }

   public function getAllPosts()
{
    $posts = \App\Models\Post::with([
        'user',
        'comments' => function ($query) {
            $query->latest(); // orders comments by created_at descending
        }
    ])
    ->latest()
    ->get();

    return response()->json(['status' => true, 'posts' => $posts]);
}

public function getUserPosts($id){
    $user = User::find($id);

    $posts = $user->posts()->with('comments')->get();

    return response()->json(['status'=>true, 'posts'=>$posts]);
}

public function deletePost($id){
    $post = \App\Models\Post::find($id);

    if(!$post){
        return response()->json(['status'=>false, 'error'=>'Post not found']);
    }

    $post->delete();

    return response()->json(['status'=>true, 'message'=>'Post deleted successfully']);  
}

}
