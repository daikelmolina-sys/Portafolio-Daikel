<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO -->
    <title>🍙 Onigiri POS — Comida Rápida Asiática</title>
    <meta name="description" content="Onigiri Express: los mejores onigiris japoneses de la ciudad. Pide en línea o en local.">
    <meta name="theme-color" content="#0a0a0a">

    <!-- Open Graph -->
    <meta property="og:title" content="Onigiri POS">
    <meta property="og:description" content="Sistema de pedidos para comida rápida asiática">
    <meta property="og:type" content="website">

    <!-- Favicon emoji -->
    <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>🍙</text></svg>">

    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body>
    <div id="root"></div>
</body>
</html>
