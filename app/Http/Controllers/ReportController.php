<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Project;
use App\Models\SeoLog;
use App\Models\ReportSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with(['project', 'generatedBy'])
            ->latest()
            ->paginate(10);

        return view('reports.index', compact('reports'));
    }

    public function create(Request $request)
    {
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
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'required|array',
            'description.content' => 'required|string',
            'description.plainText' => 'required|string',
            'sections' => 'array',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.content' => 'required|array',
            'sections.*.content.content' => 'required|string',
            'sections.*.content.plainText' => 'required|string',
            'sections.*.order' => 'required|integer|min:0',
            'sections.*.image' => 'nullable|image|max:2048',
            'seo_logs' => 'array',
            'seo_logs.*' => 'exists:seo_logs,id',
        ]);

        try {
            DB::beginTransaction();

            $report = Report::create([
                'project_id' => $validated['project_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'generated_by' => auth()->id(),
                'generated_at' => now(),
            ]);

            if (!empty($validated['sections'])) {
                foreach ($validated['sections'] as $index => $sectionData) {
                    $section = $report->sections()->create([
                        'title' => $sectionData['title'],
                        'content' => $sectionData['content'],
                        'order' => $sectionData['order'],
                    ]);

                    if (isset($sectionData['image']) && $sectionData['image']->isValid()) {
                        $section->addMedia($sectionData['image'])
                               ->toMediaCollection('section_images');
                    }
                }
            }

            if (!empty($validated['seo_logs'])) {
                $report->seoLogs()->attach($validated['seo_logs']);
            }

            DB::commit();

            return redirect()->route('reports.show', $report)
                           ->with('success', 'Report created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create report. ' . $e->getMessage())
                        ->withInput();
        }
    }

    public function show(Report $report)
    {
        $report->load(['project', 'generatedBy', 'sections' => function($query) {
            $query->orderBy('order');
        }, 'seoLogs']);
        
        return view('reports.show', compact('report'));
    }

    public function generatePdf(Report $report)
    {
        $report->load(['project', 'generatedBy', 'sections' => function($query) {
            $query->orderBy('order');
        }, 'seoLogs']);
        
        $pdf = PDF::loadView('reports.pdf', compact('report'));
        
        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');
        
        // Optional: Set other PDF options
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ]);

        return $pdf->download($report->title . '.pdf');
    }
} 