<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Event;
use App\Models\MentorRequest;
use App\Models\Organization;
use App\Models\Request as ModelsRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function getOrganizationRequests(Request $request){
        $requests = ModelsRequest::with('user')->where('status', 'pending')->where('type', 'create_organization')->get();

        return response()->json(['status'=>true, 'requests'=>$requests]);
    }
public function removeOrganization(Request $request){
    $organization = Organization::find($request->orgId);
    $organization->status = 'rejected';
    $organization->save();
    return response()->json(['status'=>true]);
}
public function deleteUser(Request $request){
    $user = User::where('id', $request->userId)->first();
    if(!$user){
        return response()->json(['error'=>'User not found']);
    }
    $user->delete();
    return response()->json(['status'=>true]);
}
    public function uploadArticle(Request $request){
        try {
           
           
          $request->validate(['title'=>'required', 'content'=>'required']);
          $user = $request->user();
          $content = $user->contents()->create([
            'title'=>$request->title,
            'link'=>$request->link,
            'image'=>$request->image,
            'content'=>$request->content
          ]);
          return response()->json(['status'=>true, 'article'=>$content]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>$th->getMessage()]);
        }
    }

    public function getAllMentorRequests(){
        $requests = MentorRequest::with("user")->where('status', 'pending')->get();
        return response()->json(['status'=>true, 'requests'=>$requests]);
    }

    public function approveMentor(Request $request){
        $mentorRequest = MentorRequest::find($request->requestId);
        if(!$request->approved){
             $mentorRequest->status = 'rejected';
        $mentorRequest->save();

        return response()->json(['status'=>true]);
        }
        $mentorRequest->status = 'approved';
        $mentorRequest->save();
        $user = User::where('id', $mentorRequest->user_id)->first();
        $user->is_mentor = true;
        $user->save();

        return response()->json(['status'=>true, 'message'=>'Mentorship request approved successfully']);
    }

    public function approveOrganizationRequest(Request $request){
        $orgRequest = ModelsRequest::where('id', $request->organizationId)->first();
        if($request->approved==false){
            $orgRequest->status = "rejected";
        $orgRequest->save();
        

        return response()->json(['status'=>true]);
        }

        $orgRequest->status = "approved";
        $orgRequest->save();
        $organization = Organization::where('name', $orgRequest->name)->where('email', $orgRequest->email)->first();
        $organization->status = "approved";
        $organization->save();

        return response()->json(['status'=>true]);
    }

    public function getMentors(Request $request){
        $mentors = MentorRequest::with('user')->where('status', 'approved')->get();
        return response()->json(['mentors'=>$mentors, 'status'=>true]);
    }
    public function getOrganizations(Request $request){
        $organizations = Organization::where('status', 'approved')->get();

        return response()->json(['status'=>true, 'organizations'=>$organizations]);
    }
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

    public function getPosts(Request $request){
     $user = $request->user();
     $posts = $user->posts;
     return response()->json(['status'=>true, 'posts'=>$posts]);
    }

    public function deleteArticle(Request $request){
        $article = Content::find($request->articleId);
        $article->delete();
        return response()->json(['status'=>true]);
    }
    public function updateEmail(Request $request){
        try {
            //code...
            $request->validate(['email'=>'required']);
            $user = $request->user();
            $user->email = $request->email;
            $user->save();
            return response()->json(['status'=>true], 200);
        
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>$th->getMessage()]);
        }
    }

    public function updateAdminPassword(Request $request){
        $user = $request->user();
        $passwordSame = Hash::check($request->currentPassword, $user->password);
        if(!$passwordSame){
            return response()->json(['error'=>'Current password is incorrect.']);
        }
        $user->password = Hash::make($request->newPassword);
        $user->save();
        return response()->json(['status'=>true]);
    }

    public function createEvent(Request $request){
        try {
            //code...

            $request->validate(['title'=>'required', 'eventDate'=>'required', 'startTime'=>'required', 'endTime'=>'required']);
            Event::create([
                'title'=>$request->title,
                'event_date'=>$request->eventDate,
                'start_time'=>$request->startTime,
                'end_time'=>$request->endTime,
                'event_image'=>$request->eventImage,
                'members_to_notify' => $request->members ?? null,

            ]);
            return response()->json(['status'=>true]);

        } catch (\Throwable $th) {
            //throw $th;
            \Log::info($th->getMessage());
            return response()->json(['error'=>$th->getMessage()]);
        }
    }
}
