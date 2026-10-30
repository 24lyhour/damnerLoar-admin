<?php

namespace Modules\Merchant\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Merchant\Models\Merchant;

/** Email to the merchant's contact address listing what to fix (spec 03.2). */
class MerchantChangesRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Merchant $merchant,
        public readonly string $message,
    ) {
        $this->afterCommit();
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('DamnerLaor: please update your application')
            ->greeting("Hello {$this->merchant->contact_name},")
            ->line("We reviewed the application for {$this->merchant->name} and need a few changes before we can approve it:")
            ->line($this->message)
            ->line('Please update your details in the DamnerLaor merchant app. We will review it again as soon as you do.');
    }
}
