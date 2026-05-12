<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:app-user')]
#[Description('Command description')]
class AppUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
