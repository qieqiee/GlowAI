<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GlowAI')</title>
    @yield('page-assets')
    @vite('resources/css/mua-navbar.css')
    @stack('styles')
</head>
<body class="@yield('body-class')">
    @include('mua.partials.navbar')
    @yield('content')

@stack('scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggle = document.getElementById('muaNavToggle');
    const menu = document.getElementById('muaNavMenu');

    if (!toggle || !menu) {
        return;
    }

    toggle.addEventListener('click', function () {

        const isOpen = menu.classList.toggle('open');

        toggle.classList.toggle('open', isOpen);

        toggle.setAttribute(
            'aria-expanded',
            isOpen ? 'true' : 'false'
        );

    });

});
</script>

</body>
</html>
