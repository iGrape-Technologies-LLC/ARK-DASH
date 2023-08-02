<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Transaction;

use App\Utils\UtilGeneral;

class NewSellToCustomer extends Mailable
{
    use Queueable, SerializesModels;

    protected $transaction;
    protected $title;
    protected $extra_info;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Transaction $transaction, $extra_info)
    {
        $this->title = "Gracias por su compra!";        
        $this->transaction = $transaction;
        $this->extra_info = $extra_info;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->subject($this->title)
                ->view('emails.user.sell_customer_modern')
                ->with([
                    'transaction'=>$this->transaction, 
                    'title' => $this->title,
                    'extra_info' => $this->extra_info
                ]);

    }
}
