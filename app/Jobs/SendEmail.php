<?php

namespace App\Jobs;

use App\User;
use App\Entity\Trade;
use Illuminate\Support\Facades\Mail;
use App\Mail\TradeCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public $trade;
    public $seller;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Trade $trade, User $seller)
    {
        $this->trade  = $trade;
        $this->seller = $seller;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->seller)
            ->send(new TradeCreated($this->trade, $this->seller));
    }
}
