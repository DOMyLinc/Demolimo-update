<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoostPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BoostPackageController extends Controller
{
    /**
     * Display a listing of boost packages
     */
    public function index()
    {
        $packages = BoostPackage::orderBy('sort_order')->get();

        return view('admin.boost-packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new package
     */
    public function create()
    {
        return view('admin.boost-packages.create');
    }

    /**
     * Store a newly created package
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:boost_packages,slug',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'target_views' => 'required|integer|min:0',
            'target_impressions' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        BoostPackage::create($validated);

        return redirect()->route('admin.boost-packages.index')
            ->with('success', 'Boost package created successfully!');
    }

    /**
     * Show the form for editing a package
     */
    public function edit(BoostPackage $boostPackage)
    {
        return view('admin.boost-packages.edit', compact('boostPackage'));
    }

    /**
     * Update the specified package
     */
    public function update(Request $request, BoostPackage $boostPackage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:boost_packages,slug,' . $boostPackage->id,
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'target_views' => 'required|integer|min:0',
            'target_impressions' => 'required|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $boostPackage->update($validated);

        return redirect()->route('admin.boost-packages.index')
            ->with('success', 'Boost package updated successfully!');
    }

    /**
     * Remove the specified package
     */
    public function destroy(BoostPackage $boostPackage)
    {
        $boostPackage->delete();

        return redirect()->route('admin.boost-packages.index')
            ->with('success', 'Boost package deleted successfully!');
    }
}
