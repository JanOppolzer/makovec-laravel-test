<x-mail::message>

# {{ __('mail.user_account_created_header') }}

{{ __('mail.user_account_created_body', ['name' => $user->name]) }}

{{ config('app.name') }}
</x-mail::message>
