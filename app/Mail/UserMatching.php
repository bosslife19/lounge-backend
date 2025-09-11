<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserMatching extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $messages;
    public $name;

    public function __construct($subject,$messages,$name )
    {
        $this->name = $name;
        $this->messages = $messages;
        $this->subject = $subject;
    }

    public function build()
    {
        return $this->view('emails.kyc')
                    ->subject($this->subject)
                    ->with([
                        'name' => $this->name,
                        
                        'messages'=>$this->messages,
                    ]);
    }
}
