<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function formLogin(Request $request)
    {
        return view('auth.masuk');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => 'required|string',      // email atau no WA
            'password' => 'required|string',
        ], [
            'login.required' => 'Email atau nomor WA wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $kolom = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'no_wa';

        if (! Auth::attempt([$kolom => $data['login'], 'password' => $data['password']], $request->boolean('ingat'))) {
            return back()
                ->withErrors(['login' => 'Email/nomor WA atau kata sandi salah.'])
                ->onlyInput('login');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->berandaPeran(Auth::user()));
    }

    public function formRegister(Request $request)
    {
        return view('auth.daftar', ['peran' => $request->query('peran', 'pengusaha')]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:users,email|required_without:no_wa',
            'no_wa' => 'nullable|string|max:20|unique:users,no_wa|required_without:email',
            'peran' => 'required|in:petani,pengusaha',
            'password' => ['required', 'confirmed', Password::min(8)],
            'setuju' => 'accepted',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required_without' => 'Isi email atau nomor WA.',
            'no_wa.required_without' => 'Isi email atau nomor WA.',
            'email.unique' => 'Email sudah terdaftar.',
            'no_wa.unique' => 'Nomor WA sudah terdaftar.',
            'peran.required' => 'Pilih peran kamu.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'setuju.accepted' => 'Kamu harus menyetujui syarat dan ketentuan.',
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'no_wa' => $data['no_wa'] ?? null,
                'peran' => $data['peran'],
                'password' => $data['password'], // otomatis di-hash oleh cast 'hashed'
            ]);

            // Profil kosong dulu, dilengkapi nanti di halaman akun
            $data['peran'] === 'petani'
                ? $user->profilPetani()->create()
                : $user->profilPengusaha()->create();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($this->berandaPeran($user))
            ->with('pesan', 'Akun berhasil dibuat. Lengkapi profil dan ajukan verifikasi, ya.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk');
    }

    private function berandaPeran(User $user): string
    {
        return match ($user->peran) {
            'petani' => route('petani.dashboard'),
            'admin' => route('admin.verifikasi'),
            default => route('pengusaha.beranda'),
        };
    }

    public function masukDemo(Request $request)
    {
        $data = $request->validate(['peran' => 'required|in:petani,pengusaha']);

        $email = $data['peran'] === 'petani' ? 'made@taniloka.test' : 'resto@taniloka.test';
        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withErrors(['login' => 'Akun demo belum ada. Jalankan php artisan migrate:fresh --seed.']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($this->berandaPeran($user))->with('sukses', "Selamat datang, {$user->name}!");
    }
}