<x-mail::message>

# {{ __('mail.user_status_changed_header') }}

{{ $user->active ? __('mail.user_status_changed_body_active', ['name' => $user->name]) : __('mail.user_status_changed_body_inactive', ['name' => $user->name]) }}

{{ config('app.name') }}
</x-mail::message>