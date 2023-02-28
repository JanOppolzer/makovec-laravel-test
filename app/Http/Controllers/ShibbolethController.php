<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class ShibbolethController extends Controller
{
    public function create(): RedirectResponse|View|string
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
                'login_at' => now(),
            ]
        );

        $user->refresh();

        if ($user->login_at->eq($user->created_at)) {
            Log::channel('slack')->info($user->name.' has just created an account, activate it here: '.route('users.show', $user));
        }

        if (! $user->active) {
            return view('blocked');
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
