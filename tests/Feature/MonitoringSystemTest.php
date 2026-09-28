<?php

namespace Tests\Feature;

use App\Models\ProjectSetting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MonitoringSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat setting proyek awal
        ProjectSetting::updateOrCreate(
            ['id' => 1],
            [
                'project_name' => 'Monitoring Apotek Keluarga',
                'client_password' => 'keluarga2026',
                'project_description' => 'Test Deskripsi',
                'start_date' => now()->subDays(10)->toDateString(),
                'target_completion_date' => now()->addDays(20)->toDateString(),
            ]
        );

        // Buat akun admin
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Admin Apotek Keluarga',
                'email' => 'admin@apotekkeluarga.com',
                'password' => Hash::make('admin123'),
            ]
        );
    }

    /**
     * Klien yang belum terautentikasi otomatis dialihkan ke gerbang password.
     */
    public function test_unauthenticated_client_is_redirected_to_gate(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('client.gate'));
    }

    /**
     * Halaman gerbang password dapat diakses dengan status 200.
     */
    public function test_client_gate_screen_can_be_rendered(): void
    {
        $response = $this->get('/access-gate');
        $response->assertStatus(200);
        $response->assertSee('Gerbang Akses Dashboard');
        $response->assertSee('Apotek Keluarga', false);
    }

    /**
     * Verifikasi gagal bila password salah.
     */
    public function test_client_cannot_access_with_wrong_password(): void
    {
        $response = $this->post('/access-gate', [
            'password' => 'passwordsalah123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertFalse(session('client_authenticated', false));
    }

    /**
     * Verifikasi berhasil dengan password yang tersimpan di database.
     */
    public function test_client_can_access_with_correct_database_password(): void
    {
        $response = $this->post('/access-gate', [
            'password' => 'keluarga2026',
        ]);

        $response->assertRedirect(route('client.dashboard'));
        $response->assertSessionHas('client_authenticated', true);

        // Lanjutkan akses ke dashboard dengan session tersebut
        $dashboardResponse = $this->withSession(['client_authenticated' => true])->get('/');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Dashboard Real-Time Monitoring');
        $dashboardResponse->assertSee('Outstanding Task');
    }

    /**
     * Klien dapat mengunci sesi kembali.
     */
    public function test_client_can_lock_session(): void
    {
        $response = $this->withSession(['client_authenticated' => true])->post('/access-gate/lock');
        $response->assertRedirect(route('client.gate'));
        $response->assertSessionMissing('client_authenticated');
    }

    /**
     * Klien dapat mengekspor laporan monitoring CSV.
     */
    public function test_client_can_export_monitoring_csv(): void
    {
        $response = $this->withSession(['client_authenticated' => true])->get('/export');
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    /**
     * Admin dapat login dengan username dan password.
     */
    public function test_admin_can_login_with_valid_credentials(): void
    {
        $response = $this->post(route('admin.login.submit'), [
            'login' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertRedirect(route('admin.tasks.index'));
        $this->assertAuthenticated();
    }

    /**
     * Admin dapat membuat task baru.
     */
    public function test_admin_can_create_new_task(): void
    {
        $admin = User::where('username', 'admin')->first();

        $response = $this->actingAs($admin)->post(route('admin.tasks.store'), [
            'title' => 'Integrasi Sensor Suhu Kulkas Vaksin Farmasi',
            'module' => 'Inventori & Cold Chain',
            'milestone' => 'Sprint 4',
            'status' => 'in_progress',
            'priority' => 'high',
            'pic_name' => 'Fauzi IoT',
            'progress_percent' => 50,
            'due_date' => now()->addDays(10)->toDateString(),
            'description' => 'Monitoring suhu otomatis 2-8 derajat celcius.',
        ]);

        $response->assertRedirect(route('admin.tasks.index'));
        $this->assertDatabaseHas('tasks', [
            'title' => 'Integrasi Sensor Suhu Kulkas Vaksin Farmasi',
            'module' => 'Inventori & Cold Chain',
        ]);
    }

    /**
     * Admin dapat mengubah status task cepat dan mencatat blocker.
     */
    public function test_admin_can_quick_update_task_status_and_blocker(): void
    {
        $admin = User::where('username', 'admin')->first();
        
        $task = Task::create([
            'title' => 'Integrasi Payment Gateway QRIS',
            'module' => 'POS & Kasir Farmasi',
            'status' => 'in_progress',
            'priority' => 'urgent',
            'pic_name' => 'Sarah',
            'progress_percent' => 70,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.tasks.quick-status', $task), [
            'status' => 'blocked',
            'progress_percent' => 70,
            'blocker_notes' => 'Menunggu verifikasi Merchant ID dari Bank BCA.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'blocked',
            'blocker_notes' => 'Menunggu verifikasi Merchant ID dari Bank BCA.',
        ]);
    }

    /**
     * Admin dapat mengubah password klien di database dan password baru langsung berlaku.
     */
    public function test_admin_can_update_client_password_and_new_password_is_effective(): void
    {
        $admin = User::where('username', 'admin')->first();

        $updateResponse = $this->actingAs($admin)->put(route('admin.settings.update'), [
            'project_name' => 'Digitalisasi Apotek Keluarga v2',
            'client_password' => 'apotek_rahasia_2026',
            'project_description' => 'Deskripsi Baru',
            'start_date' => now()->toDateString(),
            'target_completion_date' => now()->addDays(30)->toDateString(),
        ]);

        $updateResponse->assertRedirect(route('admin.settings.index'));
        $this->assertDatabaseHas('project_settings', [
            'id' => 1,
            'client_password' => 'apotek_rahasia_2026',
        ]);

        // Verifikasi password lama tidak lagi bisa digunakan
        $oldPassAttempt = $this->post('/access-gate', ['password' => 'keluarga2026']);
        $oldPassAttempt->assertSessionHasErrors('password');

        // Verifikasi password baru langsung berhasil
        $newPassAttempt = $this->post('/access-gate', ['password' => 'apotek_rahasia_2026']);
        $newPassAttempt->assertRedirect(route('client.dashboard'));
        $this->assertTrue(session('client_authenticated', false));
    }

    /**
     * Dashboard klien menampilkan Roadmap Timeline 7 Fase dan widget Rollout 30 Outlet telah dihapus.
     */
    public function test_client_dashboard_renders_roadmap_phases_and_rollout_tasks_in_list(): void
    {
        $this->seed();

        $response = $this->withSession(['client_authenticated' => true])->get('/');
        
        $response->assertSee('Roadmap Timeline Implementasi Odoo 18 Enterprise');
        // Bagian widget status rollout 30 outlet telah dihapus dari dashboard
        $response->assertDontSee('Status Rollout 30 Outlet Apotek Keluarga');
        $response->assertViewHas('roadmapPhases');
        $response->assertViewMissing('rolloutWaves');

        // Task rollout individual di Fase 4 tetap terdata lengkap di database
        $rolloutTasks = Task::where('milestone', 'Fase 4: Rollout 30 Outlet (100% Live)')->get();
        $this->assertCount(30, $rolloutTasks);
        $this->assertFalse(Task::where('title', 'like', '%Apotek Keluarga 10%')->exists());
        $this->assertTrue(Task::where('title', 'like', '%Apotek Keluarga 01%')->exists());
        $this->assertTrue(Task::where('title', 'like', '%Apotek Keluarga 31%')->exists());

        // Verifikasi tidak ada task yang overdue
        $response->assertViewHas('overdueCount', 0);
        $this->assertEquals(0, Task::where('status', '!=', 'completed')->get()->filter->is_overdue->count());
    }

    /**
     * Admin dapat menyaring daftar task berdasarkan Milestone / Fase Timeline.
     */
    public function test_admin_can_filter_tasks_by_milestone_timeline_phase(): void
    {
        $admin = User::where('username', 'admin')->first();

        Task::create([
            'title' => 'Rollout Wave 1 Pilot',
            'module' => 'Rollout & Deployment',
            'milestone' => 'Fase 4: Rollout 30 Outlet (100% Live)',
            'status' => 'completed',
            'priority' => 'urgent',
            'pic_name' => 'Tim Deployment',
            'progress_percent' => 100,
        ]);

        Task::create([
            'title' => 'Penyusunan Format COA Apotek',
            'module' => 'Accounting',
            'milestone' => 'Fase 5: Accounting & Financial Refinement',
            'status' => 'completed',
            'priority' => 'high',
            'pic_name' => 'Indra Finance',
            'progress_percent' => 100,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.tasks.index', [
            'milestone' => 'Fase 4: Rollout 30 Outlet (100% Live)',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Rollout Wave 1 Pilot');
        $response->assertDontSee('Penyusunan Format COA Apotek');
    }

    /**
     * Admin dapat membuat task baru dengan start_date dan due_date sesuai timeline.
     */
    public function test_admin_can_create_and_update_task_with_start_date(): void
    {
        $admin = User::where('username', 'admin')->first();

        // Create Task
        $response = $this->actingAs($admin)->post(route('admin.tasks.store'), [
            'title' => 'Task Uji Coba Timeline Baru',
            'module' => 'Testing Modul',
            'milestone' => 'Fase 1: Planning & Setup Workspace',
            'status' => 'in_progress',
            'priority' => 'high',
            'pic_name' => 'QA Engineer',
            'progress_percent' => 50,
            'start_date' => '2026-01-05',
            'due_date' => '2026-01-15',
            'description' => 'Penjelasan task uji coba',
        ]);

        $response->assertRedirect(route('admin.tasks.index'));
        $task = Task::where('title', 'Task Uji Coba Timeline Baru')->first();
        $this->assertNotNull($task);
        $this->assertEquals('2026-01-05', $task->start_date->format('Y-m-d'));
        $this->assertEquals('2026-01-15', $task->due_date->format('Y-m-d'));

        // Update Task
        $updateResponse = $this->actingAs($admin)->put(route('admin.tasks.update', $task), [
            'title' => 'Task Uji Coba Timeline Diperbarui',
            'module' => 'Testing Modul',
            'milestone' => 'Fase 1: Planning & Setup Workspace',
            'status' => 'completed',
            'priority' => 'urgent',
            'pic_name' => 'QA Lead',
            'progress_percent' => 100,
            'start_date' => '2026-01-05',
            'due_date' => '2026-01-20',
        ]);

        $updateResponse->assertRedirect(route('admin.tasks.index'));
        $task->refresh();
        $this->assertEquals('Task Uji Coba Timeline Diperbarui', $task->title);
        $this->assertEquals('2026-01-05', $task->start_date->format('Y-m-d'));
        $this->assertEquals('2026-01-20', $task->due_date->format('Y-m-d'));
        $this->assertEquals('completed', $task->status);
    }

    /**
     * Ekspor CSV menyertakan kolom Tanggal Mulai.
     */
    public function test_export_csv_includes_start_date_column(): void
    {
        $response = $this->withSession(['client_authenticated' => true])->get(route('client.export'));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->streamedContent(), 'Tanggal Mulai'));
    }
}

