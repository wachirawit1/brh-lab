<?php

namespace Tests\Feature;

use App\Support\SessionSecurity;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    public function test_idle_timeout_boundaries_and_invalid_timestamps(): void
    {
        $this->travelTo(now()->startOfSecond());
        config(['session.idle_timeout' => 60]);
        $this->assertFalse(SessionSecurity::expired(now()->subMinutes(59)));
        $this->assertTrue(SessionSecurity::expired(now()->subMinutes(60)));
        $this->assertTrue(SessionSecurity::expired(now()->subMinutes(61)));
        $this->assertTrue(SessionSecurity::expired(null));
        $this->assertTrue(SessionSecurity::expired('invalid timestamp'));
        $this->assertTrue(SessionSecurity::expired(now()->addMinutes(1)));
    }

    public function test_polling_expires_session_and_does_not_refresh_activity(): void
    {
        $last = now()->subMinutes(5);
        $this->withSession(['user' => ['logged_in' => true, 'last_activity' => $last]])
            ->getJson('/check-session')->assertOk()->assertJsonPath('alive', true);
        $this->assertTrue(session('user.last_activity')->equalTo($last));
        $this->withSession(['user' => ['logged_in' => true, 'last_activity' => now()->subMinutes(61)]])
            ->getJson('/check-session')->assertUnauthorized()->assertSessionMissing('user');
    }

    public function test_notify_is_disabled_before_any_database_or_network_access(): void
    {
        config(['services.telegram.notify_enabled' => false]);
        $this->withoutMiddleware()->postJson('/notify', [])->assertStatus(503);
    }

    public function test_login_has_private_response_headers(): void
    {
        $response = $this->get('/login');
        $response->assertOk()->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_notify_excludes_unapproved_and_inactive_subscribers(): void
    {
        \Illuminate\Support\Facades\Schema::create('telegram_subscribers', function ($table) {
            $table->id();
            $table->string('chat_id');
            $table->boolean('allowed');
            $table->boolean('is_active');
        });
        DB::table('telegram_subscribers')->insert([
            ['chat_id' => 'test-a', 'allowed' => false, 'is_active' => true],
            ['chat_id' => 'test-b', 'allowed' => true, 'is_active' => false],
            ['chat_id' => 'test-c', 'allowed' => false, 'is_active' => false],
        ]);
        config(['services.telegram.notify_enabled' => true]);
        $this->withoutMiddleware()->postJson('/notify', ['hn' => 'TEST001', 'action' => 'test'])
            ->assertOk()->assertJsonPath('data.total_subscribers', 0);
    }

    private function clinicalSchema(): void
    {
        foreach ([
            '2026_08_19_000001_create_patient_amr_organisms_table',
            '2026_08_20_000001_create_amr_organisms_master_table',
            '2026_08_21_000001_normalize_patient_amr_organisms',
            '2026_08_21_000002_drop_legacy_amr_columns_from_patient_amr_organisms',
            '2026_09_03_000001_create_system_audit_logs_table',
        ] as $migration) {
            $this->artisan('migrate', ['--path' => "database/migrations/$migration.php"])->assertExitCode(0);
        }
    }

    public function test_each_clinical_edit_keeps_before_and_after_even_when_cleared(): void
    {
        $this->clinicalSchema();
        $this->withoutMiddleware()->withSession(['user' => ['username' => 'test.user', 'fullname' => 'Test User']]);
        foreach ([['crab'], ['mrsa'], []] as $codes) {
            $this->postJson('/amr/organisms', ['hn' => 'TEST001', 'regist_flag' => '1', 'organisms' => $codes])->assertOk();
        }
        $events = DB::table('system_audit_logs')->orderBy('id')->get();
        $this->assertCount(3, $events);
        $this->assertSame(['crab'], json_decode($events[1]->old_values, true)['organism_codes']);
        $this->assertSame(['mrsa'], json_decode($events[2]->old_values, true)['organism_codes']);
        $this->assertSame([], json_decode($events[2]->new_values, true)['organism_codes']);
        $this->getJson('/settings/audit-logs')->assertOk()->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.previous_organisms', ['MRSA']);
    }

    public function test_audit_failure_rolls_back_clinical_write(): void
    {
        $this->clinicalSchema();
        \Illuminate\Support\Facades\Schema::drop('system_audit_logs');
        $this->withoutMiddleware()->postJson('/amr/organisms', [
            'hn' => 'TEST002', 'regist_flag' => '1', 'organisms' => ['crab'],
        ])->assertStatus(500);
        $this->assertDatabaseCount('patient_amr_organisms', 0);
        $this->assertDatabaseCount('patient_amr_organism_selections', 0);
    }

    public function test_audit_failure_preserves_existing_organisms(): void
    {
        $this->clinicalSchema();
        $this->withoutMiddleware()->postJson('/amr/organisms', [
            'hn' => 'TEST003', 'regist_flag' => '1', 'organisms' => ['crab'],
        ])->assertOk();
        \Illuminate\Support\Facades\Schema::drop('system_audit_logs');
        $this->postJson('/amr/organisms', [
            'hn' => 'TEST003', 'regist_flag' => '1', 'organisms' => [],
        ])->assertStatus(500);
        $this->assertSame(['crab'], \App\Models\PatientAmrOrganism::firstOrFail()
            ->selectedOrganisms->pluck('code')->all());
    }
}
