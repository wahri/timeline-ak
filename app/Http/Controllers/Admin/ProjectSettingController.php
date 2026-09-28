<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProjectSetting;
use Illuminate\Http\Request;

class ProjectSettingController extends Controller
{
    /**
     * Tampilkan form pengaturan proyek & password akses klien.
     */
    public function index()
    {
        $setting = ProjectSetting::current();

        return view('admin.settings.index', compact('setting'));
    }

    /**
     * Simpan pembaruan pengaturan proyek & password akses klien.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'client_password' => ['required', 'string', 'min:4', 'max:100'],
            'project_description' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'target_completion_date' => ['nullable', 'date'],
            'client_contact_person' => ['nullable', 'string', 'max:150'],
            'version_tag' => ['nullable', 'string', 'max:50'],
        ], [
            'project_name.required' => 'Nama proyek wajib diisi.',
            'client_password.required' => 'Password akses dashboard klien wajib diisi.',
            'client_password.min' => 'Password akses minimal 4 karakter.',
        ]);

        $setting = ProjectSetting::current();
        $setting->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan proyek dan Password Akses Klien berhasil diperbarui!');
    }
}
