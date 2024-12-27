<?php
namespace Jiny\Auth\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;

/**
 * 사용자 메일 발송
 */
class UserMail extends Mailable
{
    use Queueable, SerializesModels;

    public $content;
    public $from;
    public $subject;
    public $address;
    public $name;
    public $reply;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        // $this->address = 'jiny@jiny.dev';
        // $this->name = 'Jiny';
    }

    public function from($address, $name = null)
    {
        $this->address = $address;
        if ($name) {
            $this->name = $name;
        }
        return $this;
    }

    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function reply($reply)
    {
        $this->reply = $reply;
        return $this;
    }


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->address, $this->name),
            // replyTo: [
            //     new Address($this->from, 'Hojin Lee'),
            // ],
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {

        // // 첨부파일 추가
        // if(file_exists($filename)) {
        //     $this->attach($filename, [
        //         'as' => basename($filename),
        //         'mime' => 'application/octet-stream'
        //     ]);
        // }


        return new Content(
            view: 'jiny-auth::emails.usermail',
            with: [
                'content' => $this->content
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
