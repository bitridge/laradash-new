<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;

class ProjectController extends BaseController
{
    use AuthorizesRequests, ValidatesRequests, DispatchesJobs;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,seo_provider');
    }

    public function index()
    {
        $this->authorize('viewAny', Project::class);
        
        $query = Project::with(['customer', 'seoProviders']);
        
        if (Auth::user()->role === 'seo_provider') {
            $query->whereHas('seoProviders', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }
        
        $projects = $query->latest()->paginate(10);
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);
        
        $customers = Customer::orderBy('name')->get();
        return view('projects.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);
        
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'website_url' => 'required|url|max:255',
            'status' => 'required|string|in:active,paused,completed',
            'start_date' => 'required|date',
            'details' => 'nullable|json',
            'logo' => 'nullable|image|max:2048',
        ]);

        $project = Project::create($validated);
        
        if ($request->hasFile('logo')) {
            $project->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }
        
        if (Auth::user()->role === 'seo_provider') {
            $project->seoProviders()->attach(Auth::id());
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);
        
        $project->load(['customer', 'seoProviders', 'seoLogs' => function($query) {
            $query->latest()->take(5);
        }]);
        
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        
        $customers = Customer::orderBy('name')->get();
        return view('projects.edit', compact('project', 'customers'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'website_url' => 'required|url|max:255',
            'status' => 'required|string|in:active,paused,completed',
            'start_date' => 'required|date',
            'details' => 'nullable|json',
            'logo' => 'nullable|image|max:2048',
        ]);

        $project->update($validated);
        
        if ($request->hasFile('logo')) {
            $project->clearMediaCollection('logo');
            $project->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        
        $project->delete();
        
        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function customerProjects(Customer $customer)
    {
        $this->authorize('viewAny', Project::class);
        
        $projects = $customer->projects()
            ->with('seoProviders')
            ->when(Auth::user()->role === 'seo_provider', function ($query) {
                $query->whereHas('seoProviders', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            })
            ->latest()
            ->paginate(10);
        
        return view('projects.customer-projects', compact('customer', 'projects'));
    }
} 