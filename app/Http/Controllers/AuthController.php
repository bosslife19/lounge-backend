<?php

namespace App\Http\Controllers;

use App\Models\EmailVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            //code...
                    $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $this->sendVerificationCode($user);

         $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully. Please check your email for verification.',
            'token' => $token,
            'status'=>true,
            'user'=>$user
        ], 201);
        } catch (\Throwable $th) {
            //throw $th
           
            return response()->json(['message' => $th->getMessage()], 500);
        }

    }

public function login(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            // Generate Sanctum token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful.',
                'token' => $token,
                'user'=>$user,
                'status'=>true
            ], 200);
        }

        return response()->json(['error' => 'Invalid credentials.'], 401);
    } catch (\Throwable $th) {
        return response()->json(['message' => $th->getMessage()], 500);
    }
}

public function resendEmailOtp(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|email',
        ]);

        $code = random_int(1000, 9999);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Send email
        Mail::raw("Your verification code is: $code", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Email Verification Code');
        });

        // Save or update verification code
        EmailVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'email' => $user->email,
                'code'  => $code,
            ]
        );

        return response()->json(['message' => 'Verification code resent successfully', 'status'=>true], 200);

    } catch (\Throwable $th) {
        return response()->json(['message' => 'Something went wrong', 'error' => $th->getMessage()], 500);
    }
}

    public function sendVerificationCode(User $user)
    {
         $code = random_int(1000, 9999);

        // Store the code in the database or send it via email
        // For demonstration, we will just send it via email
       
        Mail::raw("Your verification code is: $code", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Email Verification Code');
        });
         $user->password_otp = $code;
         $user->save();

        // Save the code in the database associated with the user
        // This part should be implemented based on your database structure
    }

    public function checkEmailExists(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
            ]);

            $user = User::where('email', $request->email)->first();

            if(!$user){
                return response()->json(['error' => 'Email does not exist', 'status'=>false], 404);
            }
            $this->sendVerificationCode($user);

            return response()->json(['status'=>true], 200);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Something went wrong', 'error' => $th->getMessage()], 500);
        }
    }

    public function resetPassword(Request $request){
        try {
            //code...
            $request->validate([
                'email'=>'required',
                'otp'=>'required',
                'password'=>'required'
            ]);
            $user = User::where('email', $request->email)->first();

            if(!$user){
                return response()->json(['error'=>'User with this email does not exist']);
            }
            if($user->password_otp != intval($request->otp)){
                return response()->json(['error'=>'OTP does not match']);
            }
            $user->password = bcrypt($request->password);
            $user->password_otp = null;
            $user->save();

            return response()->json(['status'=>true, 'message'=>'Password changed successfully'], 200);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json(['message'=>$th->getMessage()]);
        }
    }
}