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

        $query = Project::with('customer');
        
        if (auth()->user()->role === 'seo_provider') {
            $query->whereHas('customer', function ($q) {
                $q->whereHas('seoProviders', function ($sq) {
                    $sq->where('users.id', auth()->id());
                });
            });
        }
        
        $projects = $query->get();
        $seoLogs = collect(); // Initialize empty collection

        // Pre-select project if project_id is provided
        $selectedProject = null;
        if ($request->has('project_id')) {
            $selectedProject = $projects->find($request->project_id);
            if (!$selectedProject) {
                abort(404);
            }
            // Check if user can create report for this project
            $this->authorize('createForProject', [Report::class, $request->project_id]);

            // Get SEO logs for the selected project
            $seoLogs = SeoLog::where('project_id', $request->project_id)
                ->when(auth()->user()->role === 'seo_provider', function ($query) {
                    $query->where('user_id', auth()->id());
                })
                ->latest()
                ->get();
        }

        return view('reports.create', compact('projects', 'selectedProject', 'seoLogs'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Report::class);
        
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.content' => 'required|string',
            'sections.*.order' => 'required|integer|min:1',
            'sections.*.image' => 'nullable|image|max:2048',
            'seo_log_ids' => 'nullable|array',
            'seo_log_ids.*' => 'exists:seo_logs,id'
        ]);

        // Check if user can create report for this project
        $this->authorize('createForProject', [Report::class, $validated['project_id']]);

        try {
            DB::beginTransaction();

            // Create the report
            $report = Report::create([
                'project_id' => $validated['project_id'],
                'title' => $validated['title'],
                'description' => [
                    'content' => $validated['description'],
                    'plainText' => strip_tags($validated['description'])
                ],
                'generated_by' => auth()->id(),
                'generated_at' => now(),
            ]);

            // Create report sections
            foreach ($validated['sections'] as $sectionData) {
                $report->sections()->create([
                    'title' => $sectionData['title'],
                    'content' => [
                        'content' => $sectionData['content'],
                        'plainText' => strip_tags($sectionData['content'])
                    ],
                    'order' => $sectionData['order']
                ]);

                if (isset($sectionData['image']) && $sectionData['image'] instanceof UploadedFile) {
                    $section = $report->sections()->latest()->first();
                    $section->image_path = $sectionData['image']->store('report-sections', 'public');
                    $section->save();
                }
            }

            // Attach SEO logs if provided
            if (!empty($validated['seo_log_ids'])) {
                $report->seoLogs()->attach($validated['seo_log_ids']);
            }

            DB::commit();

            return redirect()->route('reports.show', $report)
                ->with('success', 'Report created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create report: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'project_id' => $validated['project_id'],
                'error' => $e->getMessage()
            ]);
            
            return back()->withInput()
                ->with('error', 'Failed to create report. Please try again.');
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