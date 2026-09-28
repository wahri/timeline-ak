<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'module',
        'milestone',
        'status',
        'priority',
        'pic_name',
        'progress_percent',
        'start_date',
        'due_date',
        'completed_at',
        'blocker_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'due_date' => 'date',
            'completed_at' => 'datetime',
            'progress_percent' => 'integer',
        ];
    }

    /**
     * Cek apakah task termasuk Overdue (lewat tenggat waktu dan belum selesai).
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'completed' || !$this->due_date) {
            return false;
        }

        return $this->due_date->lt(Carbon::today());
    }

    /**
     * Cek apakah task termasuk Outstanding (belum selesai dikerjakan).
     */
    public function getIsOutstandingAttribute(): bool
    {
        return $this->status !== 'completed';
    }

    /**
     * Label status dalam bahasa Indonesia yang ramah pengguna.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'backlog' => 'Backlog / Rencana',
            'in_progress' => 'Sedang Dikerjakan',
            'review' => 'Review / Testing',
            'completed' => 'Selesai',
            'blocked' => 'Terkendala (Blocker)',
            default => ucfirst($this->status),
        };
    }

    /**
     * Label prioritas dalam format rapi.
     */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'Rendah (Low)',
            'medium' => 'Sedang (Medium)',
            'high' => 'Tinggi (High)',
            'urgent' => 'Mendesak (Urgent)',
            default => ucfirst($this->priority),
        };
    }

    /**
     * Scope untuk mengambil task yang belum selesai (Outstanding).
     */
    public function scopeOutstanding(Builder $query): Builder
    {
        return $query->where('status', '!=', 'completed');
    }

    /**
     * Scope untuk task yang sudah selesai.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope untuk task yang terkendala.
     */
    public function scopeBlocked(Builder $query): Builder
    {
        return $query->where('status', 'blocked');
    }

    /**
     * Scope untuk task yang melewati deadline.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', 'completed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today());
    }
}
