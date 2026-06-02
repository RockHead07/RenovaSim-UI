<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlanFeature;
use App\Models\PricingPlan;
use Illuminate\Http\Request;

class PricingPlanController extends Controller
{
    public function index()
    {
        $plans = PricingPlan::with('features')->latest()->get();
        return view('admin.pricing-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.pricing-plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
        ]);

        $data = $request->only('name', 'description', 'price', 'original_price');
        $data['is_popular'] = $request->boolean('is_popular', false);
        $data['is_active']  = $request->boolean('is_active', true);
        $data['original_price'] = $request->filled('original_price') ? $request->input('original_price') : null;

        if (!is_null($data['original_price']) && (float) $data['original_price'] <= (float) $data['price']) {
            $data['original_price'] = null;
        }

        if ($data['is_popular']) {
            PricingPlan::query()->update(['is_popular' => false]);
        }

        PricingPlan::create($data);
        return redirect('/admin/pricing-plans')->with('success', 'Pricing plan added successfully.');
    }

    public function show(int $id)
    {
        $plan = PricingPlan::with('features')->findOrFail($id);
        return view('admin.pricing-plans.show', compact('plan'));
    }

    public function edit(int $id)
    {
        $plan = PricingPlan::with('features')->findOrFail($id);
        return view('admin.pricing-plans.edit', compact('plan'));
    }

    public function update(Request $request, int $id)
    {
        $plan = PricingPlan::findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string|max:1000',
            'price'          => 'required|numeric|min:0',
            'original_price' => 'nullable|numeric|min:0',
        ]);

        $data = $request->only('name', 'description', 'price', 'original_price');
        $data['is_popular'] = $request->boolean('is_popular', false);
        $data['is_active']  = $request->boolean('is_active', true);
        $data['original_price'] = $request->filled('original_price') ? $request->input('original_price') : null;

        if (!is_null($data['original_price']) && (float) $data['original_price'] <= (float) $data['price']) {
            $data['original_price'] = null;
        }

        if ($data['is_popular']) {
            PricingPlan::where('id', '!=', $id)->update(['is_popular' => false]);
        }

        $plan->update($data);

        foreach ($request->input('features', []) as $featureInput) {
            $key = trim((string) ($featureInput['feature_key'] ?? ''));
            if ($key === '') continue;

            PlanFeature::where('pricing_plan_id', $id)
                ->where('feature_key', $key)
                ->update([
                    'feature_label' => trim($featureInput['feature_label'] ?? ''),
                    'feature_value' => trim($featureInput['feature_value'] ?? ''),
                    'feature'       => trim($featureInput['feature_label'] ?? ''),
                    'is_available'  => isset($featureInput['is_available']),
                ]);
        }

        return redirect('/admin/pricing-plans')->with('success', 'Pricing plan updated successfully.');
    }

    public function destroy(int $id)
    {
        $plan = PricingPlan::findOrFail($id);
        $plan->features()->delete();
        $plan->delete();
        return redirect('/admin/pricing-plans')->with('success', 'Pricing plan deleted successfully.');
    }
}
