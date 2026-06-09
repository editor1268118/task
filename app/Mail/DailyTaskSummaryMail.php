<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyTaskSummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $metrics;
    public $user;

    public function __construct($user, $metrics)
    {
        $this->user = $user;
        $this->metrics = $metrics;
    }

    public function build()
    {
        return $this->subject('Amigos TMS - Daily Task Summary')
                    ->view('emails.reports.daily_summary');
    }
}
