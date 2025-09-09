<?php

namespace App\Http\Controllers;

use App\Models\MentorRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function createProfile(Request $request)
    {
        $user = $request->user();

        try {
            //code...
            $user->first_name = $request->firstName;
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
            \Log::info('heree');
            $organization = Organization::where('name', $request->organization)->first();
            $user->organization_id = $organization ? $organization->id : null;


        }
        $user->profile_status = 'complete';
        $user->save();

        return response()->json(['status'=>true, 'message'=>'Profile Created Successfully', 'user'=>$user]);
        } catch (\Throwable $th) {
            //throw $th;
            \Log::info($th->getMessage());

            return response()->json(['message'=>$th->getMessage()]);

        }

        
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
        $users = User::where('id', '!=', $request->user()->id)->where('role', '!=', 'admin')->get();

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


}
