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
        $plans = PricingPlan::with('features')
            ->latest()
            ->get();
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
        $data['is_active'] = $request->boolean('is_active', true);
        $data['original_price'] = $request->filled('original_price') ? $request->input('original_price') : null;

        if (!is_null($data['original_price']) && (float) $data['original_price'] <= (float) $data['price']) {
            $data['original_price'] = null;
        }

        if ($data['is_popular']) {
            PricingPlan::query()->update(['is_popular' => false]);
        }

        $plan = PricingPlan::create($data);

        return redirect('/admin/pricing-plans')->with('success', 'Pricing plan added successfully.');
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
        $plan = PricingPlan::with('features')->findOrFail($id);

        $request->validate([
            'name'                       => 'required|string|max:255',
            'description'                => 'nullable|string|max:1000',
            'price'                      => 'required|numeric|min:0',
            'original_price'             => 'nullable|numeric|min:0',
            'features'                   => 'nullable|array',
            'features.*.feature_key'     => 'nullable|string|max:100',
            'features.*.feature_label'   => 'nullable|string|max:255',
            'features.*.feature_value'   => 'nullable|string|max:255',
        ]);

        $data = $request->only('name', 'description', 'price', 'original_price');
        $data['is_popular'] = $request->boolean('is_popular', false);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['original_price'] = $request->filled('original_price') ? $request->input('original_price') : null;

        if (!is_null($data['original_price']) && (float) $data['original_price'] <= (float) $data['price']) {
            $data['original_price'] = null;
        }

        if ($data['is_popular']) {
            PricingPlan::where('id', '!=', $plan->id)->update(['is_popular' => false]);
        }

        $plan->update($data);
        $this->updateFeatures($plan, $request->input('features', []));

        return redirect('/admin/pricing-plans')->with('success', 'Pricing plan updated successfully.');
    }

    public function destroy(string $id)
    {
        $plan = PricingPlan::findOrFail($id);
        $plan->delete();

        return redirect('/admin/pricing-plans')->with('success', 'Pricing plan deleted successfully.');
    }

    private function updateFeatures(PricingPlan $plan, array $features): void
    {
        foreach ($features as $featureInput) {
            $key = trim((string) ($featureInput['feature_key'] ?? ''));
            if ($key === '') {
                continue;
            }

            $label = trim((string) ($featureInput['feature_label'] ?? ''));
            $value = trim((string) ($featureInput['feature_value'] ?? ''));

            $plan->features()->where('feature_key', $key)->update([
                'feature_label' => $label,
                'feature_value' => $value,
                'feature'       => $label, // keep legacy column in sync
                'is_available'  => isset($featureInput['is_available']),
            ]);
        }
    }
}