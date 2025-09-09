<?php

namespace App\Http\Controllers;

use App\Models\MentorListing;
use Illuminate\Http\Request;

class MentorController extends Controller
{
   

      public function createListing(Request $request){
        try {
            $request->validate(['accessEmail'=>'required', 'description'=>'required', 'calendly'=>'required', 'title'=>'required']);
            $user = $request->user();
            if(!$user->is_mentor){
                return response()->json(['error'=>'Only users approved as mentors can create listings']);
            }
            $exists = MentorListing::where('user_id', $user->id)->first();
            if($exists){
                return response()->json(['error'=>'You cannot create more than one listing']);
            }
            $user->mentorListing()->create([
                'title'=>$request->title,
                'access_email'=>$request->accessEmail,
                'description'=>$request->description,
                'price'=>$request->price,
                'is_free'=>$request->isFree,
                'calendly'=>$request->calendly,
                'preparation_notice'=>$request->preparatoryNote,

            ]);
            return response()->json(['status'=>true]);
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
            $mentorListing = MentorListing::where('id', $request->listingId)->first();
            $mentorListing->update([
                'title'=>$request->title,
                'access_email'=>$request->accessEmail,
                'description'=>$request->description,
                'price'=>$request->price,
                'is_free'=>$request->isFree,
                'calendly'=>$request->calendly,
                'preparation_notice'=>$request->preparatoryNote,
                

            ]);
            return response()->json(['status'=>true]);
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['error'=>$th->getMessage()]);
        }
      }}
