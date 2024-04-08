<?php
namespace Jiny\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeEmailNotification extends Notification
{
    use Queueable;

    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)->view(
            'jinyauth::emails.welcome', // 새로 생성한 Blade 뷰
            ['user' => $this->user] // 전달할 데이터
        );

        /*
        return (new MailMessage)
                    ->subject('Welcome to Our Website!')
                    ->greeting('Hello ' . $this->user->name . '!')
                    ->line('Thank you for signing up on our website.')
                    ->line('We are excited to have you with us.')
                    ->line('If you have any questions or need assistance, feel free to contact us.')
                    ->action('Visit our Website', url('/'))
                    ->line('Thank you again for joining us!');
        */
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
