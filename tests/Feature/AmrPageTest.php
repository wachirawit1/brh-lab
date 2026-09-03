<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class AmrPageTest extends TestCase
{
    public function test_release_version_is_consistent_across_application_pages(): void
    {
        $this->assertSame('1.2.0', config('app.version'));
        $this->assertSame('03/09/2026', config('app.release_date'));

        $loginHtml = view('auth.login', ['errors' => new \Illuminate\Support\ViewErrorBag])->render();
        $this->assertStringContainsString('เวอร์ชัน 1.2.0', $loginHtml);
        $this->assertStringContainsString('ปล่อยวันที่ 03/09/2026', $loginHtml);
    }

    public function test_guest_is_redirected_from_amr_page(): void
    {
        $response = $this->get(route('amr.index'));

        $response->assertRedirect(route('loginForm'));
    }

    public function test_amr_view_renders_empty_state(): void
    {
        $patients = new LengthAwarePaginator([], 0, 50);
        $wards = collect();
        $filters = [
            'admit_date' => '',
            'search' => '',
            'ward' => '',
            'm' => null,
            'rm' => null,
        ];

        $html = view('amr.index', compact('patients', 'wards', 'filters'))->render();

        $this->assertStringContainsString('name="admit_date"', $html);
        $this->assertStringContainsString('name="search"', $html);
        $this->assertStringContainsString('name="ward"', $html);
    }

    public function test_amr_view_does_not_show_empty_guidance_when_loading_fails(): void
    {
        $patients = new LengthAwarePaginator([], 0, 50);
        $wards = collect();
        $filters = [
            'search' => '',
            'ward' => '',
            'admit_date' => '',
            'm' => null,
            'rm' => null,
        ];
        $loadError = 'Database unavailable';

        $html = view('amr.index', compact('patients', 'wards', 'filters', 'loadError'))->render();

        $this->assertStringContainsString($loadError, $html);
        $this->assertStringNotContainsString('ไม่พบรายการผู้ป่วย AMR', $html);
    }

    public function test_cbc_preview_splits_pipe_delimited_results(): void
    {
        $html = view('amr.partials.lab-popover', [
            'label' => 'CBC',
            'value' => 'WBC [ 8.5 ] | Hb [ 12.4 ] | Platelet [ 250000 ] |',
            'patientName' => 'Test Patient',
            'patientHn' => '0000001',
            'patientAn' => 'A000001',
            'admitDate' => '19 สิงหาคม 2569',
            'wardName' => 'Test Ward',
        ])->render();

        $this->assertStringContainsString('WBC [ 8.5 ]', $html);
        $this->assertStringContainsString('Hb [ 12.4 ]', $html);
        $this->assertStringContainsString('Platelet [ 250000 ]', $html);
        $this->assertStringContainsString('HN 0000001', $html);
        $this->assertStringContainsString('AN A000001', $html);
        $this->assertStringContainsString('aria-controls', $html);
        $this->assertStringContainsString('role="region"', $html);
    }

    public function test_amr_view_renders_new_date_and_status_fields(): void
    {
        $patient = (object) [
            'hn' => '0000001', 'regist_flag' => '0001', 'ladmit_n' => 'A000001',
            'admit_date' => '25690819', 'name' => 'Test Patient', 'sex' => 'F', 'age' => '42 Y',
            'Weight' => 58.5, 'Height' => 160, 'ward_id' => '101', 'ward_name' => 'Test Ward',
            'M' => 'Y', 'RM' => 'Y', 'cr' => '0.8', 'egfr' => '95',
            'CBC' => 'WBC [ 8.5 ] | Hb [ 12.4 ] |', 'LFT' => 'AST [ 24 ] |',
        ];
        $patients = new LengthAwarePaginator([$patient], 1, 50);
        $wards = collect();
        $filters = [
            'search' => '',
            'admit_date' => '',
            'ward' => '',
            'm' => null,
            'rm' => null,
        ];

        $html = view('amr.index', compact('patients', 'wards', 'filters'))->render();

        $this->assertStringContainsString('2569', $html);
        $this->assertStringContainsString('title="M: มีผลแล็บ"', $html);
        $this->assertStringContainsString('title="RM: มีผลออกใหม่"', $html);
        $this->assertStringNotContainsString('opacity-70', $html);
        $this->assertStringContainsString('2 ค่า', $html);
        $this->assertStringContainsString('AST [ 24 ]', $html);
        $this->assertStringNotContainsString('<details>', $html);
        $this->assertStringContainsString('data-amr-row', $html);
        $this->assertStringContainsString('id="amr-context-menu"', $html);
        $this->assertStringContainsString('contextmenu', $html);
    }

    public function test_amr_view_renders_dynamic_organism_chips_and_egfr_alert(): void
    {
        $patient = (object) [
            'hn' => '365887', 'regist_flag' => '0050', 'ladmit_n' => '6947930',
            'admit_date' => '25690819', 'name' => 'ผู้ป่วยทดสอบ', 'sex' => 'ช', 'age' => '29',
            'Weight' => 45.0, 'Height' => 157.0, 'ward_id' => '012', 'ward_name' => 'อายุรกรรมชาย 1',
            'M' => 'N', 'RM' => null, 'cr' => '0.70', 'egfr' => '24.5',
            'CBC' => '', 'LFT' => '',
        ];
        $patients = new LengthAwarePaginator([$patient], 1, 50);
        $wards = collect();
        $filters = ['search' => '', 'admit_date' => '', 'ward' => '', 'm' => null, 'rm' => null];
        $patientOrganisms = collect([
            '365887_0050' => (object) [
                'selectedOrganisms' => collect([
                    (object) ['name' => 'CRKP', 'full_name' => 'Carbapenem-resistant Klebsiella pneumoniae', 'description' => null, 'severity' => 'critical'],
                    (object) ['name' => 'MRSA', 'full_name' => 'Methicillin-resistant Staphylococcus aureus', 'description' => null, 'severity' => 'high'],
                    (object) ['name' => 'Other', 'full_name' => 'เชื้อดื้อยาอื่น', 'description' => null, 'severity' => 'info'],
                ]),
            ],
        ]);

        $html = view('amr.index', compact('patients', 'wards', 'filters', 'patientOrganisms'))->render();

        $this->assertStringContainsString('CRKP', $html);
        $this->assertStringContainsString('MRSA', $html);
        $this->assertStringContainsString('Other', $html);
        $this->assertStringContainsString('24.5', $html);
    }

    public function test_can_store_and_get_all_fourteen_dynamic_patient_organisms(): void
    {
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_19_000001_create_patient_amr_organisms_table.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_20_000001_create_amr_organisms_master_table.php']);

        \Illuminate\Support\Facades\DB::table('patient_amr_organisms')->insert([
            'hn' => 'legacy-hn',
            'regist_flag' => 'legacy-reg',
            'cre' => 'Y',
            'crab' => 'Y',
            'c_auris' => 'Y',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_21_000001_normalize_patient_amr_organisms.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_21_000002_drop_legacy_amr_columns_from_patient_amr_organisms.php']);

        $legacyCodes = \App\Models\PatientAmrOrganism::with('selectedOrganisms')
            ->where('hn', 'legacy-hn')
            ->firstOrFail()
            ->selectedOrganisms
            ->pluck('code')
            ->sort()
            ->values()
            ->all();
        $this->assertSame(['c_auris', 'crab', 'cre'], $legacyCodes);

        $expectedCodes
 = ['crab', 'crpa', 'crkp', 'crec', 'coro', 'escr', 'mrsa', 'visa_vrsa', 'salmonr', 'drsp', 'vre', 'mrcons', 'strepr', 'other'];
        foreach (['cre', 'crab', 'crpa', 'mrsa', 'vre', 'esbl', 'c_auris'] as $legacyColumn) {
            $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('patient_amr_organisms', $legacyColumn));
        }
        $this->assertDatabaseCount('amr_organisms_master', 17);
        $this->assertSame($expectedCodes, \App\Models\AmrOrganismMaster::active()->pluck('code')->all());

        $response = $this->withoutMiddleware()
            ->postJson(route('amr.organisms.store'), [
                'hn' => '1234567',
                'regist_flag' => '01',
                'ward_id' => '101',
                'organisms' => ['crab', 'crkp', 'visa_vrsa', 'other'],
            ]);

        $response->assertOk()->assertJson(['status' => 'success']);
        $savedRecord = \App\Models\PatientAmrOrganism::with('selectedOrganisms')->where('hn', '1234567')->firstOrFail();
        $this->assertCount(4, $savedRecord->selectedOrganisms);

        $getRes = $this->withoutMiddleware()
            ->getJson(route('amr.organisms.get', ['hn' => '1234567', 'reg_no' => '01']));

        $getRes->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.organisms', ['crab', 'crkp', 'visa_vrsa', 'other'])
            ->assertJsonCount(14, 'data.master_organisms')
            ->assertJsonMissingPath('data.crab')
            ->assertJsonMissingPath('data.cre');

        $this->withoutMiddleware()->postJson(route('amr.organisms.store'), [
            'hn' => '1234567',
            'regist_flag' => '01',
            'ward_id' => '101',
            'organisms' => ['mrsa'],
        ])->assertOk();

        $savedRecord->refresh()->load('selectedOrganisms');
        $this->assertSame(['mrsa'], $savedRecord->selectedOrganisms->pluck('code')->all());
    }

    public function test_active_organisms_can_be_reordered_and_partial_lists_are_rejected(): void
    {
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_19_000001_create_patient_amr_organisms_table.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_20_000001_create_amr_organisms_master_table.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_21_000001_normalize_patient_amr_organisms.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_21_000002_drop_legacy_amr_columns_from_patient_amr_organisms.php']);

        $activeIds = \App\Models\AmrOrganismMaster::active()->pluck('id')->all();
        $reversedIds = array_reverse($activeIds);

        $response = $this->withoutMiddleware()->patchJson(route('settings.organisms.reorder'), [
            'organism_ids' => $reversedIds,
        ]);

        $response->assertOk()->assertJson(['status' => 'success']);
        $this->assertSame($reversedIds, \App\Models\AmrOrganismMaster::active()->pluck('id')->all());

        $this->withoutMiddleware()->patchJson(route('settings.organisms.reorder'), [
            'organism_ids' => array_slice($reversedIds, 0, 3),
        ])->assertUnprocessable()->assertJsonPath('status', 'error');
    }

    public function test_reactivating_an_organism_appends_it_to_the_active_order(): void
    {
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_19_000001_create_patient_amr_organisms_table.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_20_000001_create_amr_organisms_master_table.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_21_000001_normalize_patient_amr_organisms.php']);
        $this->artisan('migrate', ['--path' => 'database/migrations/2026_08_21_000002_drop_legacy_amr_columns_from_patient_amr_organisms.php']);

        $inactive = \App\Models\AmrOrganismMaster::where('is_active', false)->firstOrFail();
        $previousMax = \App\Models\AmrOrganismMaster::where('is_active', true)->max('sort_order');

        $this->withoutMiddleware()
            ->patchJson(route('settings.organisms.toggle', $inactive->id))
            ->assertOk()
            ->assertJson(['status' => 'success', 'is_active' => true]);

        $this->assertSame($previousMax + 1, $inactive->fresh()->sort_order);
    }

    public function test_can_fetch_telegram_qr_data(): void
    {
        $response = $this->withSession(['user' => ['username' => 'testuser', 'logged_in' => true]])
            ->withoutMiddleware()
            ->getJson(route('telegram.qr'));

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'username' => 'testuser',
            ]);
    }

    public function test_amr_layout_exposes_complete_accessible_theme_contract(): void
    {
        session(['user' => ['username' => 'admin.user', 'logged_in' => true, 'role' => 'Admin']]);
        $patients = new LengthAwarePaginator([], 0, 50);
        $wards = collect();
        $filters = ['admit_date' => '', 'search' => '', 'ward' => '', 'm' => null, 'rm' => null];

        $html = view('amr.index', compact('patients', 'wards', 'filters'))->render();

        $this->assertStringContainsString('window.BRHTheme', $html);
        $this->assertStringContainsString("themes: ['light', 'dark', 'high-contrast', 'eye-care']", $html);
        $this->assertStringContainsString('html[data-theme="dark"]', $html);
        $this->assertStringContainsString('html[data-theme="high-contrast"]', $html);
        $this->assertStringContainsString('html[data-theme="eye-care"]', $html);
        $this->assertStringContainsString('--brand-solid:', $html);
        $this->assertStringContainsString('--neutral-solid:', $html);
        $this->assertStringContainsString('.select2-container', $html);
        $this->assertStringContainsString('.flatpickr-calendar', $html);
        $this->assertStringContainsString('.swal2-popup', $html);
        $this->assertStringContainsString('scrollbar-gutter: stable;', $html);
        $this->assertStringContainsString('window.BRHModalScroll', $html);
        $this->assertStringContainsString('html.brh-modal-open', $html);
        $this->assertStringContainsString('html.swal2-shown', $html);
        $this->assertStringContainsString("BRHModalScroll?.set('navbar-settings', open)", $html);
        $this->assertStringContainsString("BRHModalScroll?.set('amr-lab-history', true)", $html);
        $this->assertStringNotContainsString("document.body.classList.add('overflow-hidden')", $html);
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'scrollbarPadding: false'));
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'heightAuto: false'));
        $this->assertStringContainsString('.bg-teal-50', $html);
        $this->assertStringContainsString('.bg-purple-50', $html);
        $this->assertStringContainsString('[class~="bg-red-50/70"]', $html);
        $this->assertStringContainsString('[class~="ring-brand-500/20"]', $html);
        $this->assertStringContainsString('.bg-gray-500', $html);
        $this->assertStringContainsString('กลุ่มเฝ้าระวัง', $html);
        $this->assertStringContainsString('buildAmrOrganismOptions', $html);
        $this->assertStringContainsString('data-settings-header', $html);
        $this->assertStringContainsString('border-b border-gray-200 bg-white', $html);
        $this->assertStringContainsString('data-settings-footer', $html);
        $this->assertStringContainsString('border-t border-gray-200 bg-gray-50', $html);
        $this->assertStringContainsString('data-organism-sort-handle', $html);
        $this->assertStringContainsString('max-w-6xl', $html);
        $this->assertStringContainsString('h-[100dvh]', $html);
        $this->assertStringContainsString('md:grid-cols-[7rem_7rem_minmax(0,1fr)_6rem_6.5rem]', $html);
        $this->assertStringContainsString('class="min-w-0 md:hidden"', $html);
        $this->assertStringNotContainsString('min-w-[720px]', $html);
        $this->assertStringContainsString('@drop.prevent="dropOrganism(org.id)"', $html);
        $this->assertStringContainsString('moveOrganism(org.id, -1)', $html);
        $this->assertStringContainsString('รายการที่ปิดใช้งาน', $html);
        $this->assertStringContainsString('bg-brand-600 hover:bg-brand-700 text-white font-medium', $html);
        $this->assertStringNotContainsString('data-settings-header class="bg-gray-900', $html);

        $legacyIndexTemplate = file_get_contents(resource_path('views/index.blade.php'));
        $adminTemplate = file_get_contents(resource_path('views/admin/management.blade.php'));
        $this->assertIsString($legacyIndexTemplate);
        $this->assertIsString($adminTemplate);
        $this->assertStringContainsString('BRHModalScroll?.set(`dom-modal:${modalID}`', $legacyIndexTemplate);
        $this->assertStringContainsString('window.BRHModalScroll?.set(scrollKey(id), true);', $adminTemplate);
        $this->assertStringContainsString('window.BRHModalScroll?.set(scrollKey(id), false);', $adminTemplate);
        $this->assertStringContainsString("closeModal('editUserRoleModal');", $adminTemplate);
        $amrTemplate = file_get_contents(resource_path('views/amr/index.blade.php'));
        $this->assertIsString($amrTemplate);
        $this->assertStringContainsString('background-color: var(--input-bg) !important;', $amrTemplate);
        $this->assertStringContainsString('border-color: var(--focus-color) !important;', $amrTemplate);
        $this->assertStringNotContainsString('background-color: #ffffff !important;', $amrTemplate);
        $this->assertStringNotContainsString('border-color: #0284c7 !important;', $amrTemplate);

        preg_match_all('/<div[^>]+data-theme-preview="[^"]+"[^>]*>/', $html, $themeCards);
        $this->assertCount(4, $themeCards[0]);
        $this->assertSame(4, substr_count($html, ':aria-pressed='));
        $this->assertSame(4, substr_count($html, '@keydown.enter.prevent='));
        $this->assertSame(4, substr_count($html, '@keydown.space.prevent='));
    }

    public function test_empty_organism_submission_keeps_settings_modal_open_and_is_rejected(): void
    {
        session(['user' => ['username' => 'admin.user', 'logged_in' => true, 'role' => 'Admin']]);
        $patients = new LengthAwarePaginator([], 0, 50);
        $wards = collect();
        $filters = ['admit_date' => '', 'search' => '', 'ward' => '', 'm' => null, 'rm' => null];

        $html = view('amr.index', compact('patients', 'wards', 'filters'))->render();

        $this->assertStringContainsString("organismFormError = 'กรุณากรอกรหัสย่อและชื่อที่แสดงให้ครบถ้วน'", $html);
        $this->assertStringContainsString('id="organism-form-error"', $html);
        $this->assertStringContainsString('@click.away="if (!window.Swal || !Swal.isVisible()) settingsModal = false"', $html);
        $this->assertStringContainsString(':disabled="savingOrganism"', $html);
        $this->assertStringNotContainsString("Swal.fire({ icon: 'warning', title: 'กรุณากรอกรหัสย่อและชื่อเชื้อ'", $html);

        $response = $this->withoutMiddleware()->postJson(route('settings.organisms.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name', 'severity']);
    }

    public function test_user_management_uses_one_reusable_modal_and_releases_scroll_lock(): void
    {
        $users = collect([
            (object) [
                'username' => 'demo.user',
                'tname' => 'นาย',
                'fname' => 'ทดสอบ',
                'lname' => 'ระบบ',
                'position' => 'เจ้าหน้าที่',
                'role_id' => 1,
                'role_name' => 'Admin',
            ],
        ]);
        $roles = collect([(object) ['id' => 1, 'name' => 'Admin']]);
        $activeSessions = collect(['demo.user' => (object) ['username' => 'demo.user']]);

        $recentAuditLogs = collect();
        $html = view('admin.management', compact('users', 'roles', 'activeSessions', 'recentAuditLogs'))->render();

        $this->assertSame(1, substr_count($html, 'id="editUserRoleModal"'));
        $this->assertStringContainsString('data-update-action=', $html);
        $this->assertStringContainsString('บัญชีบุคลากรยังคงอยู่ แต่จะเข้าใช้ระบบนี้ไม่ได้', $html);
        $this->assertStringContainsString('window.BRHModalScroll?.set(scrollKey(id), false);', $html);
        $this->assertStringContainsString("closeModal('editUserRoleModal');", $html);
        $this->assertStringContainsString('กิจกรรมสิทธิ์ล่าสุด', $html);
        $this->assertStringContainsString('ผู้มีสิทธิ์ในระบบ', $html);
        $this->assertStringContainsString('ค้นหาบุคลากรเพื่อเพิ่ม', $html);
        $this->assertStringContainsString("activeScope === 'directory'", $html);
    }

    public function test_regular_user_does_not_see_admin_navigation_or_settings_tabs(): void
    {
        session(['user' => ['username' => 'regular.user', 'logged_in' => true, 'role' => 'User']]);
        $regularHtml = view('layout.navbar')->render();

        $this->assertStringNotContainsString('จัดการผู้ใช้', $regularHtml);
        $this->assertStringNotContainsString('จัดการเชื้อดื้อยา AMR', $regularHtml);
        $this->assertStringNotContainsString('ประวัติการเติมเชื้อ (Logs)', $regularHtml);

        session(['user' => ['username' => 'admin.user', 'logged_in' => true, 'role' => 'Admin']]);
        $adminHtml = view('layout.navbar')->render();

        $this->assertStringContainsString('จัดการผู้ใช้', $adminHtml);
        $this->assertStringContainsString('จัดการเชื้อดื้อยา AMR', $adminHtml);
        $this->assertStringContainsString('ประวัติการเติมเชื้อ (Logs)', $adminHtml);
    }

    public function test_admin_settings_endpoints_require_admin_middleware(): void
    {
        foreach ([
            'admin.management',
            'admin.findUser',
            'settings.organisms.index',
            'settings.organisms.store',
            'settings.organisms.reorder',
            'settings.organisms.toggle',
            'settings.audit.logs',
        ] as $routeName) {
            $route = app('router')->getRoutes()->getByName($routeName);

            $this->assertNotNull($route, "ไม่พบ route {$routeName}");
            $this->assertContains('is.admin', $route->gatherMiddleware(), "route {$routeName} ไม่ได้กันสิทธิ์ Admin");
        }
    }
}
