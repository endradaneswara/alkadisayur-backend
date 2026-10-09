<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtp extends Notification
{
    public function __construct(public readonly string $otp) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode OTP Reset Password')
            ->greeting('Halo,')
            ->line('Gunakan kode OTP berikut untuk mengatur ulang password akun AlkadiSayur Anda:')
            ->line($this->otp)
            ->line('Kode ini berlaku selama 10 menit dan hanya dapat digunakan satu kali.')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini.');
    }
}
