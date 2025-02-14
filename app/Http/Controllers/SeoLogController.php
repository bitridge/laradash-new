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
        $this->authorize('viewAny', SeoLog::class);

        $query = SeoLog::with(['project', 'user']);

        if (Auth::user()->role === 'seo_provider') {
            $query->whereHas('project', function ($q) {
                $q->whereHas('seoProviders', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            });
        }

        $logs = $query->latest()->paginate(10);

        return view('seo-logs.index', compact('logs'));
    }

    public function create()
    {
        $this->authorize('create', SeoLog::class);

        $projects = Auth::user()->role === 'admin'
            ? Project::all()
            : Project::whereHas('seoProviders', function ($query) {
                $query->where('user_id', Auth::id());
            })->get();

        return view('seo-logs.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', SeoLog::class);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'log_type' => 'required|in:' . implode(',', array_keys(SeoLog::TYPES)),
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'content' => 'required|string',
            'action_items' => 'nullable|array',
            'recommendations' => 'nullable|array'
        ]);

        $this->authorize('accessProject', [SeoLog::class, $validated['project_id']]);

        $data = $validated;
        $data['user_id'] = Auth::id();
        
        // Ensure content is properly encoded as JSON if it's not already
        if (is_string($data['content']) && !is_null(json_decode($data['content']))) {
            $data['content'] = json_decode($data['content'], true);
        }

        $seoLog = SeoLog::create($data);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $seoLog->addMedia($file)->toMediaCollection('attachments');
            }
        }

        return redirect()->route('seo-logs.show', $seoLog)
            ->with('success', 'SEO log created successfully.');
    }

    public function show(SeoLog $seoLog)
    {
        $this->authorize('view', $seoLog);
        return view('seo-logs.show', compact('seoLog'));
    }

    public function edit(SeoLog $seoLog)
    {
        $this->authorize('update', $seoLog);

        $projects = Auth::user()->role === 'admin'
            ? Project::all()
            : Project::whereHas('seoProviders', function ($query) {
                $query->where('user_id', Auth::id());
            })->get();

        return view('seo-logs.edit', compact('seoLog', 'projects'));
    }

    public function update(Request $request, SeoLog $seoLog)
    {
        $this->authorize('update', $seoLog);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'log_type' => 'required|in:' . implode(',', array_keys(SeoLog::TYPES)),
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'content' => 'required|string',
            'action_items' => 'nullable|array',
            'recommendations' => 'nullable|array'
        ]);

        $this->authorize('accessProject', [SeoLog::class, $validated['project_id']]);

        $data = $validated;
        
        // Ensure content is properly encoded as JSON if it's not already
        if (is_string($data['content']) && !is_null(json_decode($data['content']))) {
            $data['content'] = json_decode($data['content'], true);
        }

        $seoLog->update($data);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $seoLog->addMedia($file)->toMediaCollection('attachments');
            }
        }

        return redirect()->route('seo-logs.show', $seoLog)
            ->with('success', 'SEO log updated successfully.');
    }

    public function destroy(SeoLog $seoLog)
    {
        $this->authorize('delete', $seoLog);

        $seoLog->media()->delete();
        $seoLog->delete();

        return redirect()->route('seo-logs.index')
            ->with('success', 'SEO log deleted successfully.');
    }

    public function deleteAttachment(SeoLog $seoLog, $mediaId)
    {
        $this->authorize('update', $seoLog);

        $media = $seoLog->media()->findOrFail($mediaId);
        $media->delete();

        return back()->with('success', 'Attachment deleted successfully.');
    }
} 