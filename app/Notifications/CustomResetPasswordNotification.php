<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class CustomResetPasswordNotification extends ResetPasswordNotification
{
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
        // Generate the password reset URL using the parent's $token
        $url = $this->resetUrl($notifiable);

        return (new MailMessage)
                ->from('no-reply@ecomputer.es', 'Template')
                ->subject('Restablecimiento de tu contraseña')
                ->markdown('emails.reset_password', ['resetUrl' => $url]);

    }

    /**
     * Get the password reset URL for the given user.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function resetUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'custom.password.reset', // Named route for reset password
            now()->addMinutes(60), // The expiration time for the link
            ['token' => $this->token, 'email' => rawurlencode($notifiable->getEmailForPasswordReset())]
        );
    }
}
