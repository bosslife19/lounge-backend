<?php

namespace App\Http\Services;

use App\Mail\UserMatching;
use App\Models\March;
use App\Models\User;
use App\Models\MentorMatch;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class MentorMatchingService
{
    public function run()
    {
        $users = User::where('is_mentor', false)->where('opted_in', true)->get();
   
        
        foreach ($users as $user) {
            $mentor = User::where('profession', $user->profession)
                ->where('is_mentor', true)->where('opted_in', true)
                
                ->inRandomOrder()
                ->first();
               

            if ($mentor) {

                MentorMatch::updateOrCreate(
                    ['user_id' => $user->id],
                    ['mentor_id' => $mentor->id]
                );
$match = March::create([
    'user_id'   => $user->id,
    'mentor_id' => $mentor->id,
]);
                // Send notifications to Supabase
                $this->sendNotification($user->id, 'Call Matching', "You’ve been matched to be in a 15 minute call with {$mentor->first_name} {$mentor->last_name}", $mentor->profile_picture, $mentor->profession,$mentor->first_name, $match->id, true);

                $this->sendNotification($mentor->id, 'Call Matching', "You’ve been matched to be in a 15 minute call with {$user->first_name} {$user->last_name}", $user->profile_picture, $user->profession, $user->first_name, $match->id, true);
                try {
                    //code...
                     Mail::to($user->email)->send(new UserMatching('New Weekly Matching', "You have been matched to be in a 15 minute call with $mentor->first_name $mentor->last_name, this is his email $mentor->email", $user->name));
                     Mail::to($mentor->email)->send(new UserMatching('New Weekly Matching', "You have been matched to be in a 15 minute call with $user->first_name $user->last_name, this is his email $user->email", $mentor->name));
                } catch (\Throwable $th) {
                    //throw $th;
                    \Log::info($th->getMessage());
                }
                $user->opted_in = false;
                $user->points = $user->points + 10;
                
                $user->save();
                $mentor->points = $mentor->points + 10;
                $mentor->opted_in = false;
                $mentor->save();
            }
        }
    }

    public function sendNotification($userId, $title, $message, $profile_picture, $profession,$first_name, $match_id, $is_meeting)
    {
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_KEY');

        Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => "Bearer {$supabaseKey}",
            'Content-Type' => 'application/json',
        ])->post("{$supabaseUrl}/rest/v1/notifications", [
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'=>'mentor_matching',
            'profession'=>$profession,
            'profile_picture'=>$profile_picture,
            'first_name'=>$first_name,
            'match_id'=>$match_id,
            'is_meeting'=>$is_meeting

            
        ]);
    }
        public function sendNotify($userId, $title, $message)
    {
        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_KEY');

        Http::withHeaders([
            'apikey' => $supabaseKey,
            'Authorization' => "Bearer {$supabaseKey}",
            'Content-Type' => 'application/json',
        ])->post("{$supabaseUrl}/rest/v1/notifications", [
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'=>'user_notification',


            
        ]);
    }
    public function deleteNotify($notificationId)
{
    $supabaseUrl = env('SUPABASE_URL');
    $supabaseKey = env('SUPABASE_KEY');

    $response = Http::withHeaders([
        'apikey' => $supabaseKey,
        'Authorization' => "Bearer {$supabaseKey}",
    ])->delete("{$supabaseUrl}/rest/v1/notifications?id=eq.{$notificationId}");

    return $response->successful();
}

}
