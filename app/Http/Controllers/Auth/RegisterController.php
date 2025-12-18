<?php
// ========================================
// FILE: app/Http/Controllers/Auth/RegisterController.php
// FUNGSI: Mengatur proses registrasi user baru
// ========================================

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;                           // Model User untuk berinteraksi dengan tabel users
use Illuminate\Foundation\Auth\RegistersUsers; // Trait Laravel untuk logic registrasi
use Illuminate\Support\Facades\Hash;           // Facade Hash untuk enkripsi password
use Illuminate\Support\Facades\Validator;
// Facade Validator untuk validasi input

class RegisterController extends Controller
{
    // ================================================
    // TRAIT: RegistersUsers
    // ================================================
    // Trait ini yang melakukan pekerjaan berat:
    // - Menangani routes GET /register (tampil form)
    // - Menangani routes POST /register (proses submit)
    // - Login otomatis setelah register sukses
    // ================================================
    use RegistersUsers;

    /**
     * Redirect setelah registrasi berhasil.
     */
    protected $redirectTo = '/home';

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Hanya guest (belum login) yang bisa akses form register.
        // User yang sudah login akan di-redirect ke home.
        $this->middleware('guest');
    }

    /**
     * Validasi data registrasi.
     *
     * Method ini menentukan aturan validasi untuk input form.
     *
     * @param array $data Data dari request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            // RULES VALIDASI

            'name'     => ['required', 'string', 'max:255'],
            // ↑ Nama wajib, string, maksimal 255 char

            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // ↑ unique:users = Cek tabel 'users', kolom 'email'.
            //   Jika email sudah ada, validasi gagal. PENTING!

            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // ↑ confirmed = Laravel akan mencari field bernama 'password_confirmation'
            //   dan memastikan nilainya SAMA PERSIS dengan field 'password'.
            //   Biasanya field ini ada di form register: <input name="password_confirmation">

        ], [
            // CUSTOM MESSAGES
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah terdaftar. Gunakan email lain.',
            'password.min'       => 'Password minimal 8 karakter agar aman.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);
    }

    /**
     * Buat user baru setelah validasi berhasil.
     *
     * Method ini dieksekusi oleh Trait RegistersUsers setelah validasi lolos.
     *
     * @param array $data Data valid
     * @return \App\Models\User Object user baru
     */
    protected function create(array $data): User
    {
        // ================================================
        // CREATE USER + HASH PASSWORD
        // ================================================
        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],

            // SECURITY CRITICAL: Password MENDATORY di-hash!
            // Jangan pernah menyimpan password plaintext.
            // Hash::make() menggunakan algoritma Bcrypt (default aman).
            'password' => Hash::make($data['password']),

            // Set role default. Pastikan 'customer', jangan 'admin'.
            'role'     => 'customer',
        ]);
    }
}
