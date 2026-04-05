<?php

namespace App\Observers;

use App\Models\DigestSetting;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        DigestSetting::create([
            'user_id'        => $user->id,
            'digest_enabled' => true,
            'frequency'      => 'daily',
            'send_time'      => '07:00:00',
            'timezone'       => 'UTC',
            'max_articles'   => 5,
        ]);
    }
}
