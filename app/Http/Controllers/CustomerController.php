<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index()
    {
        $customers = Customer::with(['seoProviders'])->latest()->paginate(10);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $seoProviders = User::where('role', 'seo_provider')->get();
        return view('customers.create', compact('seoProviders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:2048',
            'seo_provider_ids' => 'nullable|array',
            'seo_provider_ids.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'company' => $validated['company'],
                'address' => $validated['address'],
            ]);

            if ($request->hasFile('logo')) {
                $customer->addMediaFromRequest('logo')
                    ->toMediaCollection('logo');
            }

            // Assign SEO providers if selected
            if (!empty($validated['seo_provider_ids'])) {
                $customer->seoProviders()->attach($validated['seo_provider_ids']);
            }

            DB::commit();

            return redirect()->route('customers.index')
                ->with('success', 'Customer created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to create customer. ' . $e->getMessage());
        }
    }

    public function show(Customer $customer)
    {
        $customer->load(['seoProviders', 'projects.seoProviders']);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $seoProviders = User::where('role', 'seo_provider')->get();
        return view('customers.edit', compact('customer', 'seoProviders'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|max:2048',
            'seo_provider_ids' => 'nullable|array',
            'seo_provider_ids.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $customer->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'company' => $validated['company'],
                'address' => $validated['address'],
            ]);

            if ($request->hasFile('logo')) {
                $customer->clearMediaCollection('logo');
                $customer->addMediaFromRequest('logo')
                    ->toMediaCollection('logo');
            }

            // Update SEO provider assignments
            $customer->seoProviders()->sync($validated['seo_provider_ids'] ?? []);

            DB::commit();

            return redirect()->route('customers.index')
                ->with('success', 'Customer updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Failed to update customer. ' . $e->getMessage());
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            DB::beginTransaction();
            
            // This will cascade delete all related records
            $customer->delete();
            
            DB::commit();

            return redirect()->route('customers.index')
                ->with('success', 'Customer deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete customer. ' . $e->getMessage());
        }
    }
} 