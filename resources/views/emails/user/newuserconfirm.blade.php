@component('mail::message')
# Welcome {{ $user->first_name }},

Your account has just been added on TERC. <br>
You need to confirm your account before using it.<br><br>
Use the details below to confirm your account:
<br><br>

@component('mail::panel')
Your email address: {{ $user->email }} <br>
Confirmation code: {{ $user->getActiveConfirmCode('', '', $user->email) }} <br>
@endcomponent

@component('mail::button', ['url' => 'http://41.215.126.10/terc'])
View Website
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
