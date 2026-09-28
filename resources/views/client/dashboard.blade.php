@extends('layouts.app')

@section('title', 'Dashboard Monitoring Proyek - Apotek Keluarga')

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-800" 
     x-data="{
        search: '',
        activeTab: 'all', // default tampilkan semua atau outstanding
        selectedMilestone: '',
        selectedModule: '',
        selectedPriority: '',
        selectedPic: '',
        viewMode: 'table', // 'table' atau 'kanban'
        activeTask: null,
        showDetailModal: false,
        tasks: {{ Js::from($allTasks) }},
        
        // Helper untuk filter dinamis
        filteredTasks() {
            return this.tasks.filter(task => {
                // Tab filter
                if (this.activeTab === 'outstanding' && task.status === 'completed') return false;
                if (this.activeTab === 'completed' && task.status !== 'completed') return false;
                if (this.activeTab === 'blocked' && task.status !== 'blocked') return false;
                if (this.activeTab === 'overdue' && (!task.due_date || task.status === 'completed' || new Date(task.due_date) >= new Date())) return false;
                
                // Milestone / Fase filter
                if (this.selectedMilestone && task.milestone !== this.selectedMilestone) return false;

                // Module filter
                if (this.selectedModule && task.module !== this.selectedModule) return false;
                
                // Priority filter
                if (this.selectedPriority && task.priority !== this.selectedPriority) return false;
                
                // PIC filter
                if (this.selectedPic && task.pic_name !== this.selectedPic) return false;
                
                // Search query
                if (this.search) {
                    const q = this.search.toLowerCase();
                    const title = (task.title || '').toLowerCase();
                    const desc = (task.description || '').toLowerCase();
                    const pic = (task.pic_name || '').toLowerCase();
                    const module = (task.module || '').toLowerCase();
                    const milestone = (task.milestone || '').toLowerCase();
                    return title.includes(q) || desc.includes(q) || pic.includes(q) || module.includes(q) || milestone.includes(q);
                }
                
                return true;
            });
        },
        
        filterByPhase(phaseName) {
            this.selectedMilestone = (this.selectedMilestone === phaseName) ? '' : phaseName;
            const el = document.getElementById('task-explorer-section');
            if (el) el.scrollIntoView({ behavior: 'smooth' });
        },

        openDetail(task) {
            this.activeTask = task;
            this.showDetailModal = true;
        },
        
        resetFilters() {
            this.search = '';
            this.selectedMilestone = '';
            this.selectedModule = '';
            this.selectedPriority = '';
            this.selectedPic = '';
            this.activeTab = 'all';
        }
     }">

    <!-- ========================================================================= -->
    <!-- TOP NAVIGATION BAR & HEALTH STATUS -->
    <!-- ========================================================================= -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                
                <!-- Logo & Brand Info -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-brand-600 to-teal-500 text-white flex items-center justify-center shadow-md shadow-brand-500/20">
                        <i class="fa-solid fa-notes-medical text-xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-slate-900 tracking-tight text-base sm:text-lg">APOTEK KELUARGA</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-brand-50 text-brand-700 border border-brand-200">
                                {{ $setting->version_tag }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 hidden sm:block truncate max-w-md">
                            {{ $setting->project_name }}
                        </p>
                    </div>
                </div>

                <!-- Right Action Buttons -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Health Status Pill -->
                    <div class="hidden md:flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold
                        @if($projectHealth['color'] === 'emerald') bg-emerald-50 text-emerald-700 border border-emerald-200
                        @elseif($projectHealth['color'] === 'amber') bg-amber-50 text-amber-700 border border-amber-200
                        @else bg-rose-50 text-rose-700 border border-rose-200 @endif">
                        <span class="w-2 h-2 rounded-full @if($projectHealth['color'] === 'emerald') bg-emerald-500 animate-pulse @elseif($projectHealth['color'] === 'amber') bg-amber-500 animate-ping @else bg-rose-500 animate-bounce @endif"></span>
                        <span>{{ $projectHealth['badge'] }}</span>
                    </div>

                    <!-- Export CSV Button -->
                    <a href="{{ route('client.export') }}" 
                       title="Download Laporan CSV/Excel"
                       class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition no-print">
                        <i class="fa-solid fa-file-excel text-emerald-600"></i>
                        <span class="hidden sm:inline">Export Excel</span>
                    </a>

                    <!-- Print Button -->
                    <button onclick="window.print()" 
                            title="Cetak Halaman"
                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition no-print">
                        <i class="fa-solid fa-print"></i>
                        <span class="hidden sm:inline">Cetak</span>
                    </button>

                    <!-- Lock / Keluar Sesi -->
                    <form action="{{ route('client.gate.lock') }}" method="POST" class="no-print">
                        @csrf
                        <button type="submit" 
                                title="Kunci Dashboard"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-semibold transition">
                            <i class="fa-solid fa-lock"></i>
                            <span class="hidden sm:inline">Kunci Sesi</span>
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </header>

    <!-- ========================================================================= -->
    <!-- MAIN CONTAINER -->
    <!-- ========================================================================= -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6 sm:space-y-8">

        <!-- HERO BANNER -->
        <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-800 to-emerald-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-brand-500/10 rounded-full blur-2xl pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-brand-300 text-xs font-semibold mb-3 border border-white/10">
                        <i class="fa-solid fa-chart-line"></i> Dashboard Real-Time Monitoring
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-white leading-tight">
                        {{ $setting->project_name }}
                    </h2>
                    <p class="mt-2 text-xs sm:text-sm text-slate-300 leading-relaxed">
                        {{ $setting->project_description }}
                    </p>
                </div>

                <!-- Deadline & Target Card Inside Hero -->
                <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-white/10 flex items-center gap-4 sm:gap-6 min-w-[280px]">
                    <div class="w-12 h-12 rounded-xl bg-brand-500/20 text-brand-400 flex items-center justify-center text-2xl font-bold">
                        <i class="fa-regular fa-calendar-check"></i>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wider text-slate-300 font-semibold">Target Go-Live / Selesai</div>
                        <div class="text-base sm:text-lg font-bold text-white">
                            {{ $setting->target_completion_date ? $setting->target_completion_date->format('d M Y') : '-' }}
                        </div>
                        <div class="text-xs font-medium mt-0.5 @if($daysRemaining < 0) text-rose-400 @elseif($daysRemaining <= 7) text-amber-300 @else text-brand-300 @endif">
                            @if($daysRemaining < 0)
                                <i class="fa-solid fa-circle-exclamation mr-1"></i> Lewat {{ abs($daysRemaining) }} hari
                            @elseif($daysRemaining == 0)
                                <i class="fa-solid fa-clock mr-1"></i> Hari ini tenggat waktu!
                            @else
                                <i class="fa-solid fa-stopwatch mr-1"></i> Tersisa {{ $daysRemaining }} hari lagi
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- 4 KPI SUMMARY STAT CARDS -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            
            <!-- Card 1: Total & Selesai -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Progress</span>
                    <span class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-circle-check"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $overallProgressPercent }}%</span>
                    <span class="text-xs text-slate-500 font-medium">({{ $completedTasks }}/{{ $totalTasks }} task)</span>
                </div>
                <!-- Progress bar -->
                <div class="w-full bg-slate-100 rounded-full h-2 mt-3 overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-2 rounded-full transition-all duration-500" style="width: {{ $overallProgressPercent }}%"></div>
                </div>
            </div>

            <!-- Card 2: Outstanding Task -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Outstanding Task</span>
                    <span class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-list-check"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900">{{ $outstandingTasks }}</span>
                    <span class="text-xs text-blue-600 font-semibold">Perlu Dituntaskan</span>
                </div>
                <div class="text-[11px] text-slate-500 mt-3 flex items-center justify-between">
                    <span>Berjalan: <b>{{ $inProgressTasks }}</b></span>
                    <span>Review: <b>{{ $reviewTasks }}</b></span>
                    <span>Backlog: <b>{{ $backlogTasks }}</b></span>
                </div>
            </div>

            <!-- Card 3: Terkendala (Blocker) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Task Terkendala</span>
                    <span class="w-9 h-9 rounded-xl @if($blockedTasks > 0) bg-rose-50 text-rose-600 animate-pulse @else bg-slate-100 text-slate-400 @endif flex items-center justify-center text-base">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold @if($blockedTasks > 0) text-rose-600 @else text-slate-900 @endif">
                        {{ $blockedTasks }}
                    </span>
                    <span class="text-xs @if($blockedTasks > 0) text-rose-600 font-semibold @else text-slate-500 @endif">
                        Ada Blocker / Hambatan
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 mt-3 truncate">
                    @if($blockedTasks > 0)
                        <span class="text-rose-600 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i> Membutuhkan keputusan</span>
                    @else
                        <span class="text-emerald-600 font-medium"><i class="fa-solid fa-circle-check mr-1"></i> Tidak ada blocker aktif</span>
                    @endif
                </p>
            </div>

            <!-- Card 4: Overdue Alert -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Overdue (Lewat Target)</span>
                    <span class="w-9 h-9 rounded-xl @if($overdueCount > 0) bg-amber-50 text-amber-600 @else bg-slate-100 text-slate-400 @endif flex items-center justify-center text-base">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold @if($overdueCount > 0) text-amber-600 @else text-slate-900 @endif">
                        {{ $overdueCount }}
                    </span>
                    <span class="text-xs text-slate-500">Task Melewati Deadline</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-3">
                    Durasi Proyek: <b>{{ $elapsedDays }}/{{ $totalProjectDays }} hari</b> ({{ $timeElapsedPercent }}%)
                </p>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- KOMPONEN 1: ROADMAP TIMELINE IMPLEMENTASI ODOO 18 (7 FASE TERPADU) -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl border border-slate-200 p-5 sm:p-6 shadow-sm space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-timeline"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900 tracking-tight">
                            Roadmap Timeline Implementasi Odoo 18 Enterprise
                        </h3>
                        <p class="text-xs text-slate-500">
                            7 Fase Milestone Terpadu — <span class="text-brand-600 font-semibold">Klik kartu fase</span> untuk menyaring daftar task di bawah secara otomatis.
                        </p>
                    </div>
                </div>

                <!-- Active Phase Reset Indicator -->
                <div class="flex items-center gap-2" x-show="selectedMilestone" x-cloak>
                    <span class="text-xs bg-brand-50 text-brand-700 px-3 py-1 rounded-full border border-brand-200 font-semibold flex items-center gap-1.5">
                        <i class="fa-solid fa-filter text-[10px]"></i>
                        <span x-text="'Filter: ' + selectedMilestone"></span>
                    </span>
                    <button type="button" @click="selectedMilestone = ''" class="text-xs text-slate-400 hover:text-rose-600 font-semibold">
                        <i class="fa-solid fa-xmark"></i> Reset
                    </button>
                </div>
            </div>

            <!-- Horizontal Grid of 7 Phases -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3 pt-2">
                @foreach($roadmapPhases as $phase)
                <div @click="filterByPhase('{{ $phase['name'] }}')"
                     :class="selectedMilestone === '{{ $phase['name'] }}' ? 'ring-2 ring-brand-500 bg-brand-50/40 border-brand-400 shadow-md' : 'hover:border-brand-300 hover:shadow-sm'"
                     class="bg-slate-50/80 p-3.5 rounded-2xl border border-slate-200/90 flex flex-col justify-between cursor-pointer transition transform active:scale-[0.98]">
                    
                    <div>
                        <!-- Phase Header -->
                        <div class="flex items-center justify-between gap-1 mb-2">
                            <span class="text-[10px] font-extrabold tracking-wider uppercase px-2 py-0.5 rounded-md
                                @if($phase['color'] === 'emerald') bg-emerald-100 text-emerald-800
                                @elseif($phase['color'] === 'rose') bg-rose-100 text-rose-800 animate-pulse
                                @elseif($phase['color'] === 'blue') bg-blue-100 text-blue-800
                                @else bg-slate-200 text-slate-600 @endif">
                                FASE {{ $phase['id'] }}
                            </span>
                            <i class="{{ $phase['icon'] }} text-xs 
                                @if($phase['color'] === 'emerald') text-emerald-600
                                @elseif($phase['color'] === 'rose') text-rose-600
                                @elseif($phase['color'] === 'blue') text-blue-600
                                @else text-slate-400 @endif"></i>
                        </div>

                        <!-- Phase Name -->
                        <h4 class="text-xs font-bold text-slate-800 leading-snug line-clamp-2">
                            {{ $phase['short'] }}
                        </h4>
                        <span class="text-[10px] text-slate-400 block mt-1">{{ $phase['period'] }}</span>
                    </div>

                    <div class="mt-3 pt-2.5 border-t border-slate-200/70">
                        <!-- Progress Bar -->
                        <div class="flex items-center justify-between text-[10px] font-mono text-slate-600 mb-1">
                            <span>{{ $phase['completed'] }}/{{ $phase['total'] }} Task</span>
                            <b class="font-bold @if($phase['color'] === 'emerald') text-emerald-600 @elseif($phase['color'] === 'rose') text-rose-600 @else text-slate-700 @endif">
                                {{ $phase['percent'] }}%
                            </b>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                            <div class="h-1.5 rounded-full transition-all duration-500
                                @if($phase['color'] === 'emerald') bg-emerald-500
                                @elseif($phase['color'] === 'rose') bg-rose-500
                                @elseif($phase['color'] === 'blue') bg-blue-500
                                @else bg-slate-400 @endif" 
                                style="width: {{ $phase['percent'] }}%"></div>
                        </div>

                        <!-- Status Badge -->
                        <div class="mt-2 text-[10px] font-semibold truncate
                            @if($phase['color'] === 'emerald') text-emerald-700
                            @elseif($phase['color'] === 'rose') text-rose-700 font-bold
                            @elseif($phase['color'] === 'blue') text-blue-700
                            @else text-slate-500 @endif">
                            <i class="fa-solid fa-circle text-[6px] mr-1 inline-block align-middle"></i>
                            {{ $phase['badge'] }}
                        </div>
                    </div>

                </div>
                @endforeach
            </div>
        </div>


        @if($urgentActionItems->count() > 0)
        <div class="bg-amber-50/70 border border-amber-200 rounded-3xl p-5 sm:p-6 shadow-sm no-print">
            <div class="flex items-start justify-between gap-4 mb-4">
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-amber-500 animate-ping"></span>
                    <h3 class="text-base font-bold text-amber-900">
                        Perhatian Khusus Stakeholder & Tim: {{ $urgentActionItems->count() }} Outstanding Action Items
                    </h3>
                </div>
                <span class="text-xs font-semibold text-amber-700 bg-amber-100 px-2.5 py-1 rounded-full border border-amber-300">
                    High Priority Focus
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                @foreach($urgentActionItems as $item)
                <div class="bg-white p-4 rounded-2xl border @if($item->status === 'blocked') border-rose-200 shadow-sm @else border-amber-200 @endif flex flex-col justify-between cursor-pointer hover:border-brand-500 transition"
                     @click="openDetail({{ Js::from($item) }})">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <span class="text-[11px] font-bold text-slate-500 uppercase">{{ $item->module }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full @if($item->status === 'blocked') bg-rose-100 text-rose-700 @else bg-amber-100 text-amber-700 @endif">
                                {{ $item->status === 'blocked' ? 'Terkendala (Blocker)' : 'Overdue' }}
                            </span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-800 line-clamp-1">{{ $item->title }}</h4>
                        @if($item->blocker_notes)
                            <p class="mt-2 text-xs text-rose-600 bg-rose-50 p-2.5 rounded-xl border border-rose-100 leading-relaxed">
                                <i class="fa-solid fa-circle-exclamation mr-1"></i>
                                <b>Kendala:</b> {{ $item->blocker_notes }}
                            </p>
                        @endif
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span><i class="fa-regular fa-user mr-1 text-slate-400"></i> {{ $item->pic_name }}</span>
                        <span class="font-medium text-slate-600">Target: {{ $item->due_date ? $item->due_date->format('d/m/Y') : '-' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- ========================================================================= -->
        <!-- CHARTS & VISUAL ANALYTICS -->
        <!-- ========================================================================= -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Chart 1: Donut Status Breakdown -->
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Distribusi Status Task</h3>
                        <p class="text-xs text-slate-500">Proporsi pengerjaan seluruh modul</p>
                    </div>
                    <i class="fa-solid fa-chart-pie text-slate-400"></i>
                </div>
                <div class="flex-1 flex items-center justify-center min-h-[260px]">
                    <div id="chart-status" class="w-full"></div>
                </div>
            </div>

            <!-- Chart 2: Modul Progress (Stacked Bar) -->
            <div class="bg-white p-5 sm:p-6 rounded-3xl border border-slate-200 shadow-sm flex flex-col lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">Progres Per Modul Farmasi</h3>
                        <p class="text-xs text-slate-500">Perbandingan task selesai vs outstanding di setiap kategori</p>
                    </div>
                    <div class="flex items-center gap-3 text-xs font-medium text-slate-600">
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Selesai</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-blue-500"></span> Outstanding</span>
                    </div>
                </div>
                <div class="flex-1 flex items-center justify-center min-h-[260px]">
                    <div id="chart-module" class="w-full"></div>
                </div>
            </div>

        </div>

        <!-- ========================================================================= -->
        <!-- DYNAMIC TASK EXPLORER (SEARCH, FILTER, VIEW SWITCHER, TABLE/KANBAN) -->
        <!-- ========================================================================= -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden" id="task-explorer-section">
            
            <!-- Control Header -->
            <div class="p-5 sm:p-6 border-b border-slate-200 space-y-4">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-slate-900">Daftar Task & Outstanding Proyek</h3>
                            <template x-if="selectedMilestone">
                                <span class="text-xs bg-brand-50 text-brand-700 font-semibold px-2.5 py-0.5 rounded-full border border-brand-200" x-text="selectedMilestone"></span>
                            </template>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Menampilkan <b x-text="filteredTasks().length"></b> dari total {{ $totalTasks }} task
                        </p>
                    </div>

                    <!-- View Switcher (Table vs Kanban) -->
                    <div class="inline-flex p-1 bg-slate-100 rounded-xl self-start md:self-auto border border-slate-200">
                        <button type="button" 
                                @click="viewMode = 'table'"
                                :class="viewMode === 'table' ? 'bg-white text-brand-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-table-list"></i>
                            <span>Tabel List</span>
                        </button>
                        <button type="button" 
                                @click="viewMode = 'kanban'"
                                :class="viewMode === 'kanban' ? 'bg-white text-brand-700 shadow-sm font-bold' : 'text-slate-600 hover:text-slate-900 font-medium'"
                                class="px-3.5 py-1.5 rounded-lg text-xs transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-columns"></i>
                            <span>Kanban Board</span>
                        </button>
                    </div>
                </div>

                <!-- Tab Status Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                    <button type="button" 
                            @click="activeTab = 'all'" 
                            :class="activeTab === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer">
                        Semua Task ({{ $totalTasks }})
                    </button>
                    <button type="button" 
                            @click="activeTab = 'outstanding'" 
                            :class="activeTab === 'outstanding' ? 'bg-brand-600 text-white shadow-sm shadow-brand-500/30' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer">
                        Outstanding ({{ $outstandingTasks }})
                    </button>
                    <button type="button" 
                            @click="activeTab = 'completed'" 
                            :class="activeTab === 'completed' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer">
                        Selesai ({{ $completedTasks }})
                    </button>
                    <button type="button" 
                            @click="activeTab = 'blocked'" 
                            :class="activeTab === 'blocked' ? 'bg-rose-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer">
                        Terkendala ({{ $blockedTasks }})
                    </button>
                    <button type="button" 
                            @click="activeTab = 'overdue'" 
                            :class="activeTab === 'overdue' ? 'bg-amber-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition cursor-pointer">
                        Overdue ({{ $overdueCount }})
                    </button>
                </div>

                <!-- Search and Filters Bar (6 Columns) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 pt-2">
                    
                    <!-- Search Input (2 cols) -->
                    <div class="lg:col-span-2 relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" 
                               x-model="search"
                               placeholder="Cari task, PIC, deskripsi, modul, fase..." 
                               class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                    </div>

                    <!-- Fase Timeline Filter -->
                    <div>
                        <select x-model="selectedMilestone" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none font-medium">
                            <option value="">Semua Fase Timeline</option>
                            @foreach($milestones as $ms)
                                <option value="{{ $ms }}">{{ $ms }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Modul Filter -->
                    <div>
                        <select x-model="selectedModule" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                            <option value="">Semua Modul</option>
                            @foreach($modules as $mod)
                                <option value="{{ $mod }}">{{ $mod }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Prioritas Filter -->
                    <div>
                        <select x-model="selectedPriority" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                            <option value="">Semua Prioritas</option>
                            <option value="urgent">Mendesak (Urgent)</option>
                            <option value="high">Tinggi (High)</option>
                            <option value="medium">Sedang (Medium)</option>
                            <option value="low">Rendah (Low)</option>
                        </select>
                    </div>

                    <!-- PIC Filter -->
                    <div>
                        <select x-model="selectedPic" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                            <option value="">Semua PIC</option>
                            @foreach($pics as $p)
                                <option value="{{ $p }}">{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- 1. VIEW MODE: TABEL LIST -->
            <!-- ========================================================================= -->
            <div x-show="viewMode === 'table'" class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4">Task / Modul</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Prioritas</th>
                            <th class="py-3 px-4">Progress</th>
                            <th class="py-3 px-4">PIC</th>
                            <th class="py-3 px-4">Timeline / Jadwal</th>
                            <th class="py-3 px-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        
                        <template x-for="(task, index) in filteredTasks()" :key="task.id">
                            <tr class="hover:bg-slate-50/80 transition cursor-pointer" @click="openDetail(task)">
                                <td class="py-3.5 px-4 text-center font-mono text-slate-400" x-text="index + 1"></td>
                                <td class="py-3.5 px-4 max-w-sm">
                                    <div class="font-bold text-slate-900 hover:text-brand-600 transition" x-text="task.title"></div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-block px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-medium text-[10px]" x-text="task.module"></span>
                                        <span x-show="task.milestone" class="text-[10px] text-slate-400" x-text="task.milestone"></span>
                                    </div>
                                    <!-- Blocker note callout in table -->
                                    <template x-if="task.status === 'blocked' && task.blocker_notes">
                                        <div class="mt-1 text-[11px] text-rose-600 font-medium line-clamp-1">
                                            <i class="fa-solid fa-triangle-exclamation mr-1"></i> Blocker: <span x-text="task.blocker_notes"></span>
                                        </div>
                                    </template>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800': task.status === 'completed',
                                              'bg-blue-100 text-blue-800': task.status === 'in_progress',
                                              'bg-purple-100 text-purple-800': task.status === 'review',
                                              'bg-rose-100 text-rose-800 animate-pulse': task.status === 'blocked',
                                              'bg-slate-100 text-slate-600': task.status === 'backlog'
                                          }">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                              :class="{
                                                  'bg-emerald-600': task.status === 'completed',
                                                  'bg-blue-600': task.status === 'in_progress',
                                                  'bg-purple-600': task.status === 'review',
                                                  'bg-rose-600': task.status === 'blocked',
                                                  'bg-slate-400': task.status === 'backlog'
                                              }"></span>
                                        <span x-text="task.status === 'completed' ? 'Selesai' : (task.status === 'in_progress' ? 'Sedang Jalan' : (task.status === 'review' ? 'Review' : (task.status === 'blocked' ? 'Terkendala' : 'Backlog')))"></span>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                          :class="{
                                              'bg-rose-100 text-rose-700': task.priority === 'urgent',
                                              'bg-amber-100 text-amber-700': task.priority === 'high',
                                              'bg-blue-100 text-blue-700': task.priority === 'medium',
                                              'bg-slate-100 text-slate-600': task.priority === 'low'
                                          }"
                                          x-text="task.priority"></span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full transition-all"
                                                 :class="task.status === 'completed' ? 'bg-emerald-500' : (task.status === 'blocked' ? 'bg-rose-500' : 'bg-brand-500')"
                                                 :style="'width: ' + task.progress_percent + '%'"></div>
                                        </div>
                                        <span class="font-mono text-[11px] text-slate-600" x-text="task.progress_percent + '%'"></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                                    <div class="flex items-center gap-1.5">
                                        <i class="fa-regular fa-circle-user text-slate-400"></i>
                                        <span x-text="task.pic_name"></span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-semibold text-slate-800" 
                                         x-text="task.due_date ? (task.start_date ? new Date(task.start_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) + ' - ' + new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) : new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'})) : '-'"
                                         :class="task.status !== 'completed' && task.due_date && new Date(task.due_date) < new Date() ? 'text-rose-600 font-bold' : 'text-slate-700'"></div>
                                    <template x-if="task.completed_at">
                                        <div class="text-[10px] text-emerald-600 mt-0.5 flex items-center gap-1 font-medium">
                                            <i class="fa-solid fa-check"></i> Selesai: <span x-text="new Date(task.completed_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'})"></span>
                                        </div>
                                    </template>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <button type="button" @click.stop="openDetail(task)" class="p-1.5 text-slate-400 hover:text-brand-600 rounded-lg hover:bg-slate-100 transition">
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>

                        <!-- Empty State -->
                        <tr x-show="filteredTasks().length === 0">
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-3xl mb-3 text-slate-300"></i>
                                <p class="text-sm font-semibold text-slate-600">Tidak ada task yang sesuai dengan filter.</p>
                                <button type="button" @click="resetFilters()" class="mt-2 text-xs text-brand-600 hover:underline font-medium">Reset Semua Filter</button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

            <!-- ========================================================================= -->
            <!-- 2. VIEW MODE: KANBAN BOARD -->
            <!-- ========================================================================= -->
            <div x-show="viewMode === 'kanban'" class="p-5 sm:p-6 overflow-x-auto">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 min-w-[1000px]">
                    
                    <!-- Col 1: Backlog -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-slate-700 uppercase">Backlog / Rencana</span>
                            <span class="text-xs font-bold px-2 py-0.5 bg-slate-200 text-slate-700 rounded-full"
                                  x-text="filteredTasks().filter(t => t.status === 'backlog').length"></span>
                        </div>
                        <div class="space-y-3">
                            <template x-for="task in filteredTasks().filter(t => t.status === 'backlog')" :key="task.id">
                                <div class="bg-white p-3.5 rounded-xl border border-slate-200 shadow-sm hover:border-brand-400 transition cursor-pointer" @click="openDetail(task)">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase" x-text="task.module"></span>
                                    <h4 class="text-xs font-bold text-slate-800 mt-1 line-clamp-2" x-text="task.title"></h4>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                                        <span x-text="task.pic_name"></span>
                                        <span class="px-1.5 py-0.5 rounded text-[9px] uppercase font-bold"
                                              :class="task.priority === 'urgent' ? 'bg-rose-100 text-rose-700' : 'bg-slate-100 text-slate-600'"
                                              x-text="task.priority"></span>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400" x-show="task.start_date || task.due_date">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-regular fa-calendar text-slate-400"></i>
                                            <span x-text="task.start_date ? new Date(task.start_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) + ' - ' + new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) : (task.due_date ? new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) : '')"></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Col 2: In Progress -->
                    <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-blue-800 uppercase">Sedang Dikerjakan</span>
                            <span class="text-xs font-bold px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full"
                                  x-text="filteredTasks().filter(t => t.status === 'in_progress').length"></span>
                        </div>
                        <div class="space-y-3">
                            <template x-for="task in filteredTasks().filter(t => t.status === 'in_progress')" :key="task.id">
                                <div class="bg-white p-3.5 rounded-xl border border-blue-100 shadow-sm hover:border-blue-400 transition cursor-pointer" @click="openDetail(task)">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase" x-text="task.module"></span>
                                    <h4 class="text-xs font-bold text-slate-800 mt-1 line-clamp-2" x-text="task.title"></h4>
                                    <div class="w-full bg-slate-100 rounded-full h-1 mt-2">
                                        <div class="bg-blue-500 h-1 rounded-full" :style="'width: ' + task.progress_percent + '%'"></div>
                                    </div>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                                        <span x-text="task.pic_name"></span>
                                        <span class="font-mono text-blue-700 font-bold" x-text="task.progress_percent + '%'"></span>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400" x-show="task.start_date || task.due_date">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-regular fa-calendar text-slate-400"></i>
                                            <span x-text="task.start_date ? new Date(task.start_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) + ' - ' + new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) : (task.due_date ? new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) : '')"></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Col 3: Review -->
                    <div class="bg-purple-50/50 p-4 rounded-2xl border border-purple-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-purple-800 uppercase">Review & Testing</span>
                            <span class="text-xs font-bold px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full"
                                  x-text="filteredTasks().filter(t => t.status === 'review').length"></span>
                        </div>
                        <div class="space-y-3">
                            <template x-for="task in filteredTasks().filter(t => t.status === 'review')" :key="task.id">
                                <div class="bg-white p-3.5 rounded-xl border border-purple-100 shadow-sm hover:border-purple-400 transition cursor-pointer" @click="openDetail(task)">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase" x-text="task.module"></span>
                                    <h4 class="text-xs font-bold text-slate-800 mt-1 line-clamp-2" x-text="task.title"></h4>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                                        <span x-text="task.pic_name"></span>
                                        <span class="font-mono text-purple-700 font-bold" x-text="task.progress_percent + '%'"></span>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400" x-show="task.start_date || task.due_date">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-regular fa-calendar text-slate-400"></i>
                                            <span x-text="task.start_date ? new Date(task.start_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) + ' - ' + new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) : (task.due_date ? new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) : '')"></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Col 4: Blocked -->
                    <div class="bg-rose-50/50 p-4 rounded-2xl border border-rose-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-rose-800 uppercase">Terkendala (Blocker)</span>
                            <span class="text-xs font-bold px-2 py-0.5 bg-rose-100 text-rose-700 rounded-full"
                                  x-text="filteredTasks().filter(t => t.status === 'blocked').length"></span>
                        </div>
                        <div class="space-y-3">
                            <template x-for="task in filteredTasks().filter(t => t.status === 'blocked')" :key="task.id">
                                <div class="bg-white p-3.5 rounded-xl border border-rose-200 shadow-sm hover:border-rose-400 transition cursor-pointer" @click="openDetail(task)">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase" x-text="task.module"></span>
                                    <h4 class="text-xs font-bold text-slate-800 mt-1 line-clamp-2" x-text="task.title"></h4>
                                    <div x-show="task.blocker_notes" class="mt-2 text-[10px] text-rose-600 bg-rose-50 p-1.5 rounded-lg line-clamp-2" x-text="task.blocker_notes"></div>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-slate-500">
                                        <span x-text="task.pic_name"></span>
                                        <span class="text-rose-600 font-bold"><i class="fa-solid fa-triangle-exclamation"></i></span>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-rose-500 font-medium" x-show="task.start_date || task.due_date">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-regular fa-calendar text-rose-400"></i>
                                            <span x-text="task.start_date ? new Date(task.start_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) + ' - ' + new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) : (task.due_date ? new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) : '')"></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Col 5: Completed -->
                    <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-200">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-bold text-emerald-800 uppercase">Selesai (Done)</span>
                            <span class="text-xs font-bold px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded-full"
                                  x-text="filteredTasks().filter(t => t.status === 'completed').length"></span>
                        </div>
                        <div class="space-y-3">
                            <template x-for="task in filteredTasks().filter(t => t.status === 'completed')" :key="task.id">
                                <div class="bg-white p-3.5 rounded-xl border border-emerald-100 shadow-sm hover:border-emerald-400 transition cursor-pointer" @click="openDetail(task)">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase" x-text="task.module"></span>
                                    <h4 class="text-xs font-bold text-slate-800 mt-1 line-clamp-2" x-text="task.title"></h4>
                                    <div class="mt-2 flex items-center justify-between text-[11px] text-emerald-600 font-semibold">
                                        <span class="text-slate-500" x-text="task.pic_name"></span>
                                        <span><i class="fa-solid fa-circle-check"></i> 100%</span>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400" x-show="task.start_date || task.due_date">
                                        <span class="flex items-center gap-1">
                                            <i class="fa-regular fa-calendar text-slate-400"></i>
                                            <span x-text="task.start_date ? new Date(task.start_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) + ' - ' + new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) : (task.due_date ? new Date(task.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short'}) : '')"></span>
                                        </span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </main>

    <!-- ========================================================================= -->
    <!-- INTERACTIVE TASK DETAIL MODAL -->
    <!-- ========================================================================= -->
    <div x-show="showDetailModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" 
             @click="showDetailModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 transform transition-all"
                 @click.stop>
                
                <template x-if="activeTask">
                    <div>
                        <!-- Header Modal -->
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="activeTask.module"></span>
                                <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 mt-1 leading-snug" x-text="activeTask.title"></h3>
                            </div>
                            <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                                <i class="fa-solid fa-xmark text-xl"></i>
                            </button>
                        </div>

                        <!-- Status & Priority Badges -->
                        <div class="flex flex-wrap items-center gap-2 mb-5">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                                  :class="{
                                      'bg-emerald-100 text-emerald-800': activeTask.status === 'completed',
                                      'bg-blue-100 text-blue-800': activeTask.status === 'in_progress',
                                      'bg-purple-100 text-purple-800': activeTask.status === 'review',
                                      'bg-rose-100 text-rose-800': activeTask.status === 'blocked',
                                      'bg-slate-100 text-slate-700': activeTask.status === 'backlog'
                                  }"
                                  x-text="'Status: ' + (activeTask.status === 'completed' ? 'Selesai' : (activeTask.status === 'in_progress' ? 'Sedang Jalan' : (activeTask.status === 'review' ? 'Review / Testing' : (activeTask.status === 'blocked' ? 'Terkendala (Blocker)' : 'Backlog'))))"></span>
                            
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                                  :class="{
                                      'bg-rose-100 text-rose-700': activeTask.priority === 'urgent',
                                      'bg-amber-100 text-amber-700': activeTask.priority === 'high',
                                      'bg-blue-100 text-blue-700': activeTask.priority === 'medium',
                                      'bg-slate-100 text-slate-600': activeTask.priority === 'low'
                                  }"
                                  x-text="'Prioritas: ' + activeTask.priority"></span>
                        </div>

                        <!-- Progress Slider -->
                        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 mb-5">
                            <div class="flex items-center justify-between text-xs font-bold text-slate-700 mb-2">
                                <span>Pencapaian Task</span>
                                <span class="font-mono text-brand-600 text-sm" x-text="activeTask.progress_percent + '%'"></span>
                            </div>
                            <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-brand-500 h-2 rounded-full" :style="'width: ' + activeTask.progress_percent + '%'"></div>
                            </div>
                        </div>

                        <!-- Blocker Alert Box (If Blocked) -->
                        <template x-if="activeTask.status === 'blocked' && activeTask.blocker_notes">
                            <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 mb-5 text-xs text-rose-800">
                                <div class="font-bold flex items-center gap-1.5 mb-1 text-rose-700">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>Catatan Kendala (Blocker):</span>
                                </div>
                                <p class="leading-relaxed" x-text="activeTask.blocker_notes"></p>
                            </div>
                        </template>

                        <!-- Description -->
                        <div class="mb-5">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Deskripsi & Ruang Lingkup</h4>
                            <p class="text-xs sm:text-sm text-slate-700 leading-relaxed bg-slate-50 p-4 rounded-2xl border border-slate-100" 
                               x-text="activeTask.description || 'Tidak ada deskripsi rinci.'"></p>
                        </div>

                        <!-- Meta Info Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <div>
                                <span class="text-slate-400 block text-[11px]">Penanggung Jawab (PIC):</span>
                                <span class="font-bold text-slate-800" x-text="activeTask.pic_name"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Milestone / Sprint:</span>
                                <span class="font-bold text-slate-800" x-text="activeTask.milestone || '-'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Tanggal Mulai:</span>
                                <span class="font-bold text-slate-800" x-text="activeTask.start_date ? new Date(activeTask.start_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) : '-'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[11px]">Target Selesai:</span>
                                <span class="font-bold text-slate-800" x-text="activeTask.due_date ? new Date(activeTask.due_date).toLocaleDateString('id-ID', {day: 'numeric', month: 'short', year: 'numeric'}) : '-'"></span>
                            </div>
                            <template x-if="activeTask.completed_at">
                                <div class="col-span-2 sm:col-span-4 pt-2 border-t border-slate-200/60">
                                    <span class="text-slate-400 block text-[11px]">Tanggal Realisasi Selesai:</span>
                                    <span class="font-bold text-emerald-700 flex items-center gap-1.5 mt-0.5">
                                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                        <span x-text="new Date(activeTask.completed_at).toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'})"></span>
                                    </span>
                                </div>
                            </template>
                        </div>

                        <!-- Close Button -->
                        <div class="mt-6 flex justify-end">
                            <button type="button" 
                                    @click="showDetailModal = false"
                                    class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition">
                                Tutup
                            </button>
                        </div>

                    </div>
                </template>

            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- FOOTER -->
    <!-- ========================================================================= -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-12 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div>
                © {{ date('Y') }} <b>{{ $setting->project_name }}</b> — Apotek Keluarga
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Kontak: {{ $setting->client_contact_person }}</span>
                <span>•</span>
                <a href="{{ route('admin.login') }}" class="hover:text-brand-600 transition font-medium">Portal Admin</a>
            </div>
        </div>
    </footer>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        // 1. Chart Donut Status Breakdown
        const statusData = @json($chartStatusData);
        const optionsStatus = {
            series: statusData.series,
            labels: statusData.labels,
            colors: statusData.colors,
            chart: {
                type: 'donut',
                height: 260,
                fontFamily: 'inherit',
                toolbar: { show: false }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Task',
                                fontSize: '12px',
                                fontWeight: 600,
                                color: '#64748b',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'bottom',
                fontSize: '11px',
                markers: { radius: 12 }
            },
            stroke: { width: 0 }
        };
        const chartStatus = new ApexCharts(document.querySelector("#chart-status"), optionsStatus);
        chartStatus.render();

        // 2. Chart Modul Progress (Stacked Bar)
        const moduleData = @json($chartModuleData);
        const optionsModule = {
            series: [
                {
                    name: 'Selesai',
                    data: moduleData.completed
                },
                {
                    name: 'Outstanding',
                    data: moduleData.outstanding
                }
            ],
            colors: ['#10B981', '#3B82F6'],
            chart: {
                type: 'bar',
                height: 260,
                stacked: true,
                fontFamily: 'inherit',
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '55%'
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: moduleData.categories,
                labels: {
                    style: { fontSize: '11px', colors: '#64748b' }
                }
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', fontWeight: 600, colors: '#334155' }
                }
            },
            legend: { show: false },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4
            }
        };
        const chartModule = new ApexCharts(document.querySelector("#chart-module"), optionsModule);
        chartModule.render();

    });
</script>
@endpush
