<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;

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
        //   $organization =   Organization::create([
        //         'name'=>$request->organizationName
        //   ]);

        //   $user->organization_id = $organization->id;
            $user->requests()->create([
                'type'=>'create_organization',
                'description'=>'Request to create organization: '.$request->organizationName,
                'status'=>'pending'
            ]);

            Organization::create([
                'name'=>$request->organizationName,
                'status'=>"pending"
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
            \Log::info($th->getMessage());

            return response()->json(['message'=>$th->getMessage()]);

        }

        
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
}
