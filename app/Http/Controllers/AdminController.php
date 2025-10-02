<?php

namespace App\Http\Controllers;

use App\Http\Services\MentorMatchingService;
use App\Models\Content;
use App\Models\Event;
use App\Models\MentorListing;
use App\Models\MentorRequest;
use App\Models\Organization;
use App\Models\Program;
use App\Models\Request as ModelsRequest;
use App\Models\Section;
use App\Models\SpeakerHighlight;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
     public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($request->user()->role=='admin') {
                return $next($request);
            }
            return response()->json(['error'=>'Unauthorized'], 403);
            
        });
    }
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
            'content'=>$request->content,
            'type'=>$request->type
          ]);
          return response()->json(['status'=>true, 'article'=>$content]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>$th->getMessage()]);
        }
    }
        public function updateArticle(Request $request){
        try {
           
           
            $request->validate(['title'=>'required', 'content'=>'required']);
          $article = Content::find($request->articleId);
          $article->update([
            'title'=>$request->title,
            'link'=>$request->link,
            'image'=>$request->image,
            'content'=>$request->content,
            'type'=>$request->type
          ]);
        
          return response()->json(['status'=>true, 'article'=>$article]);
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
         $userId = $mentorRequest->user->id;
        if(!$request->approved){
             $mentorRequest->status = 'rejected';
        $mentorRequest->save();
       

        $notify = (new MentorMatchingService())->sendNotify($userId, 'Rejected', 'Your mentorship request has been rejected');


        return response()->json(['status'=>true]);
        }
        $mentorRequest->status = 'approved';
        $mentorRequest->save();
        $user = User::where('id', $mentorRequest->user_id)->first();
        $user->is_mentor = true;
        $user->save();
        (new MentorMatchingService())->sendNotify($userId, 'Congratulations!', 'Your mentorship request has been accepted');

        return response()->json(['status'=>true, 'message'=>'Mentorship request approved successfully']);
    }

    public function approveOrganizationRequest(Request $request){
        $orgRequest = ModelsRequest::where('id', $request->organizationId)->first();
        if($request->approved==false){
            $orgRequest->status = "rejected";
        $orgRequest->save();

        (new MentorMatchingService())->sendNotify($orgRequest->user->id,'Organization Request Rejected', 'Your request to create an organization has been rejected after review');
        

        return response()->json(['status'=>true]);
        }

        $orgRequest->status = "approved";
        $orgRequest->save();
        $organization = Organization::where('name', $orgRequest->name)->where('email', $orgRequest->email)->first();
        $organization->status = "approved";
        $organization->save();
        (new MentorMatchingService())->sendNotify($orgRequest->user->id,'Organization Request Accepted', 'Your request to create an organization has been accepted after review');

        return response()->json(['status'=>true]);
    }

    public function updateVideo(Request $request){
        try {
            //code...
            $video = Video::find($request->videoId);
            $video->update([
                 'thumbnail'=>$request->image?? $video->thumbnail,
                'title'=>$request->title,
                'video_link'=>$request->link
            ]);
            return response()->json(['status'=>true]);
        } catch (\Throwable $th) {
            //throw $th;
             return response()->json(['error'=>$th->getMessage()]);
        }
    }

    public function uploadVideo(Request $request){
        try {
            //code...
             $request->validate(['title'=>'required|string', 'link'=>'required|string']);
             $user = $request->user();
           $video =   $user->videos()->create([
                'thumbnail'=>$request->image,
                'title'=>$request->title,
                'video_link'=>$request->link
             ]);
             return response()->json(['status'=>true, 'video'=>$video], 201);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>$th->getMessage()]);
        }
       
    }

