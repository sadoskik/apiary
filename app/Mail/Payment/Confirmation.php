<?php declare(strict_types = 1);

namespace App\Mail\Payment;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Confirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $uid;
    public $payment;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($uid, $payment): void
    {
        $this->uid = $uid;
        $this->payment = $payment;
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
                $message->getHeaders()->addTextHeader('Reply-To', 'RoboJackets <treasurer@robojackets.org>');
            })->subject('[RoboJackets] Payment Processed')
            ->markdown('mail.payment.confirmation');
    }
}
