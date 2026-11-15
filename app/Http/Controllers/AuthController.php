<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $default = match (Auth::user()->role) {
                'admin'       => '/admin/dashboard',
                'pemilik_kos' => '/pemilik/dashboard',
                default       => '/',
            };

            return redirect()->intended($default);
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'role'         => ['required', Rule::in(['pencari_kos', 'pemilik_kos'])],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'username'     => ['required', 'string', 'max:50', 'unique:users,username'],
            'email'        => ['required', 'email', 'max:100', 'unique:users,email'],
            'no_telp'      => ['nullable', 'string', 'max:20'],
            'password'     => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'nama_lengkap' => $data['nama_lengkap'],
            'username'     => $data['username'],
            'email'        => $data['email'],
            'no_telp'      => $data['no_telp'] ?? null,
            'password'     => Hash::make($data['password']),
            'role'         => $data['role'],
        ]);

        Auth::login($user);

        return $this->redirectByRole($user);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function redirectByRole(User $user)
    {
        return match ($user->role) {
            'admin'       => redirect('/admin/dashboard'),
            'pemilik_kos' => redirect('/pemilik/dashboard'),
            default       => redirect('/pencari/dashboard'),
        };
    }
}