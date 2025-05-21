{{--
name: 'layout-simple',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    @include('web.partials.head')
</head>
<body class="min-h-screen bg-white">
    {{ $slot }}
    @fluxScripts
</body>
</html>
