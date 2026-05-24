<?php

namespace Tests\Unit;

use App\Http\Controllers\CenterNotificationController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class CenterNotificationRecipientSelectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_only_select_supervised_users_when_supervision_is_configured(): void
    {
        $admin = User::factory()->admin('TZ0001')->create();
        $supervisedUser = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
            'name' => 'Supervised User',
        ]);
        $otherUser = User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
            'name' => 'Other User',
        ]);

        $admin->supervisedUsers()->sync([$supervisedUser->id]);

        $recipients = $this->invokeControllerMethod('availableManagedUsersFor', $admin);

        $this->assertCount(1, $recipients);
        $this->assertSame([$supervisedUser->id], $recipients->pluck('id')->all());
        $this->assertNotContains($otherUser->id, $recipients->pluck('id')->all());
    }

    public function test_standard_user_can_select_linked_admins_and_system_administrators(): void
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
        $otherAdmin = User::factory()->admin('TZ0002')->create([
            'name' => 'Other Admin',
        ]);

        $centerAdmin->supervisedUsers()->sync([$user->id]);

        $recipients = $this->invokeControllerMethod('availableAdminRecipientsFor', $user);

        $this->assertContains($centerAdmin->id, $recipients->pluck('id')->all());
        $this->assertContains($officialAdmin->id, $recipients->pluck('id')->all());
        $this->assertNotContains($otherAdmin->id, $recipients->pluck('id')->all());
    }

    public function test_official_admin_can_filter_admins_and_cluster_users(): void
    {
        $officialAdmin = User::factory()->officialAdmin()->create();
        $adminA = User::factory()->admin('TZ0001')->create([
            'name' => 'Admin A',
        ]);
        $adminB = User::factory()->admin('TZ0002')->create([
            'name' => 'Admin B',
        ]);
        User::factory()->create([
            'center_id' => 'TZ0001',
            'cluster_name' => 'Cluster A',
            'name' => 'Cluster A User',
        ]);
        User::factory()->create([
            'center_id' => 'TZ0002',
            'cluster_name' => 'Cluster B',
            'name' => 'Cluster B User',
        ]);

        $adminRecipients = $this->invokeControllerMethod('availableOfficialAdminAdminRecipientsFor', $officialAdmin);
        $clusterOptions = $this->invokeControllerMethod('availableClusterRecipientOptions');
        $clusterARecipients = $this->invokeControllerMethod('availableSystemUsersForOfficialAdmin', 'Cluster A');

        $this->assertEqualsCanonicalizing([$adminA->id, $adminB->id], $adminRecipients->pluck('id')->all());
        $this->assertSame(['Cluster A', 'Cluster B'], $clusterOptions->all());
        $this->assertCount(1, $clusterARecipients);
        $this->assertSame(['Cluster A'], $clusterARecipients->pluck('cluster_name')->unique()->values()->all());
    }

    protected function invokeControllerMethod(string $methodName, ...$arguments)
    {
        $controller = app(CenterNotificationController::class);
        $method = new ReflectionMethod($controller, $methodName);
        $method->setAccessible(true);

        return $method->invoke($controller, ...$arguments);
    }
}
