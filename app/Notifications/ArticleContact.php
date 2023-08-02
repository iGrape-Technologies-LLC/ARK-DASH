<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ArticleContact extends Notification
{
    use Queueable;

    private $data = [];

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($id, $name, $email, $message)
    {
        $this->data['id'] = $id;
        $this->data['name'] = $name;
        $this->data['email'] = $email;
        $this->data['message'] = $message;
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
                ->subject('Consulta desde ' . config('config.APP_NAME') )
                ->greeting('Hay un interesado en tu artículo!')
                ->line('Nombre: ' . $this->data['name'])
                ->line('Email de contacto: ' . $this->data['email'])
                ->line('Mensaje: ' . $this->data['message'])
                ->action('Ver artículo', route('articledetail', $this->data['id']));
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
