<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\SeoLog;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoLogManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $seoProvider;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->admin = User::factory()->admin()->create();
        $this->seoProvider = User::factory()->seoProvider()->create();
        
        $customer = Customer::factory()->create();
        $this->project = Project::factory()->create([
            'customer_id' => $customer->id
        ]);
        
        // Assign the SEO provider to the project
        $this->project->seoProviders()->attach($this->seoProvider->id);
    }

    public function test_seo_log_index_page_is_displayed(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('seo-logs.index'));

        $response->assertOk();
    }

    public function test_seo_log_can_be_created(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $logData = [
            'project_id' => $this->project->id,
            'title' => 'Test SEO Log',
            'log_type' => 'technical',
            'date' => now()->format('Y-m-d'),
            'content' => [
                'content' => '<p>Test content</p>',
                'plainText' => 'Test content'
            ],
            'attachments' => [$file]
        ];

        $response = $this->actingAs($this->seoProvider)
            ->post(route('seo-logs.store'), $logData);

        $response->assertRedirect();
        
        $seoLog = SeoLog::where('project_id', $this->project->id)
            ->where('title', 'Test SEO Log')
            ->first();
            
        $this->assertNotNull($seoLog);
        $this->assertEquals($this->project->id, $seoLog->project_id);
        $this->assertEquals($this->seoProvider->id, $seoLog->user_id);
        $this->assertEquals('technical', $seoLog->log_type);
        $this->assertTrue($seoLog->getMedia('attachments')->isNotEmpty());
    }

    public function test_seo_log_can_be_updated(): void
    {
        Storage::fake('public');
        $seoLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id,
            'log_type' => 'technical',
            'title' => 'Original Title'
        ]);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $updatedData = [
            'project_id' => $this->project->id,
            'title' => 'Updated SEO Log',
            'log_type' => 'technical',
            'date' => now()->format('Y-m-d'),
            'content' => [
                'content' => '<p>Updated content</p>',
                'plainText' => 'Updated content'
            ],
            'attachments' => [$file]
        ];

        $response = $this->actingAs($this->seoProvider)
            ->put(route('seo-logs.update', $seoLog), $updatedData);

        $response->assertRedirect();
        
        $seoLog->refresh();
        $this->assertEquals('technical', $seoLog->log_type);
        $this->assertEquals('Updated SEO Log', $seoLog->title);
        $this->assertTrue($seoLog->getMedia('attachments')->isNotEmpty());
    }

    public function test_seo_log_can_be_deleted(): void
    {
        $seoLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id
        ]);

        $attachment = UploadedFile::fake()->image('work.jpg');
        $seoLog->addMedia($attachment)->toMediaCollection('attachments');

        $response = $this->actingAs($this->seoProvider)
            ->delete(route('seo-logs.destroy', $seoLog));

        $response->assertRedirect();
        $this->assertModelMissing($seoLog);
        $this->assertEmpty($seoLog->getMedia('attachments'));
    }

    public function test_seo_provider_can_only_see_assigned_projects_logs(): void
    {
        // Create another project and log not assigned to the SEO provider
        $otherProject = Project::factory()->create();
        $otherLog = SeoLog::factory()->create([
            'project_id' => $otherProject->id,
            'user_id' => $this->admin->id
        ]);

        // Create a log for the assigned project
        $assignedLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id
        ]);

        $response = $this->actingAs($this->seoProvider)
            ->get(route('seo-logs.index'));

        $response->assertOk()
            ->assertSee($assignedLog->title)
            ->assertDontSee($otherLog->title);
    }

    public function test_admin_can_see_all_logs(): void
    {
        // Create logs for different projects
        $log1 = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id
        ]);

        $otherProject = Project::factory()->create();
        $log2 = SeoLog::factory()->create([
            'project_id' => $otherProject->id,
            'user_id' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('seo-logs.index'));

        $response->assertOk()
            ->assertSee($log1->title)
            ->assertSee($log2->title);
    }

    public function test_seo_log_requires_valid_project(): void
    {
        $response = $this->actingAs($this->seoProvider)
            ->post(route('seo-logs.store'), [
                'project_id' => 999, // Non-existent project
                'log_type' => 'technical',
                'title' => 'Test Log',
                'date' => now()->format('Y-m-d'),
                'content' => json_encode(['content' => '<p>Test</p>', 'plainText' => 'Test'])
            ]);

        $response->assertSessionHasErrors('project_id');
    }

    public function test_seo_log_requires_valid_log_type(): void
    {
        $response = $this->actingAs($this->seoProvider)
            ->post(route('seo-logs.store'), [
                'project_id' => $this->project->id,
                'log_type' => 'invalid_type',
                'title' => 'Test Log',
                'date' => now()->format('Y-m-d'),
                'content' => json_encode(['content' => '<p>Test</p>', 'plainText' => 'Test'])
            ]);

        $response->assertSessionHasErrors('log_type');
    }

    public function test_seo_log_date_defaults_to_today(): void
    {
        $response = $this->actingAs($this->seoProvider)
            ->get(route('seo-logs.create'));

        $response->assertOk()
            ->assertSee('value="' . now()->format('Y-m-d') . '"', false);
    }

    public function test_seo_log_belongs_to_project(): void
    {
        $seoLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id
        ]);

        $this->assertEquals($this->project->id, $seoLog->project->id);
        $this->assertTrue($seoLog->project->seoLogs->contains($seoLog));
    }

    public function test_seo_log_belongs_to_user(): void
    {
        $seoLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id
        ]);

        $this->assertEquals($this->seoProvider->id, $seoLog->user->id);
        $this->assertTrue($this->seoProvider->seoLogs->contains($seoLog));
    }
} 