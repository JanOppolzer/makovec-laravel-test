<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class ShibbolethController extends Controller
{
    public function create(): RedirectResponse|string
    {
        if (is_null(request()->server('Shib-Handler'))) {
            return 'login';
        }

        return redirect(
            request()
                ->server('Shib-Handler')
                .'/Login?target='
                .action('\\'.__CLASS__.'@store')
        );
    }

    public function store(): RedirectResponse
    {
        $mail = explode(';', request()->server('mail'));

        $user = User::updateOrCreate(
            ['uniqueid' => request()->server('uniqueId')],
            [
                'name' => request()->server('cn'),
                'email' => $mail[0],
                'emails' => count($mail) > 1 ? request()->server('mail') : null,
                'last_login' => now(),
            ]
        );

        if ($user->wasRecentlyCreated()) {
            Log::channel('slack')->info($user->name.' has just created an account, activate it here: '.route('users.show', $user));
        }

        $user->refresh();

        if (! $user->active) {
            return redirect('blocked');
        }

        Auth::login($user);
        Session::regenerate();

        return redirect()->intended('/');
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();
        Session::flush();

        return redirect('/Shibboleth.sso/Logout');
    }
}
