<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * Get available order numbers (excluding taken ones)
     */
    private function getAvailableOrders(?int $excludeId = null): array
    {
        $query = Partner::query();
        
        // Exclude current partner when editing
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $usedOrders = $query->pluck('order')->toArray();
        $totalPartners = Partner::count();
        
        // Generate available order numbers (1 to totalPartners + 1)
        $allOrders = range(1, $totalPartners + 1);
        $availableOrders = array_diff($allOrders, $usedOrders);
        
        return array_values($availableOrders); // Reindex array
    }

    public function index()
    {
        $partners = Partner::orderBy('order')->get();
        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        $availableOrders = $this->getAvailableOrders();
        return view('admin.partners.create', compact('availableOrders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'logo'  => 'nullable|image|mimes:png,jpg,svg|max:2048',
            'order' => 'required|integer|min:1',
        ]);

        $data = $request->only('name', 'order', 'is_active');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('partners', 'public');
        }

        Partner::create($data);

        return redirect()->route('admin.partners.index')
                         ->with('success', 'Partner added successfully.');
    }

    public function show(string $id)
    {
        $partner = Partner::findOrFail($id);
        return view('admin.partners.show', compact('partner'));
    }

    public function edit(string $id)
    {
        $partner = Partner::findOrFail($id);
        $availableOrders = $this->getAvailableOrders($id);
        // Add current order to available if not there
        if (!in_array($partner->order, $availableOrders)) {
            $availableOrders[] = $partner->order;
        }
        sort($availableOrders);
        return view('admin.partners.edit', compact('partner', 'availableOrders'));
    }

    public function update(Request $request, string $id)
    {
        $partner = Partner::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'logo'  => 'nullable|image|mimes:png,jpg,svg|max:2048',
            'order' => 'required|integer|min:1',
        ]);

        $data = $request->only('name', 'order');
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('partners', 'public');
        }

        $partner->update($data);

        return redirect()->route('admin.partners.index')
                         ->with('success', 'Partner updated successfully.');
    }

    public function destroy(string $id)
    {
        $partner = Partner::findOrFail($id);
        $partner->delete();

        return redirect()->route('admin.partners.index')
                         ->with('success', 'Partner deleted successfully.');
    }
}