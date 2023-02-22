<x-mail::message>

# {{ __('mail.your_role_changed_subject') }}

{{ $user->admin ? __('mail.your_role_changed_body_granted') : __('mail.your_role_changed_body_revoked') }}

{{ config('app.name') }}
</x-mail::message>

