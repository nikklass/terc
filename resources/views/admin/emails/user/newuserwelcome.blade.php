@component('mail::message')
# Welcone {{ $user->first_name }},

Your account has just been added on pendo admin. <br>
You can login to your account by clicking the link below: <br>
<br>

@component('mail::panel')
Your email address: {{ $user->email }} <br>
Your password: {{ $user->password }} <br>
@endcomponent

@component('mail::button', ['url' => 'http://41.215.126.10/public'])
View Website
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
