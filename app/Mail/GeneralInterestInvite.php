<?php declare(strict_types = 1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GeneralInterestInvite extends Mailable
{
    use Queueable, SerializesModels;

    public $app_url;
    public $visit_token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($visit_token): void
    {
        $this->app_url = url('/');
        $this->visit_token = $visit_token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->from('noreply@my.robojackets.org', 'RoboJackets')
            ->withSwiftMessage(static function ($message): void {
                $message->getHeaders()->addTextHeader('Reply-To', 'RoboJackets <hello@robojackets.org>');
            })->subject('RoboJackets General Interest Event - RSVP Requested')
            ->markdown('mail.generalinterest.invite');
    }
}
