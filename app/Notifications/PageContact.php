<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class PageContact extends Notification
{
    use Queueable;

    private $data = [];

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($nombre, $email, $asunto, $mensaje)
    {
        $this->data['nombre'] = $nombre;
        $this->data['email'] = $email;
        $this->data['asunto'] = $asunto;
        $this->data['mensaje'] = $mensaje;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                ->subject('Contacto desde ' . config('config.APP_NAME'))
                ->greeting('Nuevo contacto desde ' . config('config.APP_NAME'))
                ->line('Nombre: ' . $this->data['nombre'])
                ->line('Email: ' . $this->data['email'])
                ->line('Asunto: ' . $this->data['asunto'])
                ->line('Mensaje: ' . $this->data['mensaje']);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
