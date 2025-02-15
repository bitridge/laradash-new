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
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

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

        $reports = Report::with(['project', 'generator'])
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

        // Check project existence and authorization first
        try {
            $project = Project::findOrFail($request->project_id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Only proceed with validation if project_id is not provided
            if (!$request->has('project_id')) {
                $request->validate([
                    'project_id' => 'required|exists:projects,id',
                    'title' => 'required|string|max:255',
                    'description' => 'required|array',
                ]);
            }
            return back()->withErrors(['project_id' => 'Project not found.'])->withInput();
        }

        // Check if user is authorized to create reports for this project
        if (auth()->user()->role === 'seo_provider' && !$project->seoProviders->contains(auth()->user())) {
            abort(403, 'You are not authorized to create reports for this project.');
        }

        // Validate the request
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|array',
            'description.content' => 'required|string',
            'description.plainText' => 'required|string',
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.content' => 'required|array',
            'sections.*.content.content' => 'required|string',
            'sections.*.content.plainText' => 'required|string',
            'sections.*.order' => 'required|integer|min:0',
            'sections.*.image' => 'nullable|image|max:2048',
            'seo_logs' => 'nullable|array',
            'seo_logs.*' => 'exists:seo_logs,id'
        ]);

        try {
            DB::beginTransaction();

            // Create the report
            $report = Report::create([
                'project_id' => $validated['project_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'generated_by' => auth()->id(),
                'generated_at' => now(),
            ]);

            // Create sections
            foreach ($validated['sections'] as $sectionData) {
                $section = new ReportSection([
                    'title' => $sectionData['title'],
                    'content' => $sectionData['content'],
                    'order' => $sectionData['order']
                ]);

                // Save the section to get an ID
                $report->sections()->save($section);

                // Handle image upload if present
                if (isset($sectionData['image']) && $sectionData['image'] instanceof UploadedFile) {
                    $path = $sectionData['image']->store('report-images', 'public');
                    $section->image_path = $path;
                    $section->save();
                }
            }

            // Attach SEO logs if provided
            if (!empty($validated['seo_logs'])) {
                $report->seoLogs()->attach($validated['seo_logs']);
            }

            DB::commit();

            return redirect()->route('reports.show', $report)
                ->with('success', 'Report created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create report: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create report. Please try again.']);
        }
    }

    public function show(Report $report)
    {
        $this->authorize('view', $report);

        $report->load(['project', 'generator', 'sections' => function($query) {
            $query->orderBy('order');
        }, 'seoLogs']);
        
        return view('reports.show', compact('report'));
    }

    public function generatePdf(Report $report)
    {
        $this->authorize('view', $report);

        $report->load([
            'project.customer',
            'generator',
            'sections' => function($query) {
                $query->orderBy('order');
            },
            'seoLogs' => function($query) {
                $query->orderBy('date', 'desc');
            }
        ]);

        $pdf = PDF::loadView('reports.pdf', compact('report'));
        
        return $pdf->download($report->title . '.pdf');
    }
} 