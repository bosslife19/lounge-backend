<?php

namespace App\Console\Commands;

use App\Http\Services\MentorMatchingService;
use Illuminate\Console\Command;


class MatchMentors extends Command
{
    protected $signature = 'mentors:match';
    protected $description = 'Run weekly mentor-user matching';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(MentorMatchingService $service)
    {
        $service->run();
        $this->info('Mentor matching completed successfully.');
    }
}
