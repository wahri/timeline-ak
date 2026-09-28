<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSetting extends Model
{
    protected $fillable = [
        'project_name',
        'client_password',
        'project_description',
        'start_date',
        'target_completion_date',
        'client_contact_person',
        'version_tag',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'target_completion_date' => 'date',
        ];
    }

    /**
     * Ambil atau inisialisasi pengaturan proyek saat ini.
     */
    public static function current(): self
    {
        return static::firstOrCreate([], [
            'project_name' => 'Digitalisasi Sistem Apotek Keluarga',
            'client_password' => 'keluarga2026',
            'project_description' => 'Sistem monitoring pengembangan aplikasi farmasi, kasir POS, stok obat FEFO/FIFO, dan pelaporan Kemenkes.',
            'start_date' => now()->startOfMonth(),
            'target_completion_date' => now()->addMonths(2)->endOfMonth(),
            'client_contact_person' => 'Direksi Apotek Keluarga',
            'version_tag' => 'v1.0.0-Beta',
        ]);
    }
}
