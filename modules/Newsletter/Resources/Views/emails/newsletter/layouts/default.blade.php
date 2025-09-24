{{--
name: 'email_default_layout',
base: 1,
active: 1,
override_name: '',
priority: 0
--}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; }
        .content { max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body>
<div class="content">
    @yield('content')
</div>
</body>
</html>
