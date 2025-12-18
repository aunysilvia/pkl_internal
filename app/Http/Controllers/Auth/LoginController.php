<?php
// ========================================
// FILE: app/Http/Controllers/Auth/LoginController.php
// FUNGSI: Mengatur proses login user
// ========================================

namespace App\Http\Controllers\Auth;

// ↑ Namespace adalah "alamat" file ini dalam struktur folder
// App\Http\Controllers\Auth berarti file ada di app/Http/Controllers/Auth/

use App\Http\Controllers\Controller;
// ↑ Import class Controller sebagai parent class

use Illuminate\Foundation\Auth\AuthenticatesUsers;

// ↑ Import trait yang berisi logic login (attempt, logout, dll)
// Trait adalah kumpulan method yang bisa di-reuse oleh banyak class

class LoginController extends Controller
{
    // ================================================
    // TRAIT: AuthenticatesUsers
    // ================================================
    // Trait ini menyediakan method inti:
    // - showLoginForm()  → Menampilkan view auth.login
    // - login()          → Menangani POST request login
    // - logout()         → Menangani POST request logout
    // - sendFailedLoginResponse() → Return error jika salah password
    // ================================================
    use AuthenticatesUsers;

    /**
     * Redirect setelah login berhasil.
     *
     * Property ini menentukan kemana user diarahkan
     * setelah berhasil login jika tidak ada logic khusus.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    // ↑ Default: arahkan ke /home

    /**
     * Constructor: Method yang dipanggil saat class dibuat.
     *
     * Di sini kita mengatur middleware (filter) untuk controller ini.
     */
    public function __construct()
    {
        // ================================================
        // MIDDLEWARE: guest
        // ================================================
        // Artinya: Hanya user yang BELUM LOGIN (guest)
        // yang bisa mengakses halaman login.
        //
        // Logika: Kalau user sudah login, mereka akan di-redirect
        // ke halaman home jika mencoba buka /login lagi.
        //
        // except('logout'): Method logout DIKECUALIKAN.
        // Logout boleh (dan harus) diakses oleh user yang sudah login.
        // ================================================
        $this->middleware('guest')->except('logout');

        // ================================================
        // MIDDLEWARE: auth (Tambahan untuk Logout)
        // ================================================
        // Kita bisa memastikan hanya user yang login yang bisa logout.
        // ================================================
        $this->middleware('auth')->only('logout');
    }

    /**
     * Override method redirectTo untuk custom redirect.
     *
     * Method ini akan dipanggil otomatis oleh Laravel jika ada,
     * menggantikan property $redirectTo di atas.
     *
     * Gunanya untuk logika redirect dinamis (misal beda role).
     *
     * @return string URL tujuan redirect
     */
    protected function redirectTo(): string
    {
        // ================================================
        // LOGIKA REDIRECT DINAMIS
        // ================================================

        // Ambil user yang sedang login saat ini
        $user = auth()->user();

        // Jika role-nya admin, arahkan ke dashboard admin
        if ($user->role === 'admin') {
            return route('admin.dashboard');
            // ↑ Menggunakan route helper lebih aman daripada hardcode URL '/admin/dashboard'
        }

        // Jika customer biasa, arahkan ke home landing page
        return route('home');
    }

    /**
     * Override untuk custom validation rules.
     *
     * Method ini mengatur aturan validasi input dari form login.
     * Jika validasi gagal, user dikembalikan ke form dengan pesan error.
     *
     * @param \Illuminate\Http\Request $request
     */
    protected function validateLogin($request): void
    {
        // ================================================
        // VALIDASI INPUT LOGIN
        // ================================================

        $request->validate([
            // $this->username() defaultnya return 'email'
            // Kita bisa ubah method username() jika ingin login pakai username/no hp
            $this->username() => 'required|string|email',
            // ↑ required = wajib diisi
            // ↑ email    = format harus valid (ada @ dan .)

            'password'        => 'required|string|min:6',
            // ↑ min:6 = minimal 6 karakter (opsional, untuk security dasar)
        ], [
            // ================================================
            // CUSTOM ERROR MESSAGES (Bahasa Indonesia)
            // ================================================
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid (harus ada @).',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);
    }
}
