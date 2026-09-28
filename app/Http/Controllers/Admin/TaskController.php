<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Tampilkan daftar task untuk pengelolaan admin.
     */
    public function index(Request $request)
    {
        $query = Task::query();

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('pic_name', 'like', "%{$search}%");
            });
        }

        // Filter Modul
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        // Filter Milestone / Fase Timeline
        if ($request->filled('milestone')) {
            $query->where('milestone', $request->input('milestone'));
        }

        // Filter Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter Prioritas
        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        // Filter Khusus: Outstanding / Overdue
        if ($request->input('filter') === 'outstanding') {
            $query->where('status', '!=', 'completed');
        } elseif ($request->input('filter') === 'overdue') {
            $query->where('status', '!=', 'completed')
                  ->whereNotNull('due_date')
                  ->where('due_date', '<', Carbon::today());
        } elseif ($request->input('filter') === 'blocked') {
            $query->where('status', 'blocked');
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'due_date' => $query->orderBy('due_date', 'asc'),
            'priority' => $query->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END"),
            'progress' => $query->orderBy('progress_percent', 'desc'),
            'oldest' => $query->orderBy('id', 'asc'),
            default => $query->orderBy('id', 'desc'),
        };

        $tasks = $query->paginate(15)->withQueryString();

        // Opsi-opsi filter
        $allModules = Task::distinct()->pluck('module')->filter()->values();
        $allPics = Task::distinct()->pluck('pic_name')->filter()->values();
        $allMilestones = Task::distinct()->pluck('milestone')->filter()->values();

        // Hitungan Ringkasan Admin
        $counts = [
            'total' => Task::count(),
            'completed' => Task::where('status', 'completed')->count(),
            'in_progress' => Task::where('status', 'in_progress')->count(),
            'blocked' => Task::where('status', 'blocked')->count(),
            'outstanding' => Task::where('status', '!=', 'completed')->count(),
            'overdue' => Task::where('status', '!=', 'completed')
                ->whereNotNull('due_date')
                ->where('due_date', '<', Carbon::today())->count(),
        ];

        return view('admin.tasks.index', compact('tasks', 'allModules', 'allPics', 'allMilestones', 'counts'));
    }

    /**
     * Simpan task baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'module' => ['required', 'string', 'max:100'],
            'milestone' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:backlog,in_progress,review,completed,blocked'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'pic_name' => ['required', 'string', 'max:100'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'blocker_notes' => ['nullable', 'string'],
        ], [
            'title.required' => 'Judul task wajib diisi.',
            'module.required' => 'Modul / Fitur wajib dipilih atau diisi.',
            'pic_name.required' => 'PIC / Penanggung jawab wajib diisi.',
        ]);

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
            $validated['progress_percent'] = 100;
        }

        Task::create($validated);

        return redirect()->route('admin.tasks.index')
            ->with('success', 'Task baru berhasil ditambahkan ke daftar monitoring!');
    }

    /**
     * Update detail task.
     */
    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'module' => ['required', 'string', 'max:100'],
            'milestone' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:backlog,in_progress,review,completed,blocked'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'pic_name' => ['required', 'string', 'max:100'],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
            'blocker_notes' => ['nullable', 'string'],
        ]);

        // Atur completed_at jika status diubah ke completed
        if ($validated['status'] === 'completed') {
            if (!$task->completed_at) {
                $validated['completed_at'] = now();
            }
            $validated['progress_percent'] = 100;
            $validated['blocker_notes'] = null; // Bersihkan blocker jika sudah selesai
        } elseif ($task->status === 'completed' && $validated['status'] !== 'completed') {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        return redirect()->route('admin.tasks.index')
            ->with('success', "Task \"{$task->title}\" berhasil diperbarui!");
    }

    /**
     * Hapus task.
     */
    public function destroy(Task $task)
    {
        $title = $task->title;
        $task->delete();

        return redirect()->route('admin.tasks.index')
            ->with('success', "Task \"{$title}\" berhasil dihapus!");
    }

    /**
     * Update cepat status & catatan blocker.
     */
    public function quickStatus(Request $request, Task $task)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:backlog,in_progress,review,completed,blocked'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'blocker_notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'completed') {
            $validated['completed_at'] = now();
            $validated['progress_percent'] = 100;
            $validated['blocker_notes'] = null;
        } elseif ($validated['status'] === 'blocked') {
            // Biarkan progress_percent tetap, catat blocker_notes
        } else {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Status task berhasil diperbarui.',
                'task' => $task,
            ]);
        }

        return back()->with('success', "Status task \"{$task->title}\" berhasil diubah menjadi {$task->status_label}!");
    }
}
