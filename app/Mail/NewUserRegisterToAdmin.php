<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\User;

use App\Utils\UtilGeneral;

class NewUserRegisterToAdmin extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $title;
    protected $description;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->title = "Nuevo cliente registrado";
        $this->description = "Un nuevo cliente se ha registrado en su plataforma.";
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $datas = [];

        $datas[] = array(
            'title' => 'Nombre',
            'content' => $this->user->name . ' ' . $this->user->lastname
        );

        $datas[] = array(
            'title' => 'Email',
            'content' => $this->user->email
        );

        $datas[] = array(
            'title' => 'Fecha',
            'content' => UtilGeneral::format_date(now())
        );

        return $this->subject($this->title)->view('emails.user.register_modern')->with(['datas'=>$datas, 'title' => $this->title, 'description'=>$this->description]);

    }
}
