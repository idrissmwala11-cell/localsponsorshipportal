<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CenterReportController;
use App\Http\Controllers\Admin\OfficialAdminController;
use App\Http\Controllers\DashboardController;
use App\Models\Center;
use App\Models\OtpCode;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_user_is_redirected_to_pending_approval_after_successful_otp_when_not_approved(): void
    {
        $user = User::factory()->unapproved()->create();

        OtpCode::create([
            'user_id' => $user->id,
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('otp.verify.submit'), ['code' => '123456']);

        $response->assertRedirect(route('approval.pending', absolute: false));
        $response->assertSessionHas('otp_verified', true);
    }

    public function test_official_admin_can_login_without_project_name(): void
    {
        $user = User::factory()->officialAdmin()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'role' => User::ROLE_OFFICIAL_ADMIN,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('otp.verify', absolute: false));
    }

    public function test_regular_admin_is_redirected_to_setup_after_successful_otp_when_not_onboarded(): void
    {
        $admin = User::factory()->admin('TZ0001')->create([
            'admin_onboarded_at' => null,
        ]);

        OtpCode::create([
            'user_id' => $admin->id,
            'code' => '123456',
            'expires_at' => now()->addMinutes(5),
            'is_used' => false,
        ]);

        $response = $this
            ->actingAs($admin)
            ->post(route('otp.verify.submit'), ['code' => '123456']);

        $response->assertRedirect(route('admin.setup.show', absolute: false));
    }

    public function test_standard_user_dashboard_hides_other_user_accounts(): void
    {
        $user = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        $this->actingAs($user);

        $response = app(DashboardController::class)->index();
        $data = $response->getData();

        $this->assertSame('Only your account is visible here', $data['usersMetricCopy']);
        $this->assertSame(1, $data['usersCount']);
    }

    public function test_standard_user_cannot_view_another_users_participant_in_same_center(): void
    {
        $owner = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        $viewer = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        $participant = Participant::create([
            'center_id' => 'TZ0001',
            'created_by_user_id' => $owner->id,
            'local_participant_number' => '001',
            'local_participant_id' => 'TZ0001001',
            'account_name' => 'Project Account',
            'preferred_name' => 'Participant One',
            'gender' => 'Female',
            'participant_status' => 'Active',
        ]);

        $this->withoutExceptionHandling();
        $this->actingAs($viewer)->withSession(['otp_verified' => true]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Unauthorized access.');

        $this->get(route('participants.show', $participant));
    }

    public function test_standard_user_can_view_their_own_participant_even_if_account_center_is_missing(): void
    {
        $owner = User::factory()->create([
            'center_id' => null,
            'cluster_name' => 'Cluster A',
        ]);

        $participant = Participant::create([
            'center_id' => 'TZ0001',
            'created_by_user_id' => $owner->id,
            'local_participant_number' => '002',
            'local_participant_id' => 'TZ0001002',
            'account_name' => 'Project Account',
            'preferred_name' => 'Participant Two',
            'gender' => 'Male',
            'participant_status' => 'Active',
        ]);

        $response = $this->actingAs($owner)
            ->withSession(['otp_verified' => true])
            ->get(route('participants.show', $participant));

        $response->assertOk();
        $response->assertSee('Participant Two');
    }

    public function test_standard_user_can_choose_center_admin_and_system_administrator_in_chat(): void
    {
        $user = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        $centerAdmin = User::factory()->admin('TZ0001')->create([
            'name' => 'Center Admin',
        ]);

        $officialAdmin = User::factory()->officialAdmin()->create([
            'name' => 'System Administrator',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['otp_verified' => true])
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Center Admin');
        $response->assertSee('System Administrator');
        $response->assertSee('Select Admin or System Administrator');
    }

    public function test_only_official_admin_can_approve_accounts(): void
    {
        $officialAdmin = User::factory()->officialAdmin()->create();
        $admin = User::factory()->admin('TZ0001')->create();
        $pendingUser = User::factory()->unapproved()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        $forbiddenRequest = Request::create(route('admin.users.approve', $pendingUser, false), 'POST');
        $forbiddenRequest->setUserResolver(fn () => $admin);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Only the system administrator can approve accounts.');

        app(UserManagementController::class)->approve($forbiddenRequest, $pendingUser);
    }

    public function test_official_admin_can_approve_accounts(): void
    {
        $officialAdmin = User::factory()->officialAdmin()->create();
        $pendingUser = User::factory()->unapproved()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        $approvedRequest = Request::create(route('admin.users.approve', $pendingUser, false), 'POST');
        $approvedRequest->setUserResolver(fn () => $officialAdmin);

        $approvedResponse = app(UserManagementController::class)->approve($approvedRequest, $pendingUser);

        $this->assertSame(route('admin.users.index'), $approvedResponse->getTargetUrl());
        $this->assertNotNull($pendingUser->fresh()->approved_at);
    }

    public function test_admin_can_view_user_detail_page_in_their_scope(): void
    {
        $admin = User::factory()->admin('TZ0001')->create();
        $managedUser = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        $request = Request::create(route('admin.users.show', $managedUser, false), 'GET');
        $request->setUserResolver(fn () => $admin);

        $response = app(UserManagementController::class)->show($request, $managedUser);
        $data = $response->getData();

        $this->assertSame('admin.users.show', $response->name());
        $this->assertSame($managedUser->id, $data['managedUser']->id);
        $this->assertSame($managedUser->email, $data['managedUser']->email);
    }

    public function test_standard_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();

        $this->withoutExceptionHandling();
        $this->actingAs($user)->withSession(['otp_verified' => true]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Admin access only.');

        $this->get(route('admin.index'));
    }

    public function test_unapproved_admin_is_redirected_to_pending_approval_before_admin_panel(): void
    {
        $admin = User::factory()->admin('TZ0001')->unapproved()->create();

        $response = $this
            ->actingAs($admin)
            ->withSession(['otp_verified' => true])
            ->get(route('admin.index'));

        $response->assertRedirect(route('approval.pending', absolute: false));
    }

    public function test_admin_dashboard_can_load_for_admin(): void
    {
        Center::query()->create([
            'center_id' => 'TZ0001',
            'center_name' => 'Center One',
            'cluster_name' => 'Cluster A',
        ]);

        $admin = User::factory()->admin('TZ0001')->create();
        $managedUser = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);
        $pendingManagedUser = User::factory()->unapproved()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);
        $admin->managedCenters()->sync(['TZ0001']);
        $admin->managedClusterAssignments()->create(['cluster_name' => 'Cluster A']);

        Participant::create([
            'center_id' => 'TZ0001',
            'created_by_user_id' => $managedUser->id,
            'local_participant_number' => '001',
            'local_participant_id' => 'TZ0001001',
            'account_name' => 'Project Account',
            'preferred_name' => 'Participant One',
            'gender' => 'Female',
            'participant_status' => 'Active',
        ]);

        $this->actingAs($admin);

        $response = app(AdminDashboardController::class)->index();
        $data = $response->getData();

        $this->assertSame('admin.index', $response->name());
        $this->assertTrue($data['isSupervisionDashboard']);
        $this->assertSame(2, $data['usersCount']);
        $this->assertSame(1, $data['approvedUsersCount']);
        $this->assertSame(1, $data['pendingUsersCount']);
        $this->assertSame(1, $data['projectCount']);
        $this->assertSame('Cluster A', $data['managedClustersLabel']);
    }

    public function test_official_admin_dashboard_focuses_on_admins_and_users(): void
    {
        Center::query()->create([
            'center_id' => 'TZ0001',
            'center_name' => 'Center One',
            'cluster_name' => 'Cluster A',
        ]);

        $officialAdmin = User::factory()->officialAdmin()->create();
        User::factory()->admin('TZ0001')->create();
        User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        $this->actingAs($officialAdmin);

        $response = app(AdminDashboardController::class)->index();
        $data = $response->getData();

        $this->assertTrue($data['isOfficialAdminDashboard']);
        $this->assertSame(2, $data['adminsCount']);
        $this->assertSame(1, $data['usersCount']);
        $this->assertSame(0, $data['participantsCount']);
        $this->assertSame(0, $data['sponsorshipsCount']);
    }

    public function test_admin_setup_can_save_managed_clusters_without_project_name(): void
    {
        Center::query()->create([
            'center_id' => 'TZ0001',
            'center_name' => 'Center One',
            'cluster_name' => 'Cluster A',
        ]);
        Center::query()->create([
            'center_id' => 'TZ0002',
            'center_name' => 'Center Two',
            'cluster_name' => 'Cluster A',
        ]);

        $admin = User::factory()->admin('TZ0001')->create([
            'admin_onboarded_at' => null,
        ]);
        $firstUser = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
            'project_name' => 'moravian',
        ]);
        $secondUser = User::factory()->create([
            'center_id' => 'TZ0002',
            'cluster_name' => 'Cluster A',
            'project_name' => 'fpct',
        ]);

        $response = $this
            ->actingAs($admin)
            ->withSession(['otp_verified' => true])
            ->post(route('admin.setup.store'), [
                'managed_cluster_names' => ['Cluster A'],
            ]);

        $response->assertRedirect(route('admin.index', absolute: false));
        $this->assertNotNull($admin->fresh()->admin_onboarded_at);
        $this->assertEqualsCanonicalizing(
            ['TZ0001', 'TZ0002'],
            $admin->fresh()->managedCenters()->pluck('centers.center_id')->all()
        );
        $this->assertEqualsCanonicalizing(
            [$firstUser->id, $secondUser->id],
            $admin->fresh()->supervisedUsers()->pluck('users.id')->all()
        );
    }

    public function test_reports_index_for_standard_user_is_limited_to_own_account_data(): void
    {
        $user = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);

        Participant::create([
            'center_id' => 'TZ0001',
            'created_by_user_id' => $user->id,
            'local_participant_number' => '001',
            'local_participant_id' => 'TZ0001001',
            'account_name' => 'My Project',
            'preferred_name' => 'Visible Participant',
            'gender' => 'Female',
            'participant_status' => 'Active',
        ]);

        $request = Request::create(route('reports.index', absolute: false), 'GET');
        $request->setUserResolver(fn () => $user);

        $response = app(CenterReportController::class)->index($request);
        $data = $response->getData();

        $this->assertSame('admin.reports.index', $response->name());
        $this->assertSame(1, $data['usersCount']);
        $this->assertSame('User: only your own account data and the records you uploaded are visible here.', $data['scopeLabel']);
        $this->assertSame(1, $data['participantsCount']);
    }

    public function test_admin_reports_can_be_filtered_to_one_center_id_only(): void
    {
        Center::query()->create([
            'center_id' => 'TZ0001',
            'center_name' => 'Center One',
            'cluster_name' => 'Cluster A',
        ]);
        Center::query()->create([
            'center_id' => 'TZ0002',
            'center_name' => 'Center Two',
            'cluster_name' => 'Cluster A',
        ]);

        $admin = User::factory()->admin('TZ0001')->create();
        $admin->managedCenters()->sync(['TZ0001', 'TZ0002']);
        $admin->managedClusterAssignments()->create(['cluster_name' => 'Cluster A']);

        $firstUser = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
        ]);
        $secondUser = User::factory()->create([
            'center_id' => 'TZ0002',
            'cluster_name' => 'Cluster A',
        ]);

        Participant::create([
            'center_id' => 'TZ0001',
            'created_by_user_id' => $firstUser->id,
            'local_participant_number' => '001',
            'local_participant_id' => 'TZ0001001',
            'account_name' => 'Center One Project',
            'preferred_name' => 'Center One Participant',
            'gender' => 'Female',
            'participant_status' => 'Active',
        ]);

        Participant::create([
            'center_id' => 'TZ0002',
            'created_by_user_id' => $secondUser->id,
            'local_participant_number' => '002',
            'local_participant_id' => 'TZ0002002',
            'account_name' => 'Center Two Project',
            'preferred_name' => 'Center Two Participant',
            'gender' => 'Male',
            'participant_status' => 'Active',
        ]);

        $request = Request::create(route('reports.index', ['center_id' => 'TZ0001'], false), 'GET', [
            'center_id' => 'TZ0001',
        ]);
        $request->setUserResolver(fn () => $admin);

        $response = app(CenterReportController::class)->index($request);
        $data = $response->getData();
        $rows = collect($data['reportRows']->items());

        $this->assertSame('TZ0001', $data['centerId']);
        $this->assertSame('TZ0001', $data['selectedCenterId']);
        $this->assertSame(1, $data['participantsCount']);
        $this->assertCount(1, $rows);
        $this->assertSame('TZ0001001', $rows->first()['local_participant_id']);
    }

    public function test_report_excel_export_uses_selected_report_columns_and_period_only(): void
    {
        $user = User::factory()->create([
            'center_id' => 'TZ0001',
        ]);

        $recentParticipant = Participant::create([
            'center_id' => 'TZ0001',
            'created_by_user_id' => $user->id,
            'local_participant_number' => '001',
            'local_participant_id' => 'TZ0001001',
            'account_name' => 'Visible Project',
            'preferred_name' => 'Visible Participant',
            'gender' => 'Female',
            'participant_status' => 'Active',
            'physical_address' => 'Should Not Export',
        ]);

        $oldParticipant = Participant::create([
            'center_id' => 'TZ0001',
            'created_by_user_id' => $user->id,
            'local_participant_number' => '002',
            'local_participant_id' => 'TZ0001002',
            'account_name' => 'Old Project',
            'preferred_name' => 'Old Participant',
            'gender' => 'Male',
            'participant_status' => 'Active',
            'physical_address' => 'Old Address',
        ]);

        $recentParticipant->timestamps = false;
        $recentParticipant->forceFill([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ])->save();

        $oldParticipant->timestamps = false;
        $oldParticipant->forceFill([
            'created_at' => now()->subMonths(4),
            'updated_at' => now()->subMonths(4),
        ])->save();

        $response = $this
            ->actingAs($user)
            ->withSession(['otp_verified' => true])
            ->get(route('reports.export', [
                'type' => 'report',
                'module' => 'basic_information',
                'period' => 'monthly',
            ]));

        $response->assertOk();

        $excel = $response->streamedContent();

        $response->assertHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
        $this->assertStringContainsString('<th colspan="40">Basic Information</th>', $excel);
        foreach (['Local Number', 'Participant ID', 'Project Name', 'Full Name', 'Gender', 'Birthdate', 'Status', 'Date'] as $header) {
            $this->assertStringContainsString("<th>{$header}</th>", $excel);
        }
        $this->assertStringContainsString('Visible Project', $excel);
        $this->assertStringNotContainsString('Should Not Export', $excel);
        $this->assertStringNotContainsString('Physical Address', $excel);
        $this->assertStringNotContainsString('Old Project', $excel);
    }

    public function test_reports_index_for_official_admin_uses_global_scope(): void
    {
        Center::query()->create([
            'center_id' => 'TZ0001',
            'center_name' => 'Center One',
        ]);
        Center::query()->create([
            'center_id' => 'TZ0002',
            'center_name' => 'Center Two',
        ]);

        $officialAdmin = User::factory()->officialAdmin()->create();
        User::factory()->admin('TZ0001')->create();
        User::factory()->create(['center_id' => 'TZ0002', 'cluster_name' => 'Cluster B']);

        $request = Request::create(route('reports.index', absolute: false), 'GET');
        $request->setUserResolver(fn () => $officialAdmin);

        $response = app(CenterReportController::class)->index($request);
        $data = $response->getData();

        $this->assertSame('ALL', $data['centerId']);
        $this->assertSame(2, $data['usersCount']);
        $this->assertSame(1, $data['adminCount']);
        $this->assertSame('Official Admin: report only on admins and users across all centers.', $data['scopeLabel']);
        $this->assertSame('Users Report', $data['reportModule']['label']);
    }

    public function test_admin_cannot_access_official_admin_oversight(): void
    {
        $admin = User::factory()->admin('TZ0001')->create();

        $this->withoutExceptionHandling();
        $this->actingAs($admin)->withSession(['otp_verified' => true]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Official admin access only.');

        $this->get(route('admin.official.index'));
    }

    public function test_official_admin_oversight_dashboard_can_load(): void
    {
        Center::query()->create([
            'center_id' => 'TZ0001',
            'center_name' => 'Center One',
        ]);
        Center::query()->create([
            'center_id' => 'TZ0002',
            'center_name' => 'Center Two',
        ]);

        $officialAdmin = User::factory()->officialAdmin()->create();
        $managedAdmin = User::factory()->admin('TZ0001')->create();
        $managedAdmin->managedCenters()->sync(['TZ0001', 'TZ0002']);
        User::factory()->create(['center_id' => 'TZ0002', 'cluster_name' => 'Cluster B']);

        $this->actingAs($officialAdmin);

        $response = app(OfficialAdminController::class)->index();
        $data = $response->getData();

        $this->assertSame('admin.official.index', $response->name());
        $this->assertSame(2, $data['centersCount']);
        $this->assertSame(3, $data['usersCount']);
        $this->assertSame(1, $data['officialAdminsCount']);
        $this->assertSame(1, $data['adminsCount']);
        $this->assertCount(2, $data['centerSummaries']);
        $this->assertSame($managedAdmin->id, $data['recentAdmins']->firstWhere('id', $managedAdmin->id)?->id);
    }
}
