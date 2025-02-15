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
        $customers = Customer::orderBy('name')->get();
        return view('projects.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'website_url' => 'required|url|max:255',
            'status' => 'required|in:active,paused,completed',
            'start_date' => 'required|date',
            'details' => [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        $content = json_decode($value, true);
                        if (!is_array($content) || !isset($content['content']) || !isset($content['plainText'])) {
                            $fail('The details format is invalid.');
                        }
                        if (empty($content['plainText'])) {
                            $fail('The details cannot be empty.');
                        }
                    } catch (\Exception $e) {
                        $fail('The details format is invalid.');
                    }
                },
            ],
            'logo' => 'nullable|image|max:10240',
        ]);

        // Handle details as JSON
        $details = json_decode($validated['details'], true);

        $project = Project::create([
            'customer_id' => $validated['customer_id'],
            'name' => $validated['name'],
            'website_url' => $validated['website_url'],
            'status' => $validated['status'],
            'start_date' => $validated['start_date'],
            'details' => $details,
        ]);

        if ($request->hasFile('logo')) {
            $project->addMediaFromRequest('logo')
                ->toMediaCollection('logo');
        }

        // Automatically assign the SEO provider if they created the project
        if (Auth::user()->role === 'seo_provider') {
            $project->seoProviders()->attach(Auth::id());
        }

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        if (Auth::user()->role === 'seo_provider' && 
            !$project->seoProviders()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        if (Auth::user()->role === 'seo_provider' && 
            !$project->seoProviders()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $customers = Customer::orderBy('name')->get();
        return view('projects.edit', compact('project', 'customers'));
    }

    public function update(Request $request, Project $project)
    {
        if (Auth::user()->role === 'seo_provider' && 
            !$project->seoProviders()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'website_url' => 'required|url|max:255',
            'status' => 'required|in:active,paused,completed',
            'start_date' => 'required|date',
            'details' => [
                'required',
                function ($attribute, $value, $fail) {
                    try {
                        $content = json_decode($value, true);
                        if (!is_array($content) || !isset($content['content']) || !isset($content['plainText'])) {
                            $fail('The details format is invalid.');
                        }
                        if (empty($content['plainText'])) {
                            $fail('The details cannot be empty.');
                        }
                    } catch (\Exception $e) {
                        $fail('The details format is invalid.');
                    }
                },
            ],
            'logo' => 'nullable|image|max:10240',
        ]);

        // Handle details as JSON
        $details = json_decode($validated['details'], true);

        $project->update([
            'customer_id' => $validated['customer_id'],
            'name' => $validated['name'],
            'website_url' => $validated['website_url'],
            'status' => $validated['status'],
            'start_date' => $validated['start_date'],
            'details' => $details,
        ]);

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
        if (Auth::user()->role === 'seo_provider' && 
            !$project->seoProviders()->where('user_id', Auth::id())->exists()) {
            abort(403, 'Unauthorized action.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function customerProjects(Customer $customer)
    {
        $query = $customer->projects()->with('seoProviders');

        if (Auth::user()->role === 'seo_provider') {
            $query->whereHas('seoProviders', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        $projects = $query->latest()->paginate(10);
        return view('projects.customer-projects', compact('customer', 'projects'));
    }
} 