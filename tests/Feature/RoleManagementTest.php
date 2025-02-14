<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Project;
use App\Models\SeoLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $seoProvider;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->admin = User::factory()->admin()->create();
        $this->seoProvider = User::factory()->seoProvider()->create();
        $this->regularUser = User::factory()->create(); // Will have default 'user' role
    }

    public function test_admin_can_access_user_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));
        $response->assertOk();

        $response = $this->actingAs($this->admin)->get(route('users.create'));
        $response->assertOk();
    }

    public function test_non_admin_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->seoProvider)->get(route('users.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->regularUser)->get(route('users.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_create_users(): void
    {
        $response = $this->actingAs($this->admin)->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'seo_provider',
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'seo_provider',
        ]);
    }

    public function test_admin_can_access_customer_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('customers.index'));
        $response->assertOk();
    }

    public function test_non_admin_cannot_access_customer_management(): void
    {
        $response = $this->actingAs($this->seoProvider)->get(route('customers.index'));
        $response->assertForbidden();

        $response = $this->actingAs($this->regularUser)->get(route('customers.index'));
        $response->assertForbidden();
    }

    public function test_seo_provider_can_access_projects(): void
    {
        $response = $this->actingAs($this->seoProvider)->get(route('projects.index'));
        $response->assertOk();
    }

    public function test_regular_user_cannot_access_projects(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('projects.index'));
        $response->assertForbidden();
    }

    public function test_seo_provider_can_access_seo_logs(): void
    {
        $response = $this->actingAs($this->seoProvider)->get(route('seo-logs.index'));
        $response->assertOk();
    }

    public function test_regular_user_cannot_access_seo_logs(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('seo-logs.index'));
        $response->assertForbidden();
    }

    public function test_admin_can_delete_other_users_but_not_self(): void
    {
        $otherUser = User::factory()->seoProvider()->create();
        
        // Try to delete another user (should succeed)
        $response = $this->actingAs($this->admin)
            ->delete(route('users.destroy', $otherUser));
        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseMissing('users', ['id' => $otherUser->id]);

        // Try to delete self (should fail)
        $response = $this->actingAs($this->admin)
            ->delete(route('users.destroy', $this->admin));
        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_user_roles_are_properly_enforced(): void
    {
        $customer = Customer::factory()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        // Test admin access
        $this->actingAs($this->admin);
        $this->get(route('customers.index'))->assertOk();
        $this->get(route('projects.index'))->assertOk();
        $this->get(route('seo-logs.index'))->assertOk();
        $this->get(route('users.index'))->assertOk();

        // Test SEO provider access
        $this->actingAs($this->seoProvider);
        $this->get(route('customers.index'))->assertForbidden();
        $this->get(route('projects.index'))->assertOk();
        $this->get(route('seo-logs.index'))->assertOk();
        $this->get(route('users.index'))->assertForbidden();

        // Test regular user access
        $this->actingAs($this->regularUser);
        $this->get(route('customers.index'))->assertForbidden();
        $this->get(route('projects.index'))->assertForbidden();
        $this->get(route('seo-logs.index'))->assertForbidden();
        $this->get(route('users.index'))->assertForbidden();
    }
} 