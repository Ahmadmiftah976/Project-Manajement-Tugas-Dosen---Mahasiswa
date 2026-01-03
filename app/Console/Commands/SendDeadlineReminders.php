<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class SendDeadlineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:deadline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send deadline reminder notifications to students';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Sending deadline reminders...');
        
        $notificationService = new NotificationService();
        $notificationService->sendDeadlineReminders();
        
        $this->info('Deadline reminders sent successfully!');
        
        return 0;
    }
}