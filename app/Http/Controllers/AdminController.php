<?php

namespace App\Http\Controllers;

use App\Models\MentorRequest;
use App\Models\Organization;
use App\Models\Request as ModelsRequest;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function getOrganizationRequests(Request $request){
        $requests = ModelsRequest::with('user')->where('status', 'pending')->where('type', 'create_organization')->get();

        return response()->json(['status'=>true, 'requests'=>$requests]);
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
}
