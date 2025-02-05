<?php
namespace Jiny\Auth\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\URL;

class VerifyEmail extends Notification
{
    // $this->user를 사용하지 않고도 Notification 클래스나
    // VerifyEmail 클래스에 직접 값을 전달합니다.
    // Notification 클래스의 생성자를 통해 값을 전달하고,
    // 해당 값을 이용하여 이메일을 구성할 수 있습니다.
    protected $verificationToken;

    public function __construct($verificationToken)
    {
        $this->verificationToken = $verificationToken;
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    /**
     * The callback that should be used to create the verify email URL.
     *
     * @var \Closure|null
     */
    public static $createUrlCallback;

    /**
     * The callback that should be used to build the mail message.
     *
     * @var \Closure|null
     */
    public static $toMailCallback;

    /**
     * Get the notification's channels.
     *
     * @param  mixed  $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $verificationUrl);
        }

        return $this->buildMailMessage($verificationUrl);
    }

    /**
     * Get the verify email notification mail message for the given URL.
     *
     * @param  string  $url
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    protected function buildMailMessage($url)
    {
        // return (new MailMessage)
        //     ->subject(Lang::get('이메일 확인요청'))
        //     ->line(Lang::get('Please click the button below to verify your email address.'))
        //     ->action(Lang::get('Verify Email Address'), $url)
        //     ->line(Lang::get('If you did not create an account, no further action is required.'));

        $viewMail = "jiny-auth::auth.verify.mail";
        return (new MailMessage)
            ->subject(Lang::get('이메일 확인요청'))
            ->action(Lang::get('Verify Email Address'), $url)
            ->view(
                $viewMail, // 새로 생성한 Blade 뷰
                ['url' => $url] // 전달할 데이터
            );
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        if (static::$createUrlCallback) {
            return call_user_func(static::$createUrlCallback, $notifiable);
        }

        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Set a callback that should be used when creating the email verification URL.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function createUrlUsing($callback)
    {
        static::$createUrlCallback = $callback;
    }

    /**
     * Set a callback that should be used when building the notification mail message.
     *
     * @param  \Closure  $callback
     * @return void
     */
    public static function toMailUsing($callback)
    {
        static::$toMailCallback = $callback;
    }
}
