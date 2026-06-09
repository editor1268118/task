@component('mail::message')
# {{ $title }}

Hello {{ $notifiable->name }},

{{ $messageText }}

@component('mail::panel')
Module: {{ ucfirst($module) }}  
Action: {{ ucfirst(str_replace('_', ' ', $action)) }}  
Priority: {{ ucfirst($priority) }}
@endcomponent

@if($url)
@component('mail::button', ['url' => $url])
Open in Amigos TMS
@endcomponent
@endif

Regards,  
Amigos TMS
@endcomponent