public function createProgram(Request $request){
    try {
        $request->validate([
            'title' => 'required',
            'content' => 'required'
        ]);

        $program = Program::create([
            'title' => $request->title,
            'content' => $request->content
        ]);

        $sessions = $request->sessions;

        if ($sessions) {


            foreach ($sessions as $session) {
                $program->sections()->create([
                    'title'       => $session['title'] ?? 'Title',
                    'description' => $session['description'] ?? 'Description',
                    'video_link'  => $session['video'] ?? 'Video',
                    'time'        => $session['time'] ?? 'Time',
                    'date'        => $session['date'] ?? 'Date',
                ]);
            }
        }

        return response()->json([
            'status'  => true,
            'program' => $program->load('sections')
        ]);
    } catch (\Throwable $th) {
        return response()->json(['error' => $th->getMessage()]);
    }
}


    public function getMentors(Request $request){
        $mentors = MentorRequest::with('user')->where('status', 'approved')->get();
        return response()->json(['mentors'=>$mentors, 'status'=>true]);
    }
    public function getOrganizations(Request $request){
        $organizations = Organization::where('status', 'approved')->get();

        return response()->json(['status'=>true, 'organizations'=>$organizations]);
    }
public function deleteArticl($id){
    $article = Content::find($id);
    if(!$article){
        return response()->json(['error'=>'Article not found'], 404);
    }
    $article->delete();
    return response()->json(['status'=>true]);
}
public function updateHighlight(Request $request){
    try {
        //code...
        $request->validate(['speaker'=>'required', 'highlight'=>'required']);
        $highlight =  SpeakerHighlight::where('id', $request->highlightId)->first();
       $highlight->speaker_name = $request->speaker;
       $highlight->highlight = $request->highlight;
       $highlight->save();
        return response()->json(['status'=>true]);
    } catch (\Throwable $th) {
        //throw $th;
        return response()->json(['error'=>$th->getMessage()]);
    }
}
    public function createSpeakerHighlight(Request $request){

try {
    //code...
    $request->validate(['name'=>'required', 'highlight'=>'required']);
    $program = Program::where('id', $request->programId)->first();
   $newProgram =  $program->speakerHighlights()->create([
        'speaker_name'=>$request->name,
        'highlight'=>$request->highlight
    ]);
    return response()->json(['status'=>true, 'program'=>$newProgram]);
} catch (\Throwable $th) {
    //throw $th;
    return response()->json(['error'=>$th->getMessage()]);
}
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
    public function updateTitle(Request $request){
        $program = Program::find($request->programId);
        $program->title = $request->title;
        $program->content = $request->description;
        $program->save();
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

            $request->validate(['title'=>'required', 'eventDate'=>'required', 'startTime'=>'required', 'endTime'=>'required', 'link'=>'required']);
          $event =   Event::create([
                'title'=>$request->title,
                'event_date'=>$request->eventDate,
                'start_time'=>$request->startTime,
                'end_time'=>$request->endTime,
                'event_image'=>$request->eventImage,
                'members_to_notify' => $request->members ?? null,
                'event_link'=>$request->link

            ]);
            return response()->json(['status'=>true, 'event'=>$event]);

        } catch (\Throwable $th) {
            //throw $th;
            \Log::info($th->getMessage());
            return response()->json(['error'=>$th->getMessage()]);
        }
    }

    public function updateSession(Request $request){
     $session =    Section::where('id', $request->sessionId)->update([
            'title'=>$request->title,
            'description'=>$request->description,
            'video_link'=>$request->link,
            'time'=>$request->time,
            'date'=>$request->date,
        ]);
        return response()->json(['status'=>true, 'session'=>$session]);
    }
        public function editEvent(Request $request){
        try {
            //code...

            $request->validate(['title'=>'required', 'eventDate'=>'required', 'startTime'=>'required', 'endTime'=>'required', 'link'=>'required']);
            $event = Event::find($request->eventId);
          $event->update([
                'title'=>$request->title,
                'event_date'=>$request->eventDate,
                'start_time'=>$request->startTime,
                'end_time'=>$request->endTime,
                'event_image'=>$request->eventImage,
                'members_to_notify' => $request->members ?? null,
                'event_link'=>$request->link

            ]);
            return response()->json(['status'=>true]);

        } catch (\Throwable $th) {
            //throw $th;
            // \Log::info($th->getMessage());
            return response()->json(['error'=>$th->getMessage()]);
        }
    }

    public function deleteListing(Request $request){
        $listing = MentorListing::find($request->listingId);
        $listing->delete();
        return response()->json(['status'=>true]);
    }
}
