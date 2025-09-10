<?php

namespace App\Http\Services;

use App\Models\User;
use App\Models\MentorMatch;
use Illuminate\Support\Facades\Http;

class MentorMatchingService
{
    public function run()
    {
        $users = User::where('is_mentor', false)->get();
        \Log::info($users);

        foreach ($users as $user) {
            $mentor = User::where('profession', $user->profession)
                ->where('is_mentor', true)
                
                ->inRandomOrder()
                ->first();

            if ($mentor) {
                MentorMatch::updateOrCreate(
                    ['user_id' => $user->id],
                    ['mentor_id' => $mentor->id]
                );

                // Send notifications to Supabase
                $this->sendNotification($user->id, 'Mentor Match', "You’ve been matched to be {$mentor->first_name} {$mentor->last_name}'s mentee", $mentor->profile_picture, $mentor->profession,$mentor->first_name);

                $this->sendNotification($mentor->id, 'Mentor Match', "You’ve been matched to mentor {$user->first_name} {$user->last_name}", $user->profile_picture, $user->profession, $user->first_name);
            }
        }
    }

    private function sendNotification($userId, $title, $message, $profile_picture, $profession,$first_name)
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

            
        ]);
    }
}
