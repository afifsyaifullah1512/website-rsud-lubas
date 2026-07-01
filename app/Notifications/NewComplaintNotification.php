<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi pengaduan baru kepada Admin_User berperan
 * `petugas-pengaduan` / `super-admin`.
 *
 * Memenuhi Requirement 11.4 dan 32.2 — payload TIDAK menyertakan body
 * `message`/`email`/`phone` pengaduan untuk mencegah PII bocor ke log
 * mail driver.
 */
class NewComplaintNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Complaint $complaint,
    ) {
    }

    /**
     * @return array<int,string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Pengaduan Baru — '.$this->complaint->ticket_number)
            ->greeting('Halo,')
            ->line('Pengaduan baru telah masuk dengan tiket **'.$this->complaint->ticket_number.'**.')
            ->line('Subjek: '.$this->complaint->subject)
            ->action('Buka Inbox Pengaduan', url('/admin/complaints'))
            ->line('Mohon segera ditindaklanjuti.');
    }

    /**
     * Payload untuk channel database (in-app notification).
     *
     * Sengaja TIDAK mencantumkan body / PII pengadu — Requirement 32.2.
     *
     * @return array<string,mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'complaint_id' => $this->complaint->id,
            'ticket_number' => $this->complaint->ticket_number,
            'subject' => $this->complaint->subject,
        ];
    }
}
