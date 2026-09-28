@extends('layouts.admin')

@section('title', 'Pengaturan Proyek & Password Klien - Admin Apotek Keluarga')
@section('header_title', 'Pengaturan Proyek & Akses Klien')

@section('content')
<div class="max-w-4xl space-y-6" x-data="{ showPass: false, copied: false }">

    <!-- SHAREABLE CLIENT LINK CARD -->
    <div class="bg-gradient-to-r from-brand-700 to-teal-800 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-brand-200 text-xs font-semibold mb-2">
                    <i class="fa-solid fa-key"></i> Kredensial Akses Klien
                </span>
                <h3 class="text-xl sm:text-2xl font-black">Bagikan Akses Dashboard ke Stakeholder</h3>
                <p class="text-xs sm:text-sm text-brand-100 mt-1 leading-relaxed">
                    Klien dan pimpinan Apotek Keluarga dapat memantau progres dan outstanding task secara langsung menggunakan password akses berikut tanpa perlu login akun admin.
                </p>
            </div>

            <div class="bg-white/10 backdrop-blur-md p-4 rounded-2xl border border-white/20 flex flex-col gap-2 min-w-[240px]">
                <div class="text-[11px] uppercase tracking-wider text-brand-200 font-bold">Password Klien Saat Ini:</div>
                <div class="flex items-center justify-between gap-2">
                    <span class="font-mono text-lg font-black tracking-widest text-white" x-text="showPass ? '{{ $setting->client_password }}' : '••••••••••••'"></span>
                    <button type="button" @click="showPass = !showPass" class="text-brand-200 hover:text-white p-1">
                        <i :class="showPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'"></i>
                    </button>
                </div>
                <button type="button" 
                        @click="
                            navigator.clipboard.writeText('Link Monitoring: {{ route('client.gate') }}\nPassword: {{ $setting->client_password }}');
                            copied = true;
                            setTimeout(() => copied = false, 3000);
                        "
                        class="w-full mt-2 py-2 px-3 bg-white text-brand-800 hover:bg-brand-50 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                    <i :class="copied ? 'fa-solid fa-check text-emerald-600' : 'fa-regular fa-copy'"></i>
                    <span x-text="copied ? 'Tersalin ke Clipboard!' : 'Salin Info Akses Klien'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- MAIN SETTINGS FORM -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm">
        
        <div class="mb-6 pb-6 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Konfigurasi Parameter Proyek</h3>
            <p class="text-xs text-slate-500 mt-0.5">Atur nama proyek, tanggal target deadline, serta ubah password akses klien yang disimpan di database.</p>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Project Name -->
            <div>
                <label for="project_name" class="block text-xs font-bold text-slate-700 mb-1">
                    Nama Proyek Monitoring *
                </label>
                <input type="text" 
                       name="project_name" 
                       id="project_name" 
                       value="{{ old('project_name', $setting->project_name) }}" 
                       required 
                       class="w-full py-2.5 px-3 text-xs bg-slate-50 border @error('project_name') border-rose-400 @else border-slate-200 @enderror rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                @error('project_name')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Client Password (Stored in Database) -->
            <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80">
                <div class="flex items-center justify-between mb-2">
                    <label for="client_password" class="block text-xs font-bold text-slate-800">
                        <i class="fa-solid fa-shield-halved text-brand-600 mr-1"></i>
                        Password Akses Dashboard Klien (Tersimpan di Database) *
                    </label>
                    <span class="text-[11px] text-slate-400">Dapat diganti sewaktu-waktu</span>
                </div>
                <div class="relative max-w-md">
                    <input type="text" 
                           name="client_password" 
                           id="client_password" 
                           value="{{ old('client_password', $setting->client_password) }}" 
                           required 
                           minlength="4"
                           placeholder="Masukkan password baru klien..." 
                           class="w-full py-2.5 pl-3 pr-10 text-xs font-mono font-bold bg-white border @error('client_password') border-rose-400 @else border-slate-300 @enderror rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none text-slate-800">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-key text-xs"></i>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500 mt-2">
                    Password ini yang akan dimasukkan klien/stakeholder di halaman <a href="{{ route('client.gate') }}" target="_blank" class="text-brand-600 underline font-medium">Gerbang Akses Dashboard</a> untuk melihat monitoring dan list task.
                </p>
                @error('client_password')
                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Project Description -->
            <div>
                <label for="project_description" class="block text-xs font-bold text-slate-700 mb-1">
                    Deskripsi Ringkas Proyek
                </label>
                <textarea name="project_description" 
                          id="project_description" 
                          rows="3" 
                          class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">{{ old('project_description', $setting->project_description) }}</textarea>
            </div>

            <!-- Timeline Dates -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-xs font-bold text-slate-700 mb-1">
                        Tanggal Mulai Proyek
                    </label>
                    <input type="date" 
                           name="start_date" 
                           id="start_date" 
                           value="{{ old('start_date', $setting->start_date ? $setting->start_date->format('Y-m-d') : '') }}" 
                           class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                </div>
                <div>
                    <label for="target_completion_date" class="block text-xs font-bold text-slate-700 mb-1">
                        Target Selesai (Go-Live Deadline)
                    </label>
                    <input type="date" 
                           name="target_completion_date" 
                           id="target_completion_date" 
                           value="{{ old('target_completion_date', $setting->target_completion_date ? $setting->target_completion_date->format('Y-m-d') : '') }}" 
                           class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                </div>
            </div>

            <!-- Additional Meta -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="client_contact_person" class="block text-xs font-bold text-slate-700 mb-1">
                        PIC Klien / Manajemen Apotek Keluarga
                    </label>
                    <input type="text" 
                           name="client_contact_person" 
                           id="client_contact_person" 
                           value="{{ old('client_contact_person', $setting->client_contact_person) }}" 
                           placeholder="Contoh: dr. Hendra / Direksi Apotek..." 
                           class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                </div>
                <div>
                    <label for="version_tag" class="block text-xs font-bold text-slate-700 mb-1">
                        Versi Sistem / Tag Rilis
                    </label>
                    <input type="text" 
                           name="version_tag" 
                           id="version_tag" 
                           value="{{ old('version_tag', $setting->version_tag) }}" 
                           placeholder="v1.0.0-Beta / v2.1-Live" 
                           class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-5 border-t border-slate-100 flex items-center justify-end">
                <button type="submit" class="px-6 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition shadow-lg shadow-brand-600/20 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Simpan Perubahan Pengaturan
                </button>
            </div>

        </form>

    </div>

</div>
@endsection
