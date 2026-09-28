<?php

namespace App\Http\Controllers;

use App\Models\ProjectSetting;
use Illuminate\Http\Request;

class ClientAccessController extends Controller
{
    /**
     * Tampilkan halaman gerbang password akses klien.
     */
    public function showGate()
    {
        if (session('client_authenticated', false)) {
            return redirect()->route('client.dashboard');
        }

        $setting = ProjectSetting::current();

        return view('client.gate', compact('setting'));
    }

    /**
     * Verifikasi password yang dimasukkan dengan password di database.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Password akses wajib diisi.',
        ]);

        $setting = ProjectSetting::current();

        if ($request->input('password') === $setting->client_password) {
            session([
                'client_authenticated' => true,
                'client_auth_at' => now(),
            ]);

            return redirect()->route('client.dashboard')
                ->with('success', 'Akses berhasil diverifikasi. Selamat datang di Dashboard Monitoring Apotek Keluarga!');
        }

        return back()
            ->withInput()
            ->withErrors(['password' => 'Password akses salah. Silakan hubungi Tim Proyek jika membutuhkan bantuan.']);
    }

    /**
     * Kunci kembali dashboard (logout klien).
     */
    public function lock(Request $request)
    {
        session()->forget(['client_authenticated', 'client_auth_at']);

        return redirect()->route('client.gate')
            ->with('info', 'Sesi dashboard telah berhasil dikunci.');
    }
}
