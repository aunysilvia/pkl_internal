{{-- ================================================
     FILE: resources/views/contoh.blade.php
     FUNGSI: Demonstrasi sintaks Blade
     ================================================ --}}

{{-- MENAMPILKAN DATA --}}
{{-- {{ }} akan auto-escape untuk mencegah XSS --}}
<h1>{{ $title }}</h1>

{{-- Untuk output HTML mentah (HATI-HATI XSS!) --}}
{!! $htmlContent !!}


{{-- KONDISIONAL --}}
@if($user->isAdmin())
<p>Selamat datang, Admin!</p>
@elseif($user->isCustomer())
<p>Selamat datang, {{ $user->name }}!</p>
@else
<p>Silakan login terlebih dahulu.</p>
@endif

{{-- KONDISI AUTHENTICATION --}}
@auth
{{-- User sudah login --}}
<p>Halo, {{ auth()->user()->name }}</p>
@endauth

@guest
{{-- User belum login --}}
<a href="{{ route('login') }}">Login</a>
@endguest


{{-- LOOPING --}}
@foreach($products as $product)
<div class="product-card">
    <h3>{{ $product->name }}</h3>
    <p>{{ $product->formatted_price }}</p>
</div>
@endforeach

{{-- Loop dengan pengecekan kosong --}}
@forelse($products as $product)
<div>{{ $product->name }}</div>
@empty
<p>Tidak ada produk.</p>
@endforelse


{{-- INCLUDE PARTIAL --}}
@include('partials.header')
@include('partials.product-card', ['product' => $product])


{{-- CSRF TOKEN (Wajib untuk form POST) --}}
<form method="POST" action="{{ route('products.store') }}">
    @csrf
    {{-- Form fields --}}
</form>


{{-- METHOD SPOOFING (untuk PUT/PATCH/DELETE) --}}
<form method="POST" action="{{ route('products.update', $product) }}">
    @csrf
    @method('PUT')
    {{-- Form fields --}}
</form>
