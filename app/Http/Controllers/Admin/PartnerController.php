<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'logo'  => 'nullable|string|max:10',
            'order' => 'required|integer|min:1',
            'logo_image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:5120',
        ]);

        $data = $request->only('name', 'order', 'logo');
        $data['is_active'] = true;

        if ($request->hasFile('logo_image')) {
            $file = $request->file('logo_image');
            $path = $file->store('partners', 'public');
            $data['logo_image'] = $path;
        }

        Partner::create($data);

        return redirect('/admin/partners')->with('success', 'Partner added successfully.');
    }

    public function show(string $id)
    {
        $partner = Partner::findOrFail($id);
        return view('admin.partners.show', compact('partner'));
    }

    public function edit(string $id)
    {
        $partner = Partner::findOrFail($id);
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, string $id)
    {
        $partner = Partner::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'logo'  => 'nullable|string|max:10',
            'order' => 'required|integer|min:1',
            'logo_image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:5120',
        ]);

        $data = $request->only('name', 'order', 'logo');
        $data['is_active'] = $request->input('status') === 'Active';

        if ($request->hasFile('logo_image')) {
            // Delete old image if exists
            if ($partner->logo_image && Storage::disk('public')->exists($partner->logo_image)) {
                Storage::disk('public')->delete($partner->logo_image);
            }

            $file = $request->file('logo_image');
            $path = $file->store('partners', 'public');
            $data['logo_image'] = $path;
        }

        $partner->update($data);

        return redirect('/admin/partners')->with('success', 'Partner updated successfully.');
    }

    public function destroy(string $id)
    {
        $partner = Partner::findOrFail($id);
        
        // Delete logo image if exists
        if ($partner->logo_image && Storage::disk('public')->exists($partner->logo_image)) {
            Storage::disk('public')->delete($partner->logo_image);
        }
        
        $partner->delete();

        return redirect('/admin/partners')->with('success', 'Partner deleted successfully.');
    }
}