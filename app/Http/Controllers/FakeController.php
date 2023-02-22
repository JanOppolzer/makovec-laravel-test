<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FakeController extends Controller
{
    public function login(int $id = null): RedirectResponse
    {
        if (! app()->environment(['local', 'testing'])) {
            dd('Only for `local` and `testing` environments!');
        }

        $user = User::findOrFail($id ?? request('id'));

        if (! $user->active) {
            return redirect('blocked');
        }

        Auth::login($user);
        Session::regenerate();

        return redirect()->intended('/');
    }

    public function logout(): RedirectResponse
    {
        if (! app()->environment(['local', 'testing'])) {
            dd('Only for `local` and `testing` environments!');
        }

        Auth::logout();
        Session::flush();

        return redirect('/');
    }
}
