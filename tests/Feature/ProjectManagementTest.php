<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $seoProvider;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->seoProvider = User::factory()->create(['role' => 'seo_provider']);
        $this->customer = Customer::factory()->create();
        Storage::fake('public');
    }

    public function test_project_index_page_is_displayed(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('projects.index'));

        $response->assertOk();
    }

    public function test_seo_provider_can_only_see_assigned_projects(): void
    {
        // Create projects and assign one to the SEO provider
        $assignedProject = Project::factory()->create(['customer_id' => $this->customer->id]);
        $unassignedProject = Project::factory()->create(['customer_id' => $this->customer->id]);
        
        $assignedProject->seoProviders()->attach($this->seoProvider->id);

        $response = $this->actingAs($this->seoProvider)
            ->get(route('projects.index'));

        $response->assertOk()
            ->assertSee($assignedProject->name)
            ->assertDontSee($unassignedProject->name);
    }

    public function test_seo_provider_can_create_project_and_is_automatically_assigned(): void
    {
        $logo = UploadedFile::fake()->image('project-logo.jpg');
        $details = ['content' => 'Test project details'];

        $response = $this->actingAs($this->seoProvider)
            ->post(route('projects.store'), [
                'customer_id' => $this->customer->id,
                'name' => 'Test Project',
                'website_url' => 'https://example.com',
                'description' => 'Test Description',
                'status' => 'active',
                'start_date' => '2024-02-15',
                'details' => json_encode($details),
                'logo' => $logo,
            ]);

        $project = Project::first();
        
        $this->assertDatabaseHas('projects', [
            'customer_id' => $this->customer->id,
            'name' => 'Test Project',
        ]);

        $this->assertTrue($project->seoProviders()->where('user_id', $this->seoProvider->id)->exists());
        $response->assertRedirect(route('projects.show', $project));
    }

    public function test_seo_provider_cannot_access_unassigned_project(): void
    {
        $project = Project::factory()->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->seoProvider)
            ->get(route('projects.show', $project));

        $response->assertForbidden();
    }

    public function test_project_can_be_created(): void
    {
        $logo = UploadedFile::fake()->image('project-logo.jpg');
        $details = ['content' => 'Test project details'];

        $response = $this->actingAs($this->admin)
            ->post(route('projects.store'), [
                'customer_id' => $this->customer->id,
                'name' => 'Test Project',
                'website_url' => 'https://example.com',
                'description' => 'Test Description',
                'status' => 'active',
                'start_date' => '2024-02-15',
                'details' => json_encode($details),
                'logo' => $logo,
            ]);

        $project = Project::first();
        
        $this->assertDatabaseHas('projects', [
            'customer_id' => $this->customer->id,
            'name' => 'Test Project',
            'website_url' => 'https://example.com',
            'status' => 'active',
        ]);

        $this->assertTrue($project->getMedia('logo')->isNotEmpty());
        $response->assertRedirect(route('projects.show', $project));
    }

    public function test_project_can_be_created_without_logo(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('projects.store'), [
                'customer_id' => $this->customer->id,
                'name' => 'Test Project',
                'website_url' => 'https://example.com',
                'description' => 'Test Description',
                'status' => 'active',
                'start_date' => '2024-02-15',
                'details' => json_encode(['content' => 'Test project details']),
            ]);

        $this->assertDatabaseHas('projects', [
            'customer_id' => $this->customer->id,
            'name' => 'Test Project',
        ]);
    }

    public function test_project_can_be_updated(): void
    {
        $project = Project::factory()->create([
            'customer_id' => $this->customer->id
        ]);
        
        $newLogo = UploadedFile::fake()->image('new-logo.jpg');
        $newDetails = ['content' => 'Updated project details'];

        $response = $this->actingAs($this->admin)
            ->put(route('projects.update', $project), [
                'customer_id' => $this->customer->id,
                'name' => 'Updated Project',
                'website_url' => 'https://updated-example.com',
                'description' => 'Updated Description',
                'status' => 'paused',
                'start_date' => '2024-02-16',
                'details' => json_encode($newDetails),
                'logo' => $newLogo,
            ]);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => 'Updated Project',
            'website_url' => 'https://updated-example.com',
            'status' => 'paused',
        ]);

        $project->refresh();
        $this->assertTrue($project->getMedia('logo')->isNotEmpty());
        $response->assertRedirect(route('projects.show', $project));
    }

    public function test_project_can_be_deleted(): void
    {
        $project = Project::factory()->create([
            'customer_id' => $this->customer->id
        ]);
        
        $logo = UploadedFile::fake()->image('logo.jpg');
        $project->addMedia($logo)->toMediaCollection('logo');

        $response = $this->actingAs($this->admin)
            ->delete(route('projects.destroy', $project));

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertEmpty($project->getMedia('logo'));
        $response->assertRedirect(route('projects.index'));
    }

    public function test_project_validation_rules(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('projects.store'), [
                'name' => '',
                'website_url' => 'not-a-url',
                'status' => 'invalid-status',
                'logo' => UploadedFile::fake()->create('document.pdf', 100),
            ]);

        $response->assertSessionHasErrors(['customer_id', 'name', 'website_url', 'status', 'start_date']);
    }

    public function test_project_belongs_to_customer(): void
    {
        $project = Project::factory()->create([
            'customer_id' => $this->customer->id
        ]);

        $this->assertInstanceOf(Customer::class, $project->customer);
        $this->assertEquals($this->customer->id, $project->customer->id);
    }

    public function test_customer_has_many_projects(): void
    {
        $projects = Project::factory()->count(3)->create([
            'customer_id' => $this->customer->id
        ]);

        $this->assertCount(3, $this->customer->projects);
        $this->assertInstanceOf(Project::class, $this->customer->projects->first());
    }

    public function test_project_show_page_displays_project_details(): void
    {
        $project = Project::factory()->create([
            'customer_id' => $this->customer->id
        ]);
        
        $logo = UploadedFile::fake()->image('logo.jpg');
        $project->addMedia($logo)->toMediaCollection('logo');

        $response = $this->actingAs($this->admin)
            ->get(route('projects.show', $project));

        $response->assertOk()
            ->assertSee($project->name)
            ->assertSee($project->website_url)
            ->assertSee($project->customer->name);
    }

    public function test_project_edit_page_displays_current_values(): void
    {
        $project = Project::factory()->create([
            'customer_id' => $this->customer->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('projects.edit', $project));

        $response->assertOk()
            ->assertSee($project->name)
            ->assertSee($project->website_url)
            ->assertSee($project->customer->name);
    }

    public function test_customer_projects_page_displays_customer_specific_projects(): void
    {
        $customerProjects = Project::factory()->count(3)->create([
            'customer_id' => $this->customer->id
        ]);
        
        $otherCustomer = Customer::factory()->create();
        $otherProjects = Project::factory()->count(2)->create([
            'customer_id' => $otherCustomer->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('projects.index'));

        $response->assertOk();
        
        // Verify that the customer's projects are displayed
        foreach ($customerProjects as $project) {
            $response->assertSee($project->name);
        }
    }
} 