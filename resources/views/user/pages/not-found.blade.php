{{-- pages.not-found — port of NotFound.tsx --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>RenovaSim — 404</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-muted">
    <div class="text-center">
        <h1 class="mb-4 text-4xl font-bold">404</h1>
        <p class="mb-4 text-xl text-muted-foreground">Oops! Page not found</p>
        <a href="/" class="text-primary underline hover:text-primary/90">Return to Home</a>
    </div>
    <script>console.error("404 Error: User attempted to access non-existent route:", window.location.pathname);</script>
</body>
</html>
