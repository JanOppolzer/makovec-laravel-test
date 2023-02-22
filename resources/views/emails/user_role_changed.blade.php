<x-mail::message>

# {{ __('mail.user_role_changed_header') }}

{{ $user->admin ? __('mail.user_role_changed_body_granted', ['name' => $user->name]) : __('mail.user_role_changed_body_revoked', ['name' => $user->name]) }}

{{ config('app.name') }}
</x-mail::message>
