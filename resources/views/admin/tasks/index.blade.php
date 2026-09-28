@extends('layouts.admin')

@section('title', 'Manajemen Task Proyek - Admin Apotek Keluarga')
@section('header_title', 'Manajemen Task & Outstanding Proyek')

@section('content')
<div class="space-y-6" 
     x-data="{
        showCreateModal: false,
        showEditModal: false,
        showStatusModal: false,
        showDeleteModal: false,
        selectedTask: null,
        deleteActionUrl: '',
        
        openEdit(task) {
            this.selectedTask = Object.assign({}, task);
            this.showEditModal = true;
        },
        
        openQuickStatus(task) {
            this.selectedTask = Object.assign({}, task);
            this.showStatusModal = true;
        },
        
        openDelete(task) {
            this.selectedTask = task;
            this.deleteActionUrl = '{{ url('admin/tasks') }}/' + task.id;
            this.showDeleteModal = true;
        }
     }">

    <!-- ========================================================================= -->
    <!-- TOP MINI KPI STATS BAR -->
    <!-- ========================================================================= -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        
        <a href="{{ route('admin.tasks.index') }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-brand-500 transition {{ !request('filter') && !request('status') ? 'ring-2 ring-brand-500/20' : '' }}">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Task</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl sm:text-2xl font-black text-slate-900">{{ $counts['total'] }}</span>
                <span class="text-[10px] text-slate-400 font-medium">Semua</span>
            </div>
        </a>

        <a href="{{ route('admin.tasks.index', ['filter' => 'outstanding']) }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-blue-500 transition {{ request('filter') === 'outstanding' ? 'ring-2 ring-blue-500/20' : '' }}">
            <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider block">Outstanding</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl sm:text-2xl font-black text-blue-600">{{ $counts['outstanding'] }}</span>
                <span class="text-[10px] text-blue-500 font-medium">Aktif</span>
            </div>
        </a>

        <a href="{{ route('admin.tasks.index', ['status' => 'in_progress']) }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-indigo-500 transition {{ request('status') === 'in_progress' ? 'ring-2 ring-indigo-500/20' : '' }}">
            <span class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider block">Berjalan</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl sm:text-2xl font-black text-indigo-600">{{ $counts['in_progress'] }}</span>
                <span class="text-[10px] text-indigo-500 font-medium">WIP</span>
            </div>
        </a>

        <a href="{{ route('admin.tasks.index', ['status' => 'completed']) }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-emerald-500 transition {{ request('status') === 'completed' ? 'ring-2 ring-emerald-500/20' : '' }}">
            <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider block">Selesai</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl sm:text-2xl font-black text-emerald-600">{{ $counts['completed'] }}</span>
                <span class="text-[10px] text-emerald-500 font-medium">Done</span>
            </div>
        </a>

        <a href="{{ route('admin.tasks.index', ['filter' => 'blocked']) }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-rose-500 transition {{ request('filter') === 'blocked' ? 'ring-2 ring-rose-500/20' : '' }}">
            <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider block">Terkendala</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl sm:text-2xl font-black @if($counts['blocked'] > 0) text-rose-600 @else text-slate-900 @endif">{{ $counts['blocked'] }}</span>
                <span class="text-[10px] text-rose-500 font-medium">Blocker</span>
            </div>
        </a>

        <a href="{{ route('admin.tasks.index', ['filter' => 'overdue']) }}" 
           class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm hover:border-amber-500 transition {{ request('filter') === 'overdue' ? 'ring-2 ring-amber-500/20' : '' }}">
            <span class="text-[11px] font-bold text-amber-600 uppercase tracking-wider block">Overdue</span>
            <div class="mt-1 flex items-baseline gap-2">
                <span class="text-xl sm:text-2xl font-black text-amber-600">{{ $counts['overdue'] }}</span>
                <span class="text-[10px] text-amber-500 font-medium">Terlambat</span>
            </div>
        </a>

    </div>

    <!-- ========================================================================= -->
    <!-- ACTION & FILTER BAR -->
    <!-- ========================================================================= -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-sm space-y-4">
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Daftar Pengerjaan Task Apotek Keluarga</h3>
                <p class="text-xs text-slate-500">Kelola rincian item, ubah progres, atau atur catatan blocker untuk setiap task.</p>
            </div>
            
            <!-- Button Tambah Task Baru -->
            <button type="button" 
                    @click="showCreateModal = true" 
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-600 hover:bg-brand-700 active:scale-[0.99] text-white text-xs font-bold rounded-xl shadow-lg shadow-brand-600/25 transition cursor-pointer">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Task Baru</span>
            </button>
        </div>

        <!-- Filter Form -->
        <form action="{{ route('admin.tasks.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3 pt-2">
            
            <!-- Search -->
            <div class="lg:col-span-2 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari judul, PIC, deskripsi..." 
                       class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
            </div>

            <!-- Milestone / Fase Timeline -->
            <div>
                <select name="milestone" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                    <option value="">Semua Fase Timeline</option>
                    @foreach($allMilestones as $ms)
                        <option value="{{ $ms }}" {{ request('milestone') === $ms ? 'selected' : '' }}>{{ $ms }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Modul -->
            <div>
                <select name="module" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                    <option value="">Semua Modul</option>
                    @foreach($allModules as $mod)
                        <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status -->
            <div>
                <select name="status" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                    <option value="">Semua Status</option>
                    <option value="backlog" {{ request('status') === 'backlog' ? 'selected' : '' }}>Backlog</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>Sedang Dikerjakan</option>
                    <option value="review" {{ request('status') === 'review' ? 'selected' : '' }}>Review / Testing</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Terkendala (Blocker)</option>
                </select>
            </div>

            <!-- Prioritas -->
            <div>
                <select name="priority" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                    <option value="">Semua Prioritas</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Mendesak (Urgent)</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Tinggi (High)</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Sedang (Medium)</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Rendah (Low)</option>
                </select>
            </div>

            <!-- Submit Filter & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-semibold transition">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'module', 'milestone', 'status', 'priority', 'filter', 'sort']))
                    <a href="{{ route('admin.tasks.index') }}" title="Reset Filter" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>

        </form>

    </div>

    <!-- ========================================================================= -->
    <!-- TASKS TABLE -->
    <!-- ========================================================================= -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/75 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4 w-12 text-center">ID</th>
                        <th class="py-3 px-4">Judul Task & Modul</th>
                        <th class="py-3 px-4">PIC</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Prioritas</th>
                        <th class="py-3 px-4">Progres</th>
                        <th class="py-3 px-4">Timeline / Jadwal</th>
                        <th class="py-3 px-4 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    
                    @forelse($tasks as $task)
                    <tr class="hover:bg-slate-50/80 transition">
                        
                        <!-- ID -->
                        <td class="py-3.5 px-4 text-center font-mono text-slate-400">
                            #{{ str_pad($task->id, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        <!-- Title & Module -->
                        <td class="py-3.5 px-4 max-w-sm">
                            <div class="font-bold text-slate-900">{{ $task->title }}</div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 font-semibold text-[10px]">{{ $task->module }}</span>
                                @if($task->milestone)
                                    <span class="text-[10px] text-slate-400">{{ $task->milestone }}</span>
                                @endif
                            </div>
                            @if($task->status === 'blocked' && $task->blocker_notes)
                                <div class="mt-1 text-[11px] text-rose-600 bg-rose-50 px-2 py-1 rounded-md border border-rose-100">
                                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                                    <b>Blocker:</b> {{ $task->blocker_notes }}
                                </div>
                            @endif
                        </td>

                        <!-- PIC -->
                        <td class="py-3.5 px-4 whitespace-nowrap text-slate-600">
                            <div class="flex items-center gap-1.5">
                                <i class="fa-regular fa-user text-slate-400"></i>
                                <span>{{ $task->pic_name }}</span>
                            </div>
                        </td>

                        <!-- Status Badge (Clickable to Quick Status Modal) -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <button type="button" 
                                    @click="openQuickStatus({{ Js::from($task) }})" 
                                    title="Klik untuk ubah status cepat"
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold transition hover:ring-2 hover:ring-offset-1 cursor-pointer
                                    @if($task->status === 'completed') bg-emerald-100 text-emerald-800 hover:ring-emerald-400
                                    @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800 hover:ring-blue-400
                                    @elseif($task->status === 'review') bg-purple-100 text-purple-800 hover:ring-purple-400
                                    @elseif($task->status === 'blocked') bg-rose-100 text-rose-800 hover:ring-rose-400 animate-pulse
                                    @else bg-slate-100 text-slate-600 hover:ring-slate-400 @endif">
                                <span>{{ $task->status_label }}</span>
                                <i class="fa-solid fa-bolt text-[10px] opacity-60"></i>
                            </button>
                        </td>

                        <!-- Priority -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider
                                @if($task->priority === 'urgent') bg-rose-100 text-rose-700
                                @elseif($task->priority === 'high') bg-amber-100 text-amber-700
                                @elseif($task->priority === 'medium') bg-blue-100 text-blue-700
                                @else bg-slate-100 text-slate-600 @endif">
                                {{ $task->priority }}
                            </span>
                        </td>

                        <!-- Progress Bar -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-16 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                    <div class="h-1.5 rounded-full @if($task->status === 'completed') bg-emerald-500 @elseif($task->status === 'blocked') bg-rose-500 @else bg-brand-500 @endif"
                                         style="width: {{ $task->progress_percent }}%"></div>
                                </div>
                                <span class="font-mono text-[11px] text-slate-600">{{ $task->progress_percent }}%</span>
                            </div>
                        </td>

                        <!-- Due Date / Timeline -->
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="@if($task->is_overdue) text-rose-600 font-bold @else text-slate-700 font-semibold @endif">
                                @if($task->start_date && $task->due_date)
                                    {{ $task->start_date->format('d/m') }} - {{ $task->due_date->format('d/m/Y') }}
                                @elseif($task->due_date)
                                    {{ $task->due_date->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                                @if($task->is_overdue)
                                    <span class="block text-[10px] font-semibold text-rose-500">Overdue!</span>
                                @endif
                            </div>
                            @if($task->completed_at)
                                <div class="text-[10px] text-slate-400 mt-0.5 flex items-center gap-1">
                                    <i class="fa-solid fa-check text-emerald-500"></i> Done: {{ $task->completed_at->format('d/m/Y') }}
                                </div>
                            @endif
                        </td>

                        <!-- Actions -->
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <!-- Quick Status -->
                                <button type="button" 
                                        @click="openQuickStatus({{ Js::from($task) }})" 
                                        title="Ubah Status Cepat" 
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                    <i class="fa-solid fa-bolt"></i>
                                </button>
                                <!-- Edit -->
                                <button type="button" 
                                        @click="openEdit({{ Js::from($task) }})" 
                                        title="Edit Task" 
                                        class="p-1.5 text-slate-600 hover:text-brand-600 hover:bg-slate-100 rounded-lg transition">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <!-- Delete -->
                                <button type="button" 
                                        @click="openDelete({{ Js::from($task) }})" 
                                        title="Hapus Task" 
                                        class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-folder-open text-3xl mb-3 text-slate-300"></i>
                            <p class="text-sm font-semibold text-slate-600">Belum ada task yang terdaftar atau cocok dengan pencarian.</p>
                        </td>
                    </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($tasks->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $tasks->links() }}
        </div>
        @endif

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: TAMBAH TASK BARU -->
    <!-- ========================================================================= -->
    <div x-show="showCreateModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showCreateModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100" @click.stop>
                
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-circle-plus text-brand-600"></i>
                        <span>Tambah Task Baru</span>
                    </h3>
                    <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('admin.tasks.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Judul Task *</label>
                        <input type="text" name="title" required placeholder="Contoh: Integrasi Barcode Scanner..." 
                               class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Modul / Fitur *</label>
                            <input type="text" name="module" required list="modules-list" placeholder="Pilih atau ketik modul..." 
                                   class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                            <datalist id="modules-list">
                                @foreach($allModules as $mod)
                                    <option value="{{ $mod }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Milestone / Fase Timeline</label>
                            <input type="text" name="milestone" list="milestones-list" placeholder="Pilih atau ketik fase timeline..." 
                                   class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                            <datalist id="milestones-list">
                                @foreach($allMilestones as $ms)
                                    <option value="{{ $ms }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Status *</label>
                            <select name="status" required class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                <option value="backlog">Backlog / Rencana</option>
                                <option value="in_progress" selected>Sedang Dikerjakan</option>
                                <option value="review">Review / Testing</option>
                                <option value="completed">Selesai</option>
                                <option value="blocked">Terkendala (Blocker)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Prioritas *</label>
                            <select name="priority" required class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                <option value="low">Rendah (Low)</option>
                                <option value="medium" selected>Sedang (Medium)</option>
                                <option value="high">Tinggi (High)</option>
                                <option value="urgent">Mendesak (Urgent)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Progress (%) *</label>
                            <input type="number" name="progress_percent" min="0" max="100" value="0" required 
                                   class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">PIC / Penanggung Jawab *</label>
                            <input type="text" name="pic_name" required placeholder="Nama PIC..." 
                                   class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai (Start Date)</label>
                            <input type="date" name="start_date" 
                                   class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Target Selesai (Due Date)</label>
                            <input type="date" name="due_date" 
                                   class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Task</label>
                        <textarea name="description" rows="2" placeholder="Rincian teknis pengerjaan task..." 
                                  class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-rose-700 mb-1">Catatan Kendala (Khusus jika status Terkendala/Blocker)</label>
                        <textarea name="blocker_notes" rows="2" placeholder="Isi penyebab blocker atau hambatan..." 
                                  class="w-full py-2.5 px-3 text-xs bg-rose-50/50 border border-rose-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 outline-none"></textarea>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-brand-600/20">Simpan Task</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: EDIT TASK -->
    <!-- ========================================================================= -->
    <div x-show="showEditModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showEditModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-100" @click.stop>
                
                <template x-if="selectedTask">
                    <div>
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-pen-to-square text-brand-600"></i>
                                <span>Edit Task: <span class="text-brand-600" x-text="selectedTask.title"></span></span>
                            </h3>
                            <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <form :action="'{{ url('admin/tasks') }}/' + selectedTask.id" method="POST" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Judul Task *</label>
                                <input type="text" name="title" x-model="selectedTask.title" required 
                                       class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Modul / Fitur *</label>
                                    <input type="text" name="module" x-model="selectedTask.module" required list="modules-list" 
                                           class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Milestone / Fase Timeline</label>
                                    <input type="text" name="milestone" x-model="selectedTask.milestone" list="milestones-list" placeholder="Pilih atau ketik fase timeline..." 
                                           class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Status *</label>
                                    <select name="status" x-model="selectedTask.status" required class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                        <option value="backlog">Backlog / Rencana</option>
                                        <option value="in_progress">Sedang Dikerjakan</option>
                                        <option value="review">Review / Testing</option>
                                        <option value="completed">Selesai</option>
                                        <option value="blocked">Terkendala (Blocker)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Prioritas *</label>
                                    <select name="priority" x-model="selectedTask.priority" required class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                        <option value="low">Rendah (Low)</option>
                                        <option value="medium">Sedang (Medium)</option>
                                        <option value="high">Tinggi (High)</option>
                                        <option value="urgent">Mendesak (Urgent)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Progress (%) *</label>
                                    <input type="number" name="progress_percent" x-model="selectedTask.progress_percent" min="0" max="100" required 
                                           class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">PIC *</label>
                                    <input type="text" name="pic_name" x-model="selectedTask.pic_name" required 
                                           class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai (Start Date)</label>
                                    <input type="date" name="start_date" :value="selectedTask.start_date ? selectedTask.start_date.substring(0, 10) : ''" 
                                           class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Target Selesai (Due Date)</label>
                                    <input type="date" name="due_date" :value="selectedTask.due_date ? selectedTask.due_date.substring(0, 10) : ''" 
                                           class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Task</label>
                                <textarea name="description" x-model="selectedTask.description" rows="2" 
                                          class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none"></textarea>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-rose-700 mb-1">Catatan Kendala (Blocker Notes)</label>
                                <textarea name="blocker_notes" x-model="selectedTask.blocker_notes" rows="2" 
                                          class="w-full py-2.5 px-3 text-xs bg-rose-50/50 border border-rose-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 outline-none"></textarea>
                            </div>

                            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                                <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">Batal</button>
                                <button type="submit" class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-brand-600/20">Perbarui Task</button>
                            </div>
                        </form>
                    </div>
                </template>

            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: QUICK STATUS UPDATER -->
    <!-- ========================================================================= -->
    <div x-show="showStatusModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showStatusModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100" @click.stop>
                
                <template x-if="selectedTask">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-bolt text-blue-600"></i>
                                <span>Ubah Status Cepat</span>
                            </h3>
                            <button type="button" @click="showStatusModal = false" class="text-slate-400 hover:text-slate-600 p-1">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="bg-slate-50 p-3 rounded-xl mb-4 border border-slate-100 text-xs">
                            <span class="text-slate-400 font-mono text-[10px]" x-text="'#TSK-' + selectedTask.id"></span>
                            <h4 class="font-bold text-slate-800" x-text="selectedTask.title"></h4>
                        </div>

                        <form :action="'{{ url('admin/tasks') }}/' + selectedTask.id + '/quick-status'" method="POST" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Status Baru *</label>
                                <select name="status" x-model="selectedTask.status" required 
                                        class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                                    <option value="backlog">Backlog / Rencana</option>
                                    <option value="in_progress">Sedang Dikerjakan</option>
                                    <option value="review">Review / Testing</option>
                                    <option value="completed">Selesai (Done 100%)</option>
                                    <option value="blocked">Terkendala (Blocker)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Progress (%)</label>
                                <input type="number" name="progress_percent" x-model="selectedTask.progress_percent" min="0" max="100" 
                                       class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:border-brand-500 focus:ring-2 focus:ring-brand-200 outline-none">
                            </div>

                            <div x-show="selectedTask.status === 'blocked'">
                                <label class="block text-xs font-bold text-rose-700 mb-1">Catatan Kendala (Blocker) *</label>
                                <textarea name="blocker_notes" x-model="selectedTask.blocker_notes" rows="3" placeholder="Sebutkan hambatan yang terjadi..." 
                                          class="w-full py-2.5 px-3 text-xs bg-rose-50/50 border border-rose-200 rounded-xl focus:border-rose-500 focus:ring-2 focus:ring-rose-200 outline-none"></textarea>
                            </div>

                            <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                                <button type="button" @click="showStatusModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">Batal</button>
                                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-blue-600/20">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </template>

            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 4: KONFIRMASI HAPUS -->
    <!-- ========================================================================= -->
    <div x-show="showDeleteModal" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         role="dialog" 
         aria-modal="true">
        
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-3xl max-w-sm w-full p-6 text-center shadow-2xl border border-slate-100" @click.stop>
                
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 mx-auto flex items-center justify-center text-xl mb-4">
                    <i class="fa-solid fa-trash-can"></i>
                </div>

                <h3 class="text-base font-bold text-slate-900">Hapus Task Ini?</h3>
                <p class="text-xs text-slate-500 mt-1 mb-5">
                    Task <span class="font-bold text-slate-700" x-text="selectedTask ? selectedTask.title : ''"></span> akan dihapus permanen dari sistem monitoring.
                </p>

                <form :action="deleteActionUrl" method="POST" class="flex items-center justify-center gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition shadow-md shadow-rose-600/20">
                        Ya, Hapus
                    </button>
                </form>

            </div>
        </div>
    </div>

</div>
@endsection
