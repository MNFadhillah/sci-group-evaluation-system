<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class loginController extends Controller
{
    public function showLoginForm()
    {
        return view('home.login'); // arahkan ke file Blade login kamu
    }

    /**
     * Memproses login pengguna.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);


        $user = User::where('email', $request->email)->first();

  
        if (!$user) {
            return back()->with('login_error', 'email_not_found')->onlyInput('email');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('login_error', 'password_wrong')->onlyInput('email');
        }

        Auth::login($user, $request->filled('remember'));
        $request->session()->regenerate();

        // Redirect berdasarkan role
        if ($user->role === 'teacher') {
            return redirect()->route('dashboardGuru')->with('success', 'Selamat datang, Guru!');
        }

        if ($user->role === 'student') {
            return redirect()->route('dashboard.siswa')->with('success', 'Selamat datang, Siswa!');
        }

        return redirect('/')->with('success', 'Selamat datang di Evolevel!');
    }

    /**
     * Logout pengguna.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Anda telah keluar.');
    }
}
