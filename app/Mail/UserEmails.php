<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserEmails extends Mailable
{
    use Queueable, SerializesModels;

    private $email_view_name;
    private $email_data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($view, $data)
    {
        $this->email_view_name = $view;
        $this->email_data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view($this->email_view_name,$this->email_data);
    }
}
