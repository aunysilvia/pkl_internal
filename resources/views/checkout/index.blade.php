{{-- resources/views/checkout/index.blade.php --}}

@extends('layouts.admin') {{-- Pastikan path ini sesuai dengan layout admin Anda --}}

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 font-weight-bold">Checkout</h1>
        </div>
    </div>

    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf
        <div class="row">

            {{-- Form Alamat (Kiri) --}}
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Informasi Pengiriman</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label text-secondary small font-weight-bold">Nama Penerima</label>
                            <input type="text" name="name" id="name" value="{{ auth()->user()->name }}" class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label text-secondary small font-weight-bold">Nomor Telepon</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Contoh: 08123456789" required>
                            @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label text-secondary small font-weight-bold">Alamat Lengkap</label>
                            <textarea name="address" id="address" rows="4" class="form-control @error('address') is-invalid @enderror" placeholder="Masukkan alamat lengkap pengiriman" required></textarea>
                            @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Summary (Kanan) --}}
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px; z-index: 10;">
                    <div class="card-header bg-white">
                        <h5 class="card-title mb-0">Ringkasan Pesanan</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                            @foreach($cart->items as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-start border-0">
                                <div class="ms-2 me-auto">
                                    <div class="small fw-bold">{{ $item->product->name }}</div>
                                    <small class="text-muted">Jumlah: {{ $item->quantity }}</small>
                                </div>
                                <span class="small font-weight-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-footer bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="h6 mb-0">Total Pembayaran</span>
                            <span class="h5 mb-0 text-primary font-weight-bold">
                                Rp {{ number_format($cart->items->sum('subtotal'), 0, ',', '.') }}
                            </span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                            <i class="fas fa-shopping-bag me-2"></i> Buat Pesanan
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
