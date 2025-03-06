<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CreateUserFolder
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        try {
            $user = $event->user;
            $folderName = 'user_' . $user->id;
            Storage::disk('local')->makeDirectory($folderName);
        } catch (Throwable $t) {
            Log::error($t->getMessage());
        }
    }
}
