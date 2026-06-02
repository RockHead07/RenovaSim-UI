<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\Request;

class PricingPlanController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    public function index()
    {
        $raw = $this->supabase->select('pricing_plans', '*');
        usort($raw, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $plans = collect($raw)->map(function ($p) {
            $features = $this->supabase->select('plan_features', '*', ['pricing_plan_id' => $p['id']]);
            $p['features'] = collect($features)->map(fn($f) => (object) $f);
            return (object) $p;
        });

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
            $allPlans = $this->supabase->select('pricing_plans', 'id');
            foreach ($allPlans as $p) {
                $this->supabase->update('pricing_plans', $p['id'], ['is_popular' => false]);
            }
        }

        $this->supabase->insert('pricing_plans', $data);
        return redirect('/admin/pricing-plans')->with('success', 'Pricing plan added successfully.');
    }

    public function show(int $id)
    {
        $rows = $this->supabase->select('pricing_plans', '*', ['id' => $id]);
        if (empty($rows)) abort(404);
        $plan = (object) $rows[0];
        $plan->features = collect($this->supabase->select('plan_features', '*', ['pricing_plan_id' => $id]))
            ->map(fn($f) => (object) $f);
        return view('admin.pricing-plans.show', compact('plan'));
    }

    public function edit(int $id)
    {
        $rows = $this->supabase->select('pricing_plans', '*', ['id' => $id]);
        if (empty($rows)) abort(404);
        $plan = (object) $rows[0];
        $plan->features = collect($this->supabase->select('plan_features', '*', ['pricing_plan_id' => $id]))
            ->map(fn($f) => (object) $f);
        return view('admin.pricing-plans.edit', compact('plan'));
    }

    public function update(Request $request, int $id)
    {
        $rows = $this->supabase->select('pricing_plans', '*', ['id' => $id]);
        if (empty($rows)) abort(404);

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
            $allPlans = $this->supabase->select('pricing_plans', 'id');
            foreach ($allPlans as $p) {
                if ($p['id'] != $id) $this->supabase->update('pricing_plans', $p['id'], ['is_popular' => false]);
            }
        }

        $this->supabase->update('pricing_plans', $id, $data);

        // Update features
        foreach ($request->input('features', []) as $featureInput) {
            $key = trim((string) ($featureInput['feature_key'] ?? ''));
            if ($key === '') continue;

            $featureRows = $this->supabase->select('plan_features', 'id', ['pricing_plan_id' => $id, 'feature_key' => $key]);
            if (!empty($featureRows)) {
                $this->supabase->update('plan_features', $featureRows[0]['id'], [
                    'feature_label' => trim($featureInput['feature_label'] ?? ''),
                    'feature_value' => trim($featureInput['feature_value'] ?? ''),
                    'feature'       => trim($featureInput['feature_label'] ?? ''),
                    'is_available'  => isset($featureInput['is_available']),
                ]);
            }
        }

        return redirect('/admin/pricing-plans')->with('success', 'Pricing plan updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->supabase->deleteWhere('plan_features', ['pricing_plan_id' => $id]);
        $this->supabase->delete('pricing_plans', $id);
        return redirect('/admin/pricing-plans')->with('success', 'Pricing plan deleted successfully.');
    }
}
