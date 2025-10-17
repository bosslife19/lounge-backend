<?php

namespace App\Http\Controllers;

use App\Http\Services\MentorMatchingService;
use App\Mail\UserMatching;
use App\Models\BenfitRequest;
use App\Models\Event;
use App\Models\March;
use App\Models\MentorMatch;
use App\Models\MentorRequest;
use App\Models\Organization;
use App\Models\SessionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function createProfile(Request $request)
    {
        $user = $request->user();

        try {
            //code...
            $user->first_name = $request->firstName;
            $user->name = "$request->firstName $request->lastName";
        $user->last_name = $request->lastName;
        $user->city = $request->location;
        $user->profession = $request->profession;
        $user->bio = $request->bio;
        $user->linkedin_url = $request->linkedin;
        $user->facebook_url = $request->facebook;
        $user->years_of_experience = $request->experience;
        $user->pronouns = $request->pronouns;
        $user->phone = $request->phone;
        $user->category = $request->category;
        $user->roots = $request->roots;
        
        if($request->profilePic){
            $user->profile_picture = $request->profilePic;

        }
        if($request->organizationName){
         
          $organization =   Organization::create([
                'name'=>$request->organizationName,
                'description'=>$request->organizationDescription,
                'logo'=>$request->organizationLogo,
                'email'=>$request->organizationEmail,
                'website_url'=>$request->organizationWebsite,
                'location'=>$request->organizationLocation,
                'status'=>'pending',
          ]);

       $user->organization_id = $organization->id;
            $user->requests()->create([
                'type'=>'create_organization',
                'name'=>$organization->name,
                'logo'=>$organization->logo,
                'email'=>$organization->email,
                'website'=>$organization->website_url,
                'description'=>'Request to create organization: '.$request->organizationName,
                'status'=>'pending'
            ]);

           
        }

        if($request->organization){

            $organization = Organization::where('name', $request->organization)->first();
            $user->organization_id = $organization ? $organization->id : null;


        }
        $user->profile_status = 'complete';
        $user->save();

        return response()->json(['status'=>true, 'message'=>'Profile Created Successfully', 'user'=>$user]);
        } catch (\Throwable $th) {
            //throw $th;
            // \Log::info($th->getMessage());
            \Log::info($th->getMessage());

            return response()->json(['message'=>$th->getMessage()]);

        }

        
    }
    public function claimBenefit(Request $request){
        $user = $request->user();
        $userPoints = $user->points;
        if($userPoints < $request->points){
            return response()->json(['error'=>'You do not have enough points to claim this benefit']);
        }
        $user->points = $user->points - $request->points;
        // $user->pointHistories()->create([
        //     'title'=>'Benefit Claimed',
        //     'description'=>"You claimed the benefit: ".$request->benefit,
        //     'addition'=>'-'.$request->points,
        // ]);
        $user->save();

        BenfitRequest::create([
            'name'=>$user->name,
            'profession'=>$user->profession,
            'benefit'=>$request->benefit,
            'points'=>$user->points,
        ]);

        return response()->json(['status'=>true, 'message'=>'Benefit Request Successful. Our team will reach out to you shortly.']);
    }
    public function checkMentorRequested(Request $request){
        $user = $request->user();
        $exists = MentorRequest::where('user_id', $user->id)->first();
        if($exists){
            return response()->json(['status'=>true, 'requested'=>true]);
        }
        return response()->json(['status'=>true, 'requested'=>false]);
    }
    public function checkOptedInCoffeeRoulette(Request $request){
        $user = $request->user();
        if($user->opted_in){
            return response()->json(['status'=>true, 'optedIn'=>true]);
        }
        return response()->json(['status'=>true, 'optedIn'=>false]);
    }
  public function editProfile(Request $request)
    {
        $user = $request->user();
      

        // Update fields only if they exist in the request
        if ($request->firstName) {
            $user->first_name = $request->firstName;
            $user->name = $request->firstName . ' ' . ($request->lastName ?? $user->last_name);
        }

        if ($request->lastName) {
            $user->last_name = $request->lastName;
        }

        if ($request->location) {
            $user->city = $request->location;
        }

        if ($request->profession) {
            $user->profession = $request->profession;
        }

        if ($request->bio) {
            $user->bio = $request->bio;
        }

        if ($request->linkedin) {
            $user->linkedin_url = $request->linkedin;
        }

        if ($request->facebook) {
            $user->facebook_url = $request->facebook;
        }

        if ($request->experience) {
            $user->years_of_experience = $request->experience;
        }

        if ($request->pronouns) {
            $user->pronouns = $request->pronouns;
        }

        if ($request->phone) {
            $user->phone = $request->phone;
        }

        if ($request->category) {
            $user->category = $request->category;
        }

        if ($request->roots) {
            $user->roots = $request->roots;
        }

        if ($request->profilePic) {
            $user->profile_picture = $request->profilePic;
        }

      
        if ($request->organization) {
            
            $organization = Organization::where('name', $request->organization)->first();
            $user->organization_id = $organization ? $organization->id : null;

        }

        $user->save();

        return response()->json([
            'status' => true,
            'user' => $user->with('organization')->first()
        ]);
    }
    public function getLinks(Request $request){
    $links = \App\Models\Link::latest()->get();
    return response()->json(['status'=>true, 'links'=>$links]);
}
public function requestToMentor(Request $request){
    $user = $request->user();
    $exists = MentorRequest::where('user_id', $user->id)->first();
    if($exists){
        return response()->json(['error'=>'You have already sent a mentorship request']);
    }
    $user->mentorRequest()->create([
        'years_of_experience'=>$user->years_of_experience,
        'name'=>$user->name
    ]);

    return response()->json(['status'=>true, 'message'=>'Mentor Request sent successfully. We are currently reviewing your request']);
}
    public function uploadProfilePicture(Request $request){
      $validated = $request->validate([
            'profilePic' => 'required|string',
        ]);

    $user = $request->user();
    $user->profile_picture = $request->profilePic;
    $user->save();

    return response()->json(['status'=>true, 'message'=>'Profile Picture Updated Successfully', 'user'=>$user]);

    }

    public function getAllUsers(Request $request){
        $users = User::where('role', '!=', 'admin')->where('profile_status', 'complete')->with('organization')->get();

        return response()->json(['users'=>$users, 'status'=>true]);
    }


    public function changePassword(Request $request){
        try {
            //code...
            $request->validate(['currentPassword'=>'required', 'newPassword'=>'required']);

            $user = $request->user();

            $samePassword = Hash::check($request->currentPassword, $user->password);
            if($samePassword){
                $user->password = Hash::make($request->newPassword);
                $user->save();
                return response()->json(['status'=>true, 'message'=>'Password Changed Successfully']);
            }else{
                return response()->json(['error'=>'The current password entered is not correct']);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message'=>$th->getMessage()]);
        }
    }
public function getEvents(){
    $events = Event::latest()->get();
    return response()->json(['events'=>$events, 'status'=>true], 200);
}
public function getMe($id){
    $user = User::where('id', $id)->with('organization')->first();

    return response()->json(['status'=>true, 'user'=>$user]);

}
public function respondToMarch(March $match, User $actor, string $response, $isMeeting)
{
  
    if ($actor->id === $match->user_id) {
        if($match->user_status =='accepted'||$match->user_status=='rejected'){
            
            return 'responded';
        }
        $match->user_status = $response; // 'accepted' or 'rejected'
    } elseif ($actor->id === $match->mentor_id) {
         if($match->mentor_status =='accepted'||$match->mentor_status=='rejected'){
            return 'responded';
        }
        $match->mentor_status = $response;
    } else {
        abort(403, 'Not part of this match.');
    }

    $match->save();

    // Check if both accepted
    if ($match->user_status === 'accepted' && $match->mentor_status === 'accepted') {
        // Bind mentor <-> user
        if($isMeeting){
        $mentee = User::find($match->user_id);
        $mentor = User::find($match->mentor_id);
        (new MentorMatchingService())->sendNotify($mentor->id,"You are now scheduled for a 15 minute call with $mentee->first_name", "We have sent you $mentee->first_name's email to your email inbox, please contact and confirm appointment");
        (new MentorMatchingService())->sendNotify($mentee->id,"You are now scheduled for a 15 minute call with $mentor->first_name", "We have sent you $mentor->first_name's email to your email inbox, please contact and confirm appointment");
        $mentor->points = $mentor->points +20;
        $mentor->pointHistories()->create([
                    'title'=>'Call Scheduled',
                    'description'=>'You were Scheduled for a 15 minute mentoring call',
                    'addition'=>'+20',
                ]);
        $mentee->points = $mentee->points + 5;
        $mentee->pointHistories()->create([
                    'title'=>'Call Scheduled',
                    'description'=>'You were Scheduled for a 15 minute call with a mentor',
                    'addition'=>'+5',
                ]);
        $mentor->save();
        $mentee->save();
         try {
    //code...
     Mail::to($mentee->email)->send(new UserMatching('New Matching Notification', "You have been scheduled for a 15 minute call with $mentor->first_name $mentor->last_name. you can contact him via his email $mentor->email to set up a meeting and connect", $mentee->name));
     Mail::to($mentor->email)->send(new UserMatching('New Matching Notification', "You have been scheduled for a 15 minute call with $mentee->first_name $mentee->last_name. you can contact him via his email $mentee->email to set up a meeting and connect", $mentor->name));
     return $match;
 } catch (\Throwable $th) {
    //throw $th;
    \Log::info($th->getMessage());
 }
        }
        
        $mentee = User::find($match->user_id);
        $mentor = User::find($match->mentor_id);
        (new MentorMatchingService())->sendNotify($mentor->id,"You are now $mentee->first_name's mentor", "$mentee->first_name is now your latest mentee");
        (new MentorMatchingService())->sendNotify($mentee->id,"You are now $mentor->first_name's mentee", "$mentor->first_name is now your latest mentor");
         try {
    //code...
     Mail::to($mentee->email)->send(new UserMatching('New Mentor Notification', "$mentor->first_name $mentor->last_name is now your new mentor. you can contact him via his email $mentor->email to set up a meeting and connect", $mentee->name));
     Mail::to($mentor->email)->send(new UserMatching('New Mentor Notification', "$mentee->first_name $mentee->last_name is now your new mentee. you can contact him via his email $mentee->email to set up a meeting and connect", $mentor->name));
 } catch (\Throwable $th) {
    //throw $th;
    \Log::info($th->getMessage());
 }
        $match->mentor->mentees()->syncWithoutDetaching([$match->user_id]);
    }

    // If either rejects → clean up
    if ($match->user_status === 'rejected' || $match->mentor_status === 'rejected') {
        
        $match->delete();
    }

    return $match;
}


public function respondToMatch(Request $request){
    $match = March::find($request->marchId);
  
    $user = User::find($request->user()->id);
   $respond = $this->respondToMarch($match, $user, $request->response, $request->isMeeting);
   if($respond =='responded'){
    return response()->json(['error'=>'You have already responded to this match']);
   }
    if(!$request->stay){
 (new MentorMatchingService())->deleteNotify($request->notId);
    }
  
   if($user->id != $match->mentor_id && $request->response=='accepted' && !$request->isMeeting){
(new MentorMatchingService())->sendNotify($match->mentor_id,'Mentorship match accepted', "$user->name has acccepted to be your mentee");
   }
   if($user->id == $match->mentor_id && $request->response=='accepted' &&!$request->isMeeting){
(new MentorMatchingService())->sendNotify($match->user_id,'Mentorship match accepted', "$user->name has acccepted to be your mentor");
   }
      if($user->id != $match->mentor_id && $request->response=='accepted' &&$request->isMeeting){
(new MentorMatchingService())->sendNotify($match->mentor_id,'Meeting match accepted', "$user->name has acccepted to be in a fifteen minute call with you");
   }
   if($user->id == $match->mentor_id && $request->response=='accepted' && $request->isMeeting){
(new MentorMatchingService())->sendNotify($match->user_id,'Meeting match accepted', "$user->name has acccepted to be in a meeting with you");
   }
   
   
   
   return response()->json(['status'=>true]);
    
}
public function getMyMentors(Request $request){
    $user = $request->user();
    $mentors = $user->mentors;
    return response()->json(['mentors'=>$mentors]);

}
public function getUserPointHistories($id){
    $user = User::find($id);
    $histories = $user->pointHistories;
    return response()->json(['histories'=>$histories], 200);
}
public function readNotification(Request $request){
    $delete= (new MentorMatchingService())->deleteNotify($request->notId);
    if($delete){
        return response()->json(['status'=>true]);
    }
    return response()->json(['error'=>'Server Error']);
}

public function requestSession(Request $request){
    $user = $request->user();
    $mentor = User::find($request->mentorId);

    if($request->user()->id == $request->mentorId) return response()->json(['error'=>'You cannot request yourself as mentor']);
    $sessionRequest = new SessionRequest();
    $exists = $sessionRequest->where('user_id', $user->id)->where('mentor_id', $request->mentorId)->first();
    if($exists){
        return response()->json(['error'=>'You cannot request for a session twice with this mentor']);
    }
 $sessionRequest->create([
    'user_id'=>$user->id,
    'mentor_id'=>$request->mentorId,
 ]);
 MentorMatch::updateOrCreate(
                    ['user_id' => $user->id],
                    ['mentor_id' => $request->mentorId]
                );
$match =  March::create([
    'user_id'   => $user->id,
    'mentor_id' => $request->mentorId,
    'user_status'=>'accepted'
]);

 (new MentorMatchingService())->sendNotification($request->mentorId,'Session Request', "$user->name has requested a session with you", $user->profile_picture,$user->profession, $user->first_name, $match->id, false);
 try {
    //code...
     Mail::to($mentor->email)->send(new UserMatching('Session Request', "$user->first_name $user->last_name has requested a session with you", $mentor->name));
 } catch (\Throwable $th) {
    //throw $th;
    \Log::info($th->getMessage());
 }

 return response()->json(['status'=>true]);
}

public function optInForRoulette(Request $request){
    $user = $request->user();
    if($user->opted_in){
        return response()->json(['error'=>"You have already opted in for this week's roulette"]);
    }
    $user->opted_in = true;
    
    $user->save();
    return response()->json(['status'=>true]);
}

}
