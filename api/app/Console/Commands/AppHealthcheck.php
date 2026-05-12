<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:app-healthcheck')]
#[Description('Command description')]
class AppHealthcheck extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
