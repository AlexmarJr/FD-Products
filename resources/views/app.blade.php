<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
        <title inertia>FD - Produtos</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
        <script>
            (function(){
                const FIXED_TITLE = 'FD - Produtos';
                // Ensure title on initial load
                document.title = FIXED_TITLE;
                // Listen to Inertia navigation events
                document.addEventListener('inertia:start', function(){ document.title = FIXED_TITLE; });
                document.addEventListener('inertia:finish', function(){ document.title = FIXED_TITLE; });
                // Observe direct title mutations and reset them
                const titleEl = document.querySelector('title') || (function(){ const t = document.createElement('title'); document.head.appendChild(t); return t; })();
                const mo = new MutationObserver(function(){ if(document.title !== FIXED_TITLE) document.title = FIXED_TITLE; });
                mo.observe(titleEl, { childList: true, characterData: true, subtree: true });
            })();
        </script>
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
