<?php

namespace Modules\Merchant\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Merchant\Models\Merchant;

/** Email to a business an admin invited to sell on DamnerLaor. */
class MerchantInvited extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Merchant $merchant)
    {
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
            ->subject('You\'re invited to sell on DamnerLaor')
            ->greeting('Hello'.($this->merchant->contact_name ? " {$this->merchant->contact_name}" : '').',')
            ->line("We've started an application for {$this->merchant->name} on DamnerLaor.")
            ->line('Download the DamnerLaor merchant app and upload your business documents. We review every application before you go live.');
    }
}
