<?php

namespace App\Http\Controllers;

use App\Models\MentorListing;
use Illuminate\Http\Request;

class MentorListingController extends Controller
{
    public function getMyListings( Request $request){
        $user = $request->user();
        $listings = $user->mentorListing()->whereHas('user', function ($query) {
    // $query->where('is_mentor', true);
})->with('user')->get();
        return response()->json(['status'=>true, 'listings'=>$listings]);

    }

    public function getAllListings(){
       $listings = MentorListing::whereHas('user', function ($query) {
    $query->where('is_mentor', true);
})->with('user')->get();
        return response()->json(['status'=>true, 'listings'=>$listings]);
    }
}
