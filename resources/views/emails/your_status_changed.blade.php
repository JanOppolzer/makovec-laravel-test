<x-mail::message>

# {{ __('mail.your_status_changed_subject') }}

{{ $user->active ? __('mail.your_status_changed_body_active') : __('mail.your_status_changed_body_inactive') }}

{{ config('app.name') }}
</x-mail::message>
