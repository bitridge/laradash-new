<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Models\SeoLog;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Spatie\MediaLibrary\Models\Media;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SeoLogController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, DispatchesJobs;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,seo_provider');
    }

    public function index()
    {
        $query = SeoLog::with(['project.customer', 'user']);

        if (Auth::user()->role === 'seo_provider') {
            $query->whereHas('project.customer', function ($q) {
                $q->whereHas('seoProviders', function ($sq) {
                    $sq->where('users.id', Auth::id());
                });
            });
        }

        $logs = $query->latest()->paginate(10);

        return view('seo-logs.index', compact('logs'));
    }

    public function create(Request $request)
    {
        $query = Project::with('customer');
        
        if (auth()->user()->role === 'seo_provider') {
            $query->whereHas('customer', function ($q) {
                $q->whereHas('seoProviders', function ($sq) {
                    $sq->where('users.id', auth()->id());
                });
            });
        }
        
        $projects = $query->get();

        // Pre-select project if project_id is provided
        $selectedProject = null;
        if ($request->has('project_id')) {
            $selectedProject = $projects->find($request->project_id);
            if (!$selectedProject) {
                abort(404);
            }
        }

        return view('seo-logs.create', compact('projects', 'selectedProject'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', SeoLog::class);
        $this->authorize('accessProject', [SeoLog::class, $request->project_id]);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'log_type' => 'required|string|in:' . implode(',', array_keys(SeoLog::TYPES)),
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'content' => 'required|json',
            'attachments.*' => 'nullable|file|max:10240'
        ]);

        try {
            DB::beginTransaction();

            $seoLog = SeoLog::create([
                'project_id' => $validated['project_id'],
                'user_id' => Auth::id(),
                'log_type' => $validated['log_type'],
                'title' => $validated['title'],
                'date' => $validated['date'],
                'content' => json_decode($validated['content'], true)
            ]);

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $attachment) {
                    $seoLog->addMedia($attachment)
                        ->toMediaCollection('attachments');
                }
            }

            DB::commit();

            return redirect()->route('seo-logs.index')
                ->with('success', 'SEO log created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create SEO log. ' . $e->getMessage());
        }
    }

    public function show(SeoLog $seoLog)
    {
        if (Auth::user()->role === 'seo_provider' && 
            !$seoLog->project->seoProviders()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        return view('seo-logs.show', compact('seoLog'));
    }

    public function edit(SeoLog $seoLog)
    {
        if (Auth::user()->role === 'seo_provider' && 
            !$seoLog->project->seoProviders()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $projects = Project::when(auth()->user()->role === 'seo_provider', function ($query) {
            $query->whereHas('seoProviders', function ($q) {
                $q->where('user_id', auth()->id());
            });
        })->get();

        return view('seo-logs.edit', compact('seoLog', 'projects'));
    }

    public function update(Request $request, SeoLog $seoLog)
    {
        if (Auth::user()->role === 'seo_provider' && 
            !$seoLog->project->seoProviders()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'log_type' => 'required|in:' . implode(',', array_keys(SeoLog::TYPES)),
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'content' => 'required',
        ]);

        // Check if user has access to the new project
        if (Auth::user()->role === 'seo_provider' && $validated['project_id'] !== $seoLog->project_id) {
            $project = Project::find($validated['project_id']);
            if (!$project->seoProviders()->where('user_id', Auth::id())->exists()) {
                abort(403, 'Unauthorized action.');
            }
        }

        // Handle content as array or JSON
        $content = is_string($validated['content']) 
            ? json_decode($validated['content'], true) 
            : $validated['content'];

        $seoLog->update([
            'project_id' => $validated['project_id'],
            'log_type' => $validated['log_type'],
            'title' => $validated['title'],
            'date' => $validated['date'],
            'content' => $content,
        ]);

        // Handle attachments if present
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $attachment) {
                $seoLog->addMedia($attachment)
                    ->toMediaCollection('attachments');
            }
        }

        return redirect()->route('seo-logs.show', $seoLog)
            ->with('success', 'SEO log updated successfully.');
    }

    public function destroy(SeoLog $seoLog)
    {
        if (Auth::user()->role === 'seo_provider' && 
            !$seoLog->project->seoProviders()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $seoLog->delete();

        return redirect()->route('seo-logs.index')
            ->with('success', 'SEO log deleted successfully.');
    }

    public function deleteAttachment(SeoLog $seoLog, $mediaId)
    {
        $this->authorize('update', $seoLog);

        try {
            $media = $seoLog->media()->findOrFail($mediaId);
            $media->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attachment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attachment'
            ], 500);
        }
    }
} 