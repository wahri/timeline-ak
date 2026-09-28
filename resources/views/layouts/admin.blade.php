<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Apotek Keluarga</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Tailwind CSS (CDN with Custom Config) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
    </style>
    @stack('styles')
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-100" x-data="{ sidebarOpen: false }">

    <!-- Flash Notifications -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2 max-w-md w-full px-4 pointer-events-none">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="pointer-events-auto flex items-center p-4 bg-emerald-600 text-white rounded-xl shadow-xl shadow-emerald-500/20">
                <i class="fa-solid fa-circle-check text-xl mr-3"></i>
                <div class="text-sm font-medium flex-1">{{ session('success') }}</div>
                <button @click="show = false" class="ml-2 text-white/80"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div x-data="{ show: true }" x-show="show" 
                 class="pointer-events-auto flex items-start p-4 bg-rose-600 text-white rounded-xl shadow-xl shadow-rose-500/20">
                <i class="fa-solid fa-circle-xmark text-xl mr-3 mt-0.5"></i>
                <div class="text-sm font-medium flex-1">
                    {{ session('error') ?? $errors->first() }}
                </div>
                <button @click="show = false" class="ml-2 text-white/80"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif
    </div>

    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR (Desktop & Mobile) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col shadow-xl">
            
            <!-- Sidebar Header -->
            <div class="h-20 flex items-center px-6 border-b border-slate-800 gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-teal-500 flex items-center justify-center text-white text-lg font-bold shadow-md shadow-brand-500/20">
                    <i class="fa-solid fa-notes-medical"></i>
                </div>
                <div>
                    <h1 class="font-extrabold text-sm tracking-tight text-white">APOTEK KELUARGA</h1>
                    <p class="text-[10px] uppercase font-bold tracking-widest text-brand-400">Admin Control Panel</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
                
                <a href="{{ route('admin.tasks.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.tasks.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <i class="fa-solid fa-list-check w-5 text-center text-sm"></i>
                    <span>Manajemen Task</span>
                </a>

                <a href="{{ route('admin.settings.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.settings.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <i class="fa-solid fa-sliders w-5 text-center text-sm"></i>
                    <span>Pengaturan Proyek</span>
                </a>

                <div class="pt-4 mt-4 border-t border-slate-800">
                    <span class="px-3.5 text-[10px] font-bold uppercase tracking-wider text-slate-500">Pratinjau Publik</span>
                    <a href="{{ route('client.dashboard') }}" target="_blank"
                       class="flex items-center gap-3 px-3.5 py-2.5 mt-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-emerald-400 hover:bg-slate-800 transition">
                        <i class="fa-solid fa-arrow-up-right-from-square w-5 text-center text-xs"></i>
                        <span>Dashboard Klien</span>
                    </a>
                </div>

            </nav>

            <!-- Sidebar User & Logout Footer -->
            <div class="p-4 border-t border-slate-800 bg-slate-950/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <div class="w-8 h-8 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center font-bold text-xs">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="truncate">
                            <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name ?? 'Administrator' }}</p>
                            <p class="text-[10px] text-slate-400 truncate">{{ Auth::user()->username ?? 'admin' }}</p>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition">
                            <i class="fa-solid fa-power-off"></i>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- Sidebar Backdrop for mobile -->
        <div x-show="sidebarOpen" 
             x-cloak 
             @click="sidebarOpen = false" 
             class="fixed inset-0 z-30 bg-black/50 lg:hidden"></div>

        <!-- MAIN CONTENT WRAPPER -->
        <div class="flex-1 flex flex-col overflow-hidden">
            
            <!-- Top Navbar -->
            <header class="h-16 sm:h-20 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 text-slate-600 hover:bg-slate-100 rounded-xl">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <h2 class="text-base sm:text-lg font-extrabold text-slate-900">
                        @yield('header_title', 'Admin Dashboard')
                    </h2>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('client.dashboard') }}" target="_blank" 
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-50 text-brand-700 hover:bg-brand-100 rounded-xl text-xs font-semibold transition border border-brand-200">
                        <i class="fa-solid fa-globe"></i>
                        <span class="hidden sm:inline">Buka Tampilan Klien</span>
                    </a>
                </div>
            </header>

            <!-- Scrollable Page Content -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-8">
                @yield('content')
            </main>

        </div>

    </div>

    @stack('scripts')
</body>
</html>
