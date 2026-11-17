<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** Notifikasi umum di aplikasi, dengan email bila diminta (Alur Bisnis bagian 6). */
class Pemberitahuan extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public bool $mail = false,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->mail ? ['database', 'mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)->subject($this->title)->greeting('Halo '.$notifiable->firstName())->line($this->body);

        return ($this->url ? $message->action('Buka alloBos', $this->url) : $message)->salutation('Salam, tim alloBos');
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'body' => $this->body, 'url' => $this->url];
    }
}
