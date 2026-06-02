<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    private function getAvailableOrders(?int $excludeId = null): array
    {
        $query = Partner::select('id', 'order');
        if ($excludeId) $query->where('id', '!=', $excludeId);
        $usedOrders = $query->pluck('order')->toArray();
        $total = Partner::count();
        return array_values(array_diff(range(1, $total + 1), $usedOrders));
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
            'name'       => 'required|string|max:255',
            'logo'       => 'nullable|string|max:10',
            'order'      => 'required|integer|min:1',
            'logo_image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:5120',
        ]);

        $data = $request->only('name', 'order', 'logo');
        $data['is_active'] = true;

        if ($request->hasFile('logo_image')) {
            $data['logo_image'] = $request->file('logo_image')->store('partners', 'public');
        }

        Partner::create($data);
        return redirect('/admin/partners')->with('success', 'Partner added successfully.');
    }

    public function show(int $id)
    {
        $partner = Partner::findOrFail($id);
        return view('admin.partners.show', compact('partner'));
    }

    public function edit(int $id)
    {
        $partner = Partner::findOrFail($id);
        $availableOrders = $this->getAvailableOrders($id);
        return view('admin.partners.edit', compact('partner', 'availableOrders'));
    }

    public function update(Request $request, int $id)
    {
        $partner = Partner::findOrFail($id);

        $request->validate([
            'name'       => 'required|string|max:255',
            'logo'       => 'nullable|string|max:10',
            'order'      => 'required|integer|min:1',
            'logo_image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:5120',
        ]);

        $data = $request->only('name', 'order', 'logo');
        $data['is_active'] = $request->input('status') === 'Active';

        if ($request->hasFile('logo_image')) {
            if ($partner->logo_image && Storage::disk('public')->exists($partner->logo_image)) {
                Storage::disk('public')->delete($partner->logo_image);
            }
            $data['logo_image'] = $request->file('logo_image')->store('partners', 'public');
        }

        $partner->update($data);
        return redirect('/admin/partners')->with('success', 'Partner updated successfully.');
    }

    public function destroy(int $id)
    {
        $partner = Partner::findOrFail($id);
        if ($partner->logo_image && Storage::disk('public')->exists($partner->logo_image)) {
            Storage::disk('public')->delete($partner->logo_image);
        }
        $partner->delete();
        return redirect('/admin/partners')->with('success', 'Partner deleted successfully.');
    }
}
