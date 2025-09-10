<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\MatchMentors; 


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');





// schedule the command every Monday at 09:00 (server timezone)
Schedule::command(MatchMentors::class)
    ->mondays()
    ->at('09:00')
    ->timezone('Africa/Lagos'); // optional: set schedule timezone

