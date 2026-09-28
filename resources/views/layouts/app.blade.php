<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Monitoring Proyek') - Apotek Keluarga</title>
    
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
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669', // Pharmacy Emerald
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        },
                        tealmed: {
                            50: '#f0fdfa',
                            500: '#14b8a6',
                            600: '#0d9488',
                            700: '#0f766e',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        /* Modern Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11pt; }
            .print-full { width: 100% !important; margin: 0 !important; box-shadow: none !important; }
        }
    </style>
    @stack('styles')
</head>
<body class="h-full flex flex-col font-sans antialiased text-slate-800 bg-slate-50 selection:bg-brand-500 selection:text-white">

    <!-- Notification Toast Messages -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2 max-w-md w-full px-4 no-print pointer-events-none">
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="pointer-events-auto flex items-center p-4 bg-emerald-600 text-white rounded-xl shadow-xl shadow-emerald-500/20 transform transition-all duration-300">
                <i class="fa-solid fa-circle-check text-xl mr-3"></i>
                <div class="text-sm font-medium flex-1">{{ session('success') }}</div>
                <button @click="show = false" class="ml-2 text-white/80 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('warning'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" 
                 class="pointer-events-auto flex items-center p-4 bg-amber-500 text-white rounded-xl shadow-xl shadow-amber-500/20 transform transition-all duration-300">
                <i class="fa-solid fa-triangle-exclamation text-xl mr-3"></i>
                <div class="text-sm font-medium flex-1">{{ session('warning') }}</div>
                <button @click="show = false" class="ml-2 text-white/80 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('info'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
                 class="pointer-events-auto flex items-center p-4 bg-blue-600 text-white rounded-xl shadow-xl shadow-blue-500/20 transform transition-all duration-300">
                <i class="fa-solid fa-circle-info text-xl mr-3"></i>
                <div class="text-sm font-medium flex-1">{{ session('info') }}</div>
                <button @click="show = false" class="ml-2 text-white/80 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <div x-data="{ show: true }" x-show="show" 
                 class="pointer-events-auto flex items-start p-4 bg-rose-600 text-white rounded-xl shadow-xl shadow-rose-500/20 transform transition-all duration-300">
                <i class="fa-solid fa-circle-xmark text-xl mr-3 mt-0.5"></i>
                <div class="text-sm font-medium flex-1">
                    {{ session('error') ?? $errors->first() }}
                </div>
                <button @click="show = false" class="ml-2 text-white/80 hover:text-white"><i class="fa-solid fa-xmark"></i></button>
            </div>
        @endif
    </div>

    <!-- Main Content Slot -->
    <div class="flex-1 flex flex-col">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
