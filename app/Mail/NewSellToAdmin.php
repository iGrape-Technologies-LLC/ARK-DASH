<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Transaction;

use App\Utils\UtilGeneral;

class NewSellToAdmin extends Mailable
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
        $this->title = "Nueva venta!";        
        $this->transaction = $transaction;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->subject($this->title)->view('emails.user.sell_admin_modern')->with(['transaction'=>$this->transaction, 'title' => $this->title]);

    }
}
