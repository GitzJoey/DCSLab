<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class AppHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:helper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Helper for this applications';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Available Helper:');
        $this->info('[1] Truncate All Master Data');
        $this->info('[2] Truncate All Transactions');
        $this->info('[3] Update Composer And NPM');
        $this->info('[4] Clear All Cache');
        $this->info('[5] ...');

        $choose = $this->ask('Choose Helper');

        switch ($choose) {
            case 1:
                break;
            case 2:
                break;
            case 3:
                $this->updateComposerAndNPM();
                break;
            case 3:
                $this->clearCache();
                break;
            case 4:
                break;
            case 5:
                break;
            default:
                break;
        }

        sleep(3);
        $this->info('Done!');
    }

    private function clearCache()
    {
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('debugbar:clear');
        Artisan::call('cache:clear');
        Artisan::call('clear-compiled');
    }

    private function updateComposerAndNPM()
    {
        $this->info('Starting Composer Update');
        exec('composer update');

        $this->info('Starting NPM Update');
        exec('npm update');

        $this->info('Starting Mix');
        if (App::environment('prod', 'production')) {
            $this->info('Executing for production enviroment');
            exec('npm run prod');
        } else {
            exec('npm run dev');
        }
    }
}
