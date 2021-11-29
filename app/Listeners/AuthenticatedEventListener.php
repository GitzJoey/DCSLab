<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;

class AuthenticatedEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $this->viewShareSelectedCompany();
    }

    private function viewShareSelectedCompany()
    {
        $usr = Auth::user();
        $selectedCompany = null;
        if (empty(session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY')))) {
            if ($usr->companies()->count() != 0) {
                $selectedCompany = $usr->companies()->where('default', '=', 1)->first()->hId;
                session()->put(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'), $selectedCompany);
            }
        } else {
            $selectedCompany = session(Config::get('const.DEFAULT.SESSIONS.SELECTED_COMPANY'));
        }

        View::share('selectedCompany', $selectedCompany);
    }
}