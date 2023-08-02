<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Transaction;

use App\Utils\UtilGeneral;

class ConfirmOfflineSellToCustomer extends Mailable
{
    use Queueable, SerializesModels;

    protected $transaction;
    protected $title;    

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction)
    {
        $this->title = "Confirmación de compra!";        
        $this->transaction = $transaction;        
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->subject($this->title)
                ->view('emails.user.offline_sell_confirm_customer_modern')
                ->with([
                    'transaction'=>$this->transaction, 
                    'title' => $this->title
                ]);

    }
}
