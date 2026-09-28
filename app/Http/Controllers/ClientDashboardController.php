<?php

namespace App\Http\Controllers;

use App\Models\ProjectSetting;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientDashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Monitoring Utama Klien.
     */
    public function index(Request $request)
    {
        $setting = ProjectSetting::current();

        $todayStr = Carbon::today()->toDateString();

        // Ambil semua task dengan pengurutan prioritas dan tenggat waktu
        $tasksQuery = Task::query()->orderByRaw("
            CASE 
                WHEN status = 'blocked' THEN 1
                WHEN due_date IS NOT NULL AND due_date < '{$todayStr}' AND status != 'completed' THEN 2
                WHEN status = 'in_progress' THEN 3
                WHEN status = 'review' THEN 4
                WHEN status = 'backlog' THEN 5
                ELSE 6
            END ASC
        ")->orderBy('due_date', 'asc');

        $allTasks = $tasksQuery->get();

        // 1. KPI Metrik Utama
        $totalTasks = $allTasks->count();
        $completedTasks = $allTasks->where('status', 'completed')->count();
        $inProgressTasks = $allTasks->where('status', 'in_progress')->count();
        $reviewTasks = $allTasks->where('status', 'review')->count();
        $blockedTasks = $allTasks->where('status', 'blocked')->count();
        $backlogTasks = $allTasks->where('status', 'backlog')->count();
        
        $outstandingTasks = $totalTasks - $completedTasks;

        $overdueTasksList = $allTasks->filter(function ($task) {
            return $task->is_overdue;
        });
        $overdueCount = $overdueTasksList->count();

        // Persentase keseluruhan
        $overallProgressPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
        
        // Perhitungan rata-rata progress aktual (termasuk parsial %)
        $weightedProgressPercent = $totalTasks > 0 ? round($allTasks->avg('progress_percent')) : 0;

        // 2. Timeline & Health Calculation
        $today = Carbon::today();
        $startDate = $setting->start_date ? Carbon::parse($setting->start_date) : $today->copy()->subDays(30);
        $targetDate = $setting->target_completion_date ? Carbon::parse($setting->target_completion_date) : $today->copy()->addDays(30);

        $totalProjectDays = max(1, $startDate->diffInDays($targetDate));
        $elapsedDays = $startDate->diffInDays($today, false);
        $daysRemaining = $today->diffInDays($targetDate, false);

        $timeElapsedPercent = min(100, max(0, round(($elapsedDays / $totalProjectDays) * 100)));

        // Status Kesehatan Proyek (Health Indicator)
        if ($blockedTasks > 0 || $overdueCount > 0) {
            $issues = [];
            if ($blockedTasks > 0) $issues[] = "{$blockedTasks} kendala (blocker) aktif";
            if ($overdueCount > 0) $issues[] = "{$overdueCount} task melewati target";
            $issuesText = implode(' & ', $issues);

            $projectHealth = [
                'status' => 'Needs Attention',
                'color' => 'amber',
                'badge' => 'Perlu Perhatian',
                'desc' => "Terdapat {$issuesText}.",
            ];
        } elseif ($daysRemaining < 0 && $outstandingTasks > 0) {
            $projectHealth = [
                'status' => 'Delayed',
                'color' => 'rose',
                'badge' => 'Terlambat',
                'desc' => 'Target tanggal selesai telah terlampaui dengan task outstanding tersisa.',
            ];
        } else {
            $projectHealth = [
                'status' => 'On Track',
                'color' => 'emerald',
                'badge' => 'Sesuai Rencana',
                'desc' => 'Perkembangan proyek berjalan stabil menuju target penyelesaian.',
            ];
        }

        // 3. Data Agregat untuk Chart ApexCharts
        // Donut: Breakdown Status
        $chartStatusData = [
            'series' => [$completedTasks, $inProgressTasks, $reviewTasks, $blockedTasks, $backlogTasks],
            'labels' => ['Selesai', 'Sedang Berjalan', 'Review/Testing', 'Terkendala', 'Rencana/Backlog'],
            'colors' => ['#10B981', '#3B82F6', '#8B5CF6', '#EF4444', '#94A3B8'],
        ];

        // Bar: Modul Breakdown
        $modulesGroup = $allTasks->groupBy('module');
        $moduleCategories = [];
        $moduleCompleted = [];
        $moduleOutstanding = [];

        foreach ($modulesGroup as $moduleName => $items) {
            $moduleCategories[] = $moduleName;
            $moduleCompleted[] = $items->where('status', 'completed')->count();
            $moduleOutstanding[] = $items->where('status', '!=', 'completed')->count();
        }

        $chartModuleData = [
            'categories' => $moduleCategories,
            'completed' => $moduleCompleted,
            'outstanding' => $moduleOutstanding,
        ];

        // Filter dropdown lists
        $modules = $allTasks->pluck('module')->unique()->values();
        $milestones = $allTasks->pluck('milestone')->filter()->unique()->values();
        $pics = $allTasks->pluck('pic_name')->unique()->values();

        // 4. Struktur 7 Fase Roadmap Timeline Proyek (Sesuai Desain Gambar)
        $phaseDefinitions = [
            [
                'id' => 1,
                'name' => 'Fase 1: Planning & Setup Workspace',
                'short' => 'Fase 1: Planning & Setup',
                'icon' => 'fa-solid fa-server',
                'period' => 'Sprint 1 (Kickoff - W2)',
            ],
            [
                'id' => 2,
                'name' => 'Fase 2: Master Data & Supply Chain',
                'short' => 'Fase 2: Master Data & Supply Chain',
                'icon' => 'fa-solid fa-boxes-packing',
                'period' => 'Sprint 2 (W3 - W5)',
            ],
            [
                'id' => 3,
                'name' => 'Fase 3: Sales, POS & Multi-UoM',
                'short' => 'Fase 3: Sales & Kasir POS',
                'icon' => 'fa-solid fa-cash-register',
                'period' => 'Sprint 3 (W6 - W8)',
            ],
            [
                'id' => 4,
                'name' => 'Fase 4: Rollout 30 Outlet (100% Live)',
                'short' => 'Fase 4: Rollout 30 Outlet (Live)',
                'icon' => 'fa-solid fa-store',
                'period' => 'Deployment Wave 1 - 4',
            ],
            [
                'id' => 5,
                'name' => 'Fase 5: Accounting & Financial Refinement',
                'short' => 'Fase 5: Accounting Refinement',
                'icon' => 'fa-solid fa-calculator',
                'period' => 'Sprint 4 - Post Rollout',
            ],
            [
                'id' => 6,
                'name' => 'Fase 6: HR & General Affair (HCGA)',
                'short' => 'Fase 6: HR & HCGA',
                'icon' => 'fa-solid fa-users-gear',
                'period' => 'Sprint 5 (Next Phase)',
            ],
            [
                'id' => 7,
                'name' => 'Fase 7: Digital Expansion & AI',
                'short' => 'Fase 7: Digital & AI Intelligence',
                'icon' => 'fa-solid fa-robot',
                'period' => 'Continuous Integration',
            ],
        ];

        $roadmapPhases = [];
        foreach ($phaseDefinitions as $pDef) {
            $phaseTasks = $allTasks->where('milestone', $pDef['name']);
            $pTotal = $phaseTasks->count();
            $pCompleted = $phaseTasks->where('status', 'completed')->count();
            $pBlocked = $phaseTasks->where('status', 'blocked')->count();
            $pPercent = $pTotal > 0 ? round(($pCompleted / $pTotal) * 100) : 0;

            if ($pPercent == 100) {
                $pStatus = 'completed';
                $pBadge = 'Selesai (100%)';
                $pColor = 'emerald';
            } elseif ($pBlocked > 0) {
                $pStatus = 'blocked';
                $pBadge = "Terkendala ({$pBlocked} Blocker)";
                $pColor = 'rose';
            } elseif ($pPercent > 0) {
                $pStatus = 'in_progress';
                $pBadge = "Sedang Berjalan ({$pPercent}%)";
                $pColor = 'blue';
            } else {
                $pStatus = 'backlog';
                $pBadge = 'Next Phase (Rencana)';
                $pColor = 'slate';
            }

            $roadmapPhases[] = [
                'id' => $pDef['id'],
                'name' => $pDef['name'],
                'short' => $pDef['short'],
                'icon' => $pDef['icon'],
                'period' => $pDef['period'],
                'total' => $pTotal,
                'completed' => $pCompleted,
                'outstanding' => $pTotal - $pCompleted,
                'percent' => $pPercent,
                'status' => $pStatus,
                'badge' => $pBadge,
                'color' => $pColor,
            ];
        }

        // Action Items (Blocker & Overdue) untuk kartu alert khusus
        $urgentActionItems = $allTasks->filter(function ($t) {
            return $t->status === 'blocked' || $t->is_overdue;
        });

        return view('client.dashboard', compact(
            'setting',
            'allTasks',
            'totalTasks',
            'completedTasks',
            'inProgressTasks',
            'reviewTasks',
            'blockedTasks',
            'backlogTasks',
            'outstandingTasks',
            'overdueCount',
            'overallProgressPercent',
            'weightedProgressPercent',
            'daysRemaining',
            'elapsedDays',
            'totalProjectDays',
            'timeElapsedPercent',
            'projectHealth',
            'chartStatusData',
            'chartModuleData',
            'modules',
            'milestones',
            'pics',
            'urgentActionItems',
            'roadmapPhases'
        ));
    }

    /**
     * Ekspor Data Monitoring ke format CSV/Excel.
     */
    public function export(Request $request): StreamedResponse
    {
        $setting = ProjectSetting::current();
        $tasks = Task::orderBy('module')->orderBy('id')->get();
        $filename = 'Monitoring-Task-Apotek-Keluarga-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tasks, $setting) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM untuk Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Judul Header
            fputcsv($handle, ['LAPORAN MONITORING TASK PROYEK - ' . strtoupper($setting->project_name)]);
            fputcsv($handle, ['Tanggal Unduh: ' . date('d/m/Y H:i')]);
            fputcsv($handle, []);

            // Baris Kolom Tabel
            fputcsv($handle, [
                'No',
                'ID Task',
                'Modul / Fitur',
                'Milestone / Sprint',
                'Judul Task',
                'Deskripsi',
                'Status',
                'Prioritas',
                'Progress (%)',
                'PIC / Penanggung Jawab',
                'Tanggal Mulai',
                'Target Selesai',
                'Tanggal Selesai',
                'Catatan Kendala (Blocker)',
            ]);

            $no = 1;
            foreach ($tasks as $task) {
                fputcsv($handle, [
                    $no++,
                    '#TSK-' . str_pad($task->id, 3, '0', STR_PAD_LEFT),
                    $task->module,
                    $task->milestone ?? '-',
                    $task->title,
                    $task->description ?? '-',
                    $task->status_label,
                    $task->priority_label,
                    $task->progress_percent . '%',
                    $task->pic_name,
                    $task->start_date ? $task->start_date->format('d/m/Y') : '-',
                    $task->due_date ? $task->due_date->format('d/m/Y') : '-',
                    $task->completed_at ? $task->completed_at->format('d/m/Y H:i') : '-',
                    $task->blocker_notes ?? '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
