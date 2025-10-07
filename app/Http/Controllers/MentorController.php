<?php

namespace App\Http\Controllers;

use App\Models\MentorListing;
use Illuminate\Http\Request;

class MentorController extends Controller
{
   

      public function createListing(Request $request){
        try {
            $request->validate(['accessEmail'=>'required', 'description'=>'required', 'calendly'=>'required', 'title'=>'required', 'category'=>'required']);
            $user = $request->user();
            // if(!$user->is_mentor){
            //     return response()->json(['error'=>'Only users approved as mentors can create listings']);
            // }
            $exists = MentorListing::where('user_id', $user->id)->first();
            if($exists){
                return response()->json(['error'=>'You cannot create more than one listing']);
            }
           $listing =  $user->mentorListing()->create([
                'title'=>$request->title,
                'access_email'=>$request->accessEmail,
                'description'=>$request->description,
                'price'=>$request->price,
                'is_free'=>$request->isFree,
                'calendly'=>$request->calendly,
                'preparation_notice'=>$request->preparatoryNote,
                'category'=>$request->category,

            ]);
            return response()->json(['status'=>true, 'listing'=>$listing->where('mentor_id', $user->id)->with("")]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>$th->getMessage()]);
        }
      }

      public function editListing(Request $request){
        try {
            // $request->validate(['accessEmail'=>'required', 'description'=>'required', 'calendly'=>'required', 'title'=>'required']);
            // $user = $request->user();
            // if(!$user->is_mentor){
            //     return response()->json(['error'=>'Only users approved as mentors can create listings']);
            // }
            // $exists = MentorListing::where('user_id', $user->id)->first();
            // if($exists){
            //     return response()->json(['error'=>'You cannot create more than one listing']);
            // }
$mentorListing = MentorListing::findOrFail($request->listingId);

$data = array_filter($request->only([
    'title',
    'accessEmail',
    'description',
    'price',
    'isFree',
    'calendly',
    'preparatoryNote',
]), function ($value) {
    return !is_null($value) && $value !== '';
});

// Map request keys to DB column names
$mappedData = [
    'title' => $data['title'] ?? $mentorListing->title,
    'access_email' => $data['accessEmail'] ?? $mentorListing->access_email,
    'description' => $data['description'] ?? $mentorListing->description,
    'price' => $data['price'] ?? $mentorListing->price,
    'is_free' => $data['isFree'] ?? $mentorListing->is_free,
    'calendly' => $data['calendly'] ?? $mentorListing->calendly,
    'preparation_notice' => $data['preparatoryNote'] ?? $mentorListing->preparation_notice,
];

$mentorListing->update($mappedData);

$user = $request->user();
return response()->json(['status' => true, 'listings'=> $user->mentorListing()->with('user')->get()]);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>$th->getMessage()]);
        }
      }}
