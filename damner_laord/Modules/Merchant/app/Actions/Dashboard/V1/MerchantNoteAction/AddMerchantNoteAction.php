<?php

namespace Modules\Merchant\Actions\Dashboard\V1\MerchantNoteAction;

use App\Models\User;
use Modules\Merchant\Models\Merchant;
use Modules\Merchant\Models\MerchantNote;

class AddMerchantNoteAction
{
    public function execute(Merchant $merchant, User $author, string $body): MerchantNote
    {
        $note = new MerchantNote(['body' => trim($body)]);
        $note->merchant()->associate($merchant);
        $note->author()->associate($author);
        $note->save();

        return $note;
    }
}
