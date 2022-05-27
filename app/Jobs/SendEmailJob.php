<?php

namespace App\Jobs;

use App\Mail\AssigneeEmails;
use App\Mail\UserEmails;
use Illuminate\Http\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $email_data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email_data)
    {
        $this->email_data = $email_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new UserEmails('email.welcome_user',$this->email_data);
        Mail::to('umar.asif@viltco.com')->send($email);
        
        $email = new AssigneeEmails('email.assign_user',$this->email_data);
        Mail::to('umar.asif@viltco.com')->send($email);
    }
}
