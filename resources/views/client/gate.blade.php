@extends('layouts.app')

@section('title', 'Akses Monitoring Proyek - Apotek Keluarga')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 relative overflow-hidden">
    
    <!-- Background Decorative Glows -->
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="max-w-md w-full relative z-10" x-data="{ showPass: false, loading: false }">
        
        <!-- Brand Header Card -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-500 text-white shadow-2xl shadow-brand-500/30 mb-4 border border-brand-400/30">
                <i class="fa-solid fa-notes-medical text-3xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">APOTEK KELUARGA</h1>
            <p class="text-xs uppercase font-bold tracking-widest text-brand-400 mt-1">Project Monitoring & Timeline</p>
        </div>

        <!-- Main Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl shadow-black/40 border border-white/20">
            
            <div class="mb-6">
                <div class="flex items-center gap-2.5 text-slate-800 font-bold text-lg mb-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-500 animate-pulse"></span>
                    <span>Gerbang Akses Dashboard</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Halaman ini memuat timeline progress, task list dinamis, serta analitik proyek <span class="font-semibold text-slate-700">{{ $setting->project_name }}</span>.
                </p>
            </div>

            <!-- Form Verifikasi Password -->
            <form action="{{ route('client.gate.verify') }}" method="POST" @submit="loading = true" class="space-y-5">
                @csrf

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-2">
                        Password Akses Dashboard
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" 
                               name="password" 
                               id="password" 
                               required 
                               autofocus
                               placeholder="Masukkan password akses..." 
                               class="w-full pl-10 pr-11 py-3 text-sm bg-slate-50 border @error('password') border-rose-400 ring-2 ring-rose-200 @else border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-200 @enderror rounded-xl transition duration-200 outline-none text-slate-800 font-medium placeholder-slate-400">
                        <button type="button" 
                                @click="showPass = !showPass" 
                                tabindex="-1"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                            <i :class="showPass ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        :disabled="loading"
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-brand-600 to-teal-600 hover:from-brand-700 hover:to-teal-700 active:scale-[0.99] text-white font-semibold rounded-xl shadow-lg shadow-brand-500/25 transition duration-200 flex items-center justify-center gap-2 disabled:opacity-70 cursor-pointer">
                    <span x-show="!loading"><i class="fa-solid fa-arrow-right-to-bracket mr-1"></i> Buka Dashboard Monitoring</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memverifikasi...
                    </span>
                </button>
            </form>

            <!-- Info Box & Default hint for demo testing -->
            <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5 text-slate-400">
                    <i class="fa-solid fa-shield-halved text-brand-600"></i> Terenkripsi & Dilindungi
                </span>
            </div>
        </div>

        <!-- Footer Link to Admin -->
        <div class="mt-6 text-center">
            <a href="{{ route('admin.login') }}" class="text-xs font-semibold text-slate-400 hover:text-brand-300 transition inline-flex items-center gap-1.5">
                <i class="fa-solid fa-user-shield"></i>
                Masuk sebagai Administrator Proyek
            </a>
        </div>
        
    </div>
</div>
@endsection
