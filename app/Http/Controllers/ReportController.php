<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Project;
use App\Models\SeoLog;
use App\Models\ReportSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,seo_provider');
    }

    public function index()
    {
        $this->authorize('viewAny', Report::class);

        $reports = Report::with(['project', 'generatedBy'])
            ->when(auth()->user()->role === 'seo_provider', function ($query) {
                $query->whereHas('project', function ($q) {
                    $q->whereHas('seoProviders', function ($q) {
                        $q->where('user_id', auth()->id());
                    });
                });
            })
            ->latest()
            ->paginate(10);

        return view('reports.index', compact('reports'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Report::class);

        // Validate project_id is provided
        $request->validate([
            'project_id' => 'required|exists:projects,id'
        ]);

        // Check if user can create report for this project
        if (!Gate::allows('createForProject', [Report::class, $request->project_id])) {
            abort(403, 'You are not authorized to create reports for this project.');
        }

        $projects = Project::all();
        $seoLogs = SeoLog::when($request->project_id, function ($query, $projectId) {
                return $query->where('project_id', $projectId);
            })
            ->latest()
            ->get();

        return view('reports.create', compact('projects', 'seoLogs'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Report::class);

        try {
            // Log the incoming request data
            \Log::info('Report creation request data:', [
                'all' => $request->all(),
                'files' => $request->allFiles()
            ]);

            $validated = $request->validate([
                'project_id' => 'required|exists:projects,id',
                'title' => 'required|string|max:255',
                'description' => 'required|array',
                'description.content' => 'required|string',
                'description.plainText' => 'required|string',
                'sections' => 'array',
                'sections.*.title' => 'required|string|max:255',
                'sections.*.content' => 'required|string',
                'sections.*.order' => 'required|integer|min:0',
                'sections.*.image' => 'nullable|image|max:2048',
                'seo_logs' => 'array',
                'seo_logs.*' => 'exists:seo_logs,id',
            ]);

            // Log the validated data
            \Log::info('Validated report data:', $validated);

            // Check if user can create report for this project
            if (!Gate::allows('createForProject', [Report::class, $validated['project_id']])) {
                \Log::warning('Unauthorized report creation attempt for project', [
                    'user_id' => auth()->id(),
                    'project_id' => $validated['project_id']
                ]);
                abort(403, 'You are not authorized to create reports for this project.');
            }

            try {
                DB::beginTransaction();

                // Parse section content if it's a JSON string
                if (!empty($validated['sections'])) {
                    foreach ($validated['sections'] as $index => &$sectionData) {
                        if (is_string($sectionData['content'])) {
                            $sectionData['content'] = json_decode($sectionData['content'], true);
                            if (json_last_error() !== JSON_ERROR_NONE) {
                                throw new \Exception('Invalid section content format for section ' . ($index + 1));
                            }
                        }
                    }
                }

                $report = Report::create([
                    'project_id' => $validated['project_id'],
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'generated_by' => auth()->id(),
                    'generated_at' => now(),
                ]);

                // Log the created report
                \Log::info('Report created:', ['report_id' => $report->id]);

                if (!empty($validated['sections'])) {
                    foreach ($validated['sections'] as $index => $sectionData) {
                        $section = $report->sections()->create([
                            'title' => $sectionData['title'],
                            'content' => $sectionData['content'],
                            'order' => $sectionData['order'],
                        ]);

                        // Log section creation
                        \Log::info('Report section created:', [
                            'section_id' => $section->id,
                            'report_id' => $report->id
                        ]);

                        if (isset($sectionData['image']) && $sectionData['image']->isValid()) {
                            $section->addMedia($sectionData['image'])
                                   ->toMediaCollection('section_images');
                            \Log::info('Section image added', [
                                'section_id' => $section->id,
                                'image_name' => $sectionData['image']->getClientOriginalName()
                            ]);
                        }
                    }
                }

                if (!empty($validated['seo_logs'])) {
                    $report->seoLogs()->attach($validated['seo_logs']);
                    \Log::info('SEO logs attached to report:', [
                        'report_id' => $report->id,
                        'seo_log_ids' => $validated['seo_logs']
                    ]);
                }

                DB::commit();

                return redirect()->route('reports.show', $report)
                               ->with('success', 'Report created successfully.');
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Failed to create report:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return back()->with('error', 'Failed to create report: ' . $e->getMessage())
                            ->withInput();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Report validation failed:', [
                'errors' => $e->errors(),
                'data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Unexpected error in report creation:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An unexpected error occurred: ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function show(Report $report)
    {
        $this->authorize('view', $report);

        $report->load(['project', 'generatedBy', 'sections' => function($query) {
            $query->orderBy('order');
        }, 'seoLogs']);
        
        return view('reports.show', compact('report'));
    }

    public function generatePdf(Report $report)
    {
        $this->authorize('view', $report);

        $report->load(['project', 'generatedBy', 'sections' => function($query) {
            $query->orderBy('order');
        }, 'seoLogs']);

        $pdf = PDF::loadView('reports.pdf', compact('report'));
        
        return $pdf->download($report->title . '.pdf');
    }
} 