<?php

namespace App\Mail;

use App\User;
use App\Entity\Trade;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TradeCreated extends Mailable
{
    use Queueable, SerializesModels;

    protected $trade;
    protected $seller;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Trade $trade, User $seller)
    {
        $this->trade  = $trade;
        $this->seller = $seller;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.tradeCreated')
                    ->with([
                        'lotId'  => $this->trade->lot_id,
                        'amount' => $this->trade->amount,
                        'seller' => $this->seller->name,
                    ]);
    }
}
