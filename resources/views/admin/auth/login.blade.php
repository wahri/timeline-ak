@extends('layouts.app')

@section('title', 'Login Administrator - Apotek Keluarga')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950 relative overflow-hidden"
     x-data="{ 
        showPass: false, 
        loading: false,
        fillDemo() {
            document.getElementById('login').value = 'admin';
            document.getElementById('password').value = 'admin123';
        }
     }">
    
    <!-- Background Glows -->
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full relative z-10">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-brand-600 to-teal-500 text-white shadow-2xl shadow-brand-500/30 mb-4 border border-brand-400/30">
                <i class="fa-solid fa-user-shield text-2xl"></i>
            </div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">PORTAL ADMIN</h1>
            <p class="text-xs uppercase font-bold tracking-widest text-brand-400 mt-1">Apotek Keluarga Task Manager</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl p-8 shadow-2xl shadow-black/40 border border-white/20">
            
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Masuk ke Akun Anda</h2>
                <p class="text-xs text-slate-500 mt-1">
                    Gunakan username atau email terdaftar untuk mengelola timeline dan task proyek.
                </p>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.login.submit') }}" method="POST" @submit="loading = true" class="space-y-4">
                @csrf

                <!-- Username or Email -->
                <div>
                    <label for="login" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5">
                        Username atau Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user text-sm"></i>
                        </div>
                        <input type="text" 
                               name="login" 
                               id="login" 
                               value="{{ old('login') }}" 
                               required 
                               autofocus
                               placeholder="admin atau email..." 
                               class="w-full pl-10 pr-4 py-3 text-sm bg-slate-50 border @error('login') border-rose-400 ring-2 ring-rose-200 @else border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-200 @enderror rounded-xl transition duration-200 outline-none text-slate-800 font-medium placeholder-slate-400">
                    </div>
                    @error('login')
                        <p class="mt-1.5 text-xs text-rose-600 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-600">
                            Password
                        </label>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-key text-sm"></i>
                        </div>
                        <input :type="showPass ? 'text' : 'password'" 
                               name="password" 
                               id="password" 
                               required 
                               placeholder="••••••••" 
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

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded text-brand-600 focus:ring-brand-500 border-slate-300">
                        <span class="text-xs text-slate-600 font-medium select-none">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        :disabled="loading"
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-slate-900 to-slate-800 hover:from-slate-800 hover:to-slate-700 text-white font-semibold rounded-xl shadow-lg shadow-slate-900/30 transition duration-200 flex items-center justify-center gap-2 disabled:opacity-70 cursor-pointer mt-2">
                    <span x-show="!loading"><i class="fa-solid fa-right-to-bracket mr-1"></i> Masuk ke Panel Admin</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>

            </form>

            <!-- Demo Account Helper Chip -->
            <div class="mt-6 pt-5 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Akun Demo Default:</span>
                <button type="button" 
                        @click="fillDemo()" 
                        class="px-2.5 py-1 rounded-lg bg-brand-50 hover:bg-brand-100 text-brand-700 font-semibold border border-brand-200 transition cursor-pointer">
                    <i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Isi Otomatis (admin / admin123)
                </button>
            </div>

        </div>

        <!-- Back to Public Dashboard -->
        <div class="mt-6 text-center">
            <a href="{{ route('client.gate') }}" class="text-xs font-semibold text-slate-400 hover:text-brand-300 transition inline-flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i>
                Kembali ke Gerbang Dashboard Klien
            </a>
        </div>

    </div>
</div>
@endsection
