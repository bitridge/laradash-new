<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Report;
use App\Models\SeoLog;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $seoProvider;
    private Project $project;
    private SeoLog $seoLog;

    protected function setUp(): void
    {
        parent::setUp();
        
        Storage::fake('public');
        
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->seoProvider = User::factory()->create(['role' => 'seo_provider']);
        
        $customer = Customer::factory()->create();
        $this->project = Project::factory()->create([
            'customer_id' => $customer->id
        ]);
        
        // Assign the SEO provider to the project
        $this->project->seoProviders()->attach($this->seoProvider->id);

        // Create a SEO log for testing
        $this->seoLog = SeoLog::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->seoProvider->id,
            'content' => [
                'content' => '<p>Test SEO log content</p>',
                'plainText' => 'Test SEO log content'
            ]
        ]);
    }

    public function test_report_create_page_is_displayed(): void
    {
        $response = $this->actingAs($this->seoProvider)
            ->get(route('reports.create', ['project_id' => $this->project->id]));

        $response->assertOk()
            ->assertViewIs('reports.create')
            ->assertViewHas('seoLogs')
            ->assertSee('Generate Report')
            ->assertSee('Report Information')
            ->assertSee('Report Sections');
    }

    public function test_report_can_be_created_with_multiple_sections_and_images(): void
    {
        $image1 = UploadedFile::fake()->image('section1.jpg');
        $image2 = UploadedFile::fake()->image('section2.jpg');
        
        $reportData = [
            'project_id' => $this->project->id,
            'title' => 'Comprehensive SEO Report',
            'description' => [
                'content' => '<h1>Executive Summary</h1><p>This is a detailed SEO report with <strong>multiple sections</strong>.</p>',
                'plainText' => 'Executive Summary This is a detailed SEO report with multiple sections.'
            ],
            'sections' => [
                [
                    'title' => 'Technical Analysis',
                    'content' => [
                        'content' => '<h2>Technical Findings</h2><ul><li>Site Speed: Excellent</li><li>Mobile Optimization: Good</li></ul>',
                        'plainText' => 'Technical Findings Site Speed: Excellent Mobile Optimization: Good'
                    ],
                    'order' => 1,
                    'image' => $image1
                ],
                [
                    'title' => 'Content Strategy',
                    'content' => [
                        'content' => '<h2>Content Recommendations</h2><p>Key improvements needed in content structure.</p>',
                        'plainText' => 'Content Recommendations Key improvements needed in content structure.'
                    ],
                    'order' => 2,
                    'image' => $image2
                ]
            ],
            'seo_logs' => [$this->seoLog->id]
        ];

        $response = $this->actingAs($this->seoProvider)
            ->post(route('reports.store'), $reportData);

        $report = Report::first();
        
        // Basic report assertions
        $this->assertNotNull($report);
        $this->assertEquals($this->project->id, $report->project_id);
        $this->assertEquals('Comprehensive SEO Report', $report->title);
        $this->assertEquals($this->seoProvider->id, $report->generated_by);
        $this->assertNotNull($report->generated_at);
        
        // Description assertions
        $this->assertArrayHasKey('content', $report->description);
        $this->assertArrayHasKey('plainText', $report->description);
        $this->assertStringContainsString('Executive Summary', $report->description['content']);
        
        // Sections assertions
        $this->assertCount(2, $report->sections);
        
        // Check first section
        $section1 = $report->sections()->where('order', 1)->first();
        $this->assertEquals('Technical Analysis', $section1->title);
        $this->assertStringContainsString('Technical Findings', $section1->content['content']);
        $this->assertNotNull($section1->image_path);
        
        // Check second section
        $section2 = $report->sections()->where('order', 2)->first();
        $this->assertEquals('Content Strategy', $section2->title);
        $this->assertStringContainsString('Content Recommendations', $section2->content['content']);
        $this->assertNotNull($section2->image_path);
        
        // SEO logs assertions
        $this->assertTrue($report->seoLogs->contains($this->seoLog));
        
        // Response assertions
        $response->assertRedirect(route('reports.show', $report));
        $response->assertSessionHas('success');
    }

    public function test_report_validation_rules(): void
    {
        // Test missing required fields
        $response = $this->actingAs($this->seoProvider)
            ->post(route('reports.store'), []);
        
        $response->assertSessionHasErrors(['project_id', 'title', 'description']);

        // Test invalid project ID
        $response = $this->actingAs($this->seoProvider)
            ->post(route('reports.store'), [
                'project_id' => 999,
                'title' => 'Test Report'
            ]);
        
        $response->assertSessionHasErrors('project_id');

        // Test invalid section data
        $response = $this->actingAs($this->seoProvider)
            ->post(route('reports.store'), [
                'project_id' => $this->project->id,
                'title' => 'Test Report',
                'description' => [
                    'content' => '<p>Test</p>',
                    'plainText' => 'Test'
                ],
                'sections' => [
                    [
                        'title' => '', // Empty title
                        'content' => 'Invalid content format', // Wrong format
                        'order' => 'not a number' // Invalid order
                    ]
                ]
            ]);
        
        $response->assertSessionHasErrors(['sections.0.title', 'sections.0.content', 'sections.0.order']);
    }

    public function test_unauthorized_users_cannot_create_reports(): void
    {
        $regularUser = User::factory()->create(['role' => 'user']);
        
        $response = $this->actingAs($regularUser)
            ->get(route('reports.create', ['project_id' => $this->project->id]));
        
        $response->assertForbidden();

        $response = $this->actingAs($regularUser)
            ->post(route('reports.store'), [
                'project_id' => $this->project->id,
                'title' => 'Test Report'
            ]);
        
        $response->assertForbidden();
    }

    public function test_seo_provider_can_only_create_reports_for_assigned_projects(): void
    {
        // Create a project not assigned to the SEO provider
        $unassignedProject = Project::factory()->create();
        
        $response = $this->actingAs($this->seoProvider)
            ->post(route('reports.store'), [
                'project_id' => $unassignedProject->id,
                'title' => 'Test Report',
                'description' => [
                    'content' => '<p>Test</p>',
                    'plainText' => 'Test'
                ]
            ]);
        
        $response->assertForbidden();
    }

    public function test_report_pdf_generation(): void
    {
        $report = Report::factory()->create([
            'project_id' => $this->project->id,
            'generated_by' => $this->seoProvider->id,
            'title' => 'PDF Test Report',
            'description' => [
                'content' => '<h1>PDF Test</h1><p>This is a test of PDF generation.</p>',
                'plainText' => 'PDF Test This is a test of PDF generation.'
            ]
        ]);

        // Add sections to the report
        $report->sections()->create([
            'title' => 'PDF Section',
            'content' => [
                'content' => '<h2>Section Content</h2><p>Test content for PDF.</p>',
                'plainText' => 'Section Content Test content for PDF.'
            ],
            'order' => 1
        ]);

        // Attach SEO log
        $report->seoLogs()->attach($this->seoLog->id);

        $response = $this->actingAs($this->seoProvider)
            ->get(route('reports.pdf', $report));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'attachment; filename="' . $report->title . '.pdf"');
    }
} 