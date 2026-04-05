<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;

class PricingPlanController extends Controller
{
    public function index()
    {
        $plans = PricingPlan::with('features')->get();
        return view('admin.pricing-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.pricing-plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
        ]);

        $data = $request->only('name', 'description', 'price');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['is_active']  = $request->boolean('is_active');

        PricingPlan::create($data);

        return redirect()->route('admin.pricing-plans.index')
                         ->with('success', 'Pricing plan added successfully.');
    }

    public function show(string $id)
    {
        $plan = PricingPlan::with('features')->findOrFail($id);
        return view('admin.pricing-plans.show', compact('plan'));
    }

    public function edit(string $id)
    {
        $plan = PricingPlan::with('features')->findOrFail($id);
        return view('admin.pricing-plans.edit', compact('plan'));
    }

    public function update(Request $request, string $id)
    {
        $plan = PricingPlan::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
        ]);

        $data = $request->only('name', 'description', 'price');
        $data['is_popular'] = $request->boolean('is_popular');
        $data['is_active']  = $request->boolean('is_active');

        $plan->update($data);

        return redirect()->route('admin.pricing-plans.index')
                         ->with('success', 'Pricing plan updated successfully.');
    }

    public function destroy(string $id)
    {
        $plan = PricingPlan::findOrFail($id);
        $plan->delete();

        return redirect()->route('admin.pricing-plans.index')
                         ->with('success', 'Pricing plan deleted successfully.');
    }
}