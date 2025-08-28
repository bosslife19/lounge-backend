<?php

namespace App\Http\Controllers;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class VerificationController extends Controller
{
    public function verifyEmail(Request $request)
    {
        try {
        $request->validate([

            'code' => 'required',
        ]);

        $verification = EmailVerification::where('code', intval($request->code))->first();
            \Log::info($verification);

        if (!$verification) {
            return response()->json(['error' => 'Invalid verification code.'], 400);
        }

        // $user = User::where('email', $request->email)->first();


        $user = User::where('id', $verification->user_id)->first();
        

        
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
            EmailVerification::where('code', intval($request->code))->delete(); 

            

            return response()->json(['message' => 'Email verified successfully.', 'status'=>true], 200);
        }

        return response()->json(['error' => 'User not found.']);
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json(['message' => $th->getMessage()], 500);
        }

    }
}