{{-- ================================================
     FILE: resources/views/layouts/app.blade.php
     FUNGSI: Master layout untuk halaman customer/publik
     ================================================ --}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- CSRF Token untuk AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- SEO Meta Tags --}}
    <title>@yield('title', 'Toko Online') - {{ config('app.name') }}</title>
    <meta name="description" content="@yield('meta_description', 'Toko online terpercaya dengan produk berkualitas')">

    {{-- Favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Vite CSS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Stack untuk CSS tambahan per halaman --}}
    @stack('styles')
</head>
<body>
    {{-- ============================================
         NAVBAR
         ============================================ --}}
    @include('profile.partials.navbar')

    {{-- ============================================
         FLASH MESSAGES
         ============================================ --}}
    <div class="container mt-3">
        @include('profile.partials.flash-messages')
    </div>

    {{-- ============================================
         MAIN CONTENT
         ============================================ --}}
    <main class="min-vh-100">
        @yield('content')
    </main>

    {{-- ============================================
         FOOTER
         ============================================ --}}
    @include('profile.partials.footer')

    {{-- Stack untuk JS tambahan per halaman --}}
    @stack('scripts')
</body>
</html>
