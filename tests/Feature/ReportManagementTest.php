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

        $response->assertOk();
    }

    public function test_report_can_be_created(): void
    {
        $image = UploadedFile::fake()->image('section1.jpg');
        
        $reportData = [
            'project_id' => $this->project->id,
            'title' => 'Test Report',
            'description' => [
                'content' => '<p>Test report description</p>',
                'plainText' => 'Test report description'
            ],
            'sections' => [
                [
                    'title' => 'Section 1',
                    'content' => [
                        'content' => '<p>Test section content</p>',
                        'plainText' => 'Test section content'
                    ],
                    'order' => 1
                ]
            ],
            'seo_logs' => [$this->seoLog->id]
        ];

        // Add the image to the sections array
        $reportData['sections'][0]['image'] = $image;

        $response = $this->actingAs($this->seoProvider)
            ->post(route('reports.store'), $reportData);

        $report = Report::first();
        
        $this->assertNotNull($report);
        $this->assertEquals($this->project->id, $report->project_id);
        $this->assertEquals('Test Report', $report->title);
        $this->assertEquals('<p>Test report description</p>', $report->description['content']);
        
        // Check sections
        $this->assertCount(1, $report->sections);
        $section = $report->sections->first();
        $this->assertEquals('Section 1', $section->title);
        $this->assertEquals('<p>Test section content</p>', $section->content['content']);
        $this->assertTrue($section->getMedia('section_images')->isNotEmpty());
        
        // Check SEO logs
        $this->assertTrue($report->seoLogs->contains($this->seoLog));
        
        $response->assertRedirect(route('reports.show', $report));
    }

    public function test_report_requires_valid_project(): void
    {
        $response = $this->actingAs($this->seoProvider)
            ->post(route('reports.store'), [
                'project_id' => 999,
                'title' => 'Test Report',
                'description' => [
                    'content' => '<p>Test description</p>',
                    'plainText' => 'Test description'
                ]
            ]);

        $response->assertSessionHasErrors('project_id');
    }

    public function test_report_requires_description_content(): void
    {
        $response = $this->actingAs($this->seoProvider)
            ->post(route('reports.store'), [
                'project_id' => $this->project->id,
                'title' => 'Test Report',
                'description' => ''
            ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_report_sections_are_ordered_correctly(): void
    {
        $reportData = [
            'project_id' => $this->project->id,
            'title' => 'Test Report',
            'description' => [
                'content' => '<p>Test description</p>',
                'plainText' => 'Test description'
            ],
            'sections' => [
                [
                    'title' => 'Section 2',
                    'content' => [
                        'content' => '<p>Content 2</p>',
                        'plainText' => 'Content 2'
                    ],
                    'order' => 2
                ],
                [
                    'title' => 'Section 1',
                    'content' => [
                        'content' => '<p>Content 1</p>',
                        'plainText' => 'Content 1'
                    ],
                    'order' => 1
                ]
            ]
        ];

        $this->actingAs($this->seoProvider)
            ->post(route('reports.store'), $reportData);

        $report = Report::first();
        $sections = $report->sections()->orderBy('order')->get();
        
        $this->assertEquals('Section 1', $sections[0]->title);
        $this->assertEquals('Section 2', $sections[1]->title);
    }

    public function test_report_can_be_viewed(): void
    {
        $report = Report::factory()->create([
            'project_id' => $this->project->id,
            'generated_by' => $this->seoProvider->id,
            'title' => 'Test Report',
            'description' => [
                'content' => '<p>Test description</p>',
                'plainText' => 'Test description'
            ]
        ]);

        $response = $this->actingAs($this->seoProvider)
            ->get(route('reports.show', $report));

        $response->assertOk()
            ->assertSee('Test Report')
            ->assertSee('Test description');
    }

    public function test_report_pdf_can_be_generated(): void
    {
        $report = Report::factory()->create([
            'project_id' => $this->project->id,
            'generated_by' => $this->seoProvider->id,
            'title' => 'Test Report',
            'description' => [
                'content' => '<p>Test description</p>',
                'plainText' => 'Test description'
            ]
        ]);

        $response = $this->actingAs($this->seoProvider)
            ->get(route('reports.pdf', $report));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_report_json_content_is_properly_stored(): void
    {
        $reportData = [
            'project_id' => $this->project->id,
            'title' => 'Test Report',
            'description' => [
                'content' => '<h1>Test Heading</h1><p>Test content with <strong>formatting</strong></p>',
                'plainText' => 'Test Heading Test content with formatting'
            ],
            'sections' => [
                [
                    'title' => 'Test Section',
                    'content' => [
                        'content' => '<h2>Section Heading</h2><ul><li>List item 1</li><li>List item 2</li></ul>',
                        'plainText' => 'Section Heading List item 1 List item 2'
                    ],
                    'order' => 1
                ]
            ]
        ];

        $this->actingAs($this->seoProvider)
            ->post(route('reports.store'), $reportData);

        $report = Report::first();
        
        // Check description JSON structure
        $this->assertIsArray($report->description);
        $this->assertArrayHasKey('content', $report->description);
        $this->assertArrayHasKey('plainText', $report->description);
        
        // Check section content JSON structure
        $section = $report->sections->first();
        $this->assertIsArray($section->content);
        $this->assertArrayHasKey('content', $section->content);
        $this->assertArrayHasKey('plainText', $section->content);
        
        // Verify HTML content is preserved
        $this->assertStringContainsString('<strong>formatting</strong>', $report->description['content']);
        $this->assertStringContainsString('<ul><li>List item 1</li>', $section->content['content']);
    }
} 