<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Mail\UserAccountCreated;
use App\Mail\UserRoleChanged;
use App\Mail\UserStatusChanged;
use App\Mail\YourAccountCreated;
use App\Mail\YourRoleChanged;
use App\Mail\YourStatusChanged;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('users.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = User::create($request->validated());
        $user->active = true;
        $user->update();

        Mail::send(new UserAccountCreated($user));
        Mail::send(new YourAccountCreated($user));

        Log::channel('slack')->info('A new account has been just created! Check it here: '.route('users.show', $user));

        return to_route('users.show', $user)
            ->with('status', __('users.added', ['name' => $user->name]));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $emails = explode(';', $user->emails);
        $categories = Category::all();

        return view('users.show', compact('user', 'emails', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        if (in_array($request->email, explode(';', $user->emails))) {
            $user->update(['email' => $request->email]);
        }

        if ($user->wasChanged()) {
            return to_route('users.show', $user)
                ->with('status', __('users.email_changed'));
        }

        return to_route('users.show', $user);
    }

    public function status(Request $request, User $user): RedirectResponse
    {
        $this->authorize('do-everything');

        if ($request->user()->is($user)) {
            return to_route('users.show', $user)
                ->with('status', __('users.cannot_toggle_your_status'))
                ->with('color', 'red');
        }

        $user->active = $user->active ? false : true;
        $user->update();

        Mail::send(new UserStatusChanged($user));
        Mail::send(new YourStatusChanged($user));

        $status = $user->active ? 'active' : 'inactive';
        $color = $user->active ? 'green' : 'red';

        return to_route('users.show', $user)
            ->with('status', __("users.{$status}", ['name' => $user->name]))
            ->with('color', $color);
    }

    public function role(Request $request, User $user): RedirectResponse
    {
        $this->authorize('do-everything');

        if ($request->user()->is($user)) {
            return to_route('users.show', $user)
                ->with('status', __('users.cannot_toggle_your_role'))
                ->with('color', 'red');
        }

        $user->admin = $user->admin ? false : true;
        $user->update();

        Mail::send(new UserRoleChanged($user));
        Mail::send(new YourRoleChanged($user));

        $role = $user->admin ? 'admined' : 'deadmined';
        $color = $user->admin ? 'indigo' : 'yellow';

        return to_route('users.show', $user)
            ->with('status', __("users.{$role}", ['name' => $user->name]))
            ->with('color', $color);
    }

    public function categories(Request $request, User $user): RedirectResponse
    {
        $this->authorize('do-everything');

        if ($request->user()->is($user)) {
            return to_route('users.show', $user)
                ->with('status', __('users.cannot_tweak_your_roles'))
                ->with('color', 'red');
        }

        $user->categories()->sync($request->categories);

        return to_route('users.show', $user)
            ->with('status', __('users.roles_updated'));
    }
}
