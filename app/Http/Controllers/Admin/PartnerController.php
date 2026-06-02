<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PartnerController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    private function getAvailableOrders(?int $excludeId = null): array
    {
        $all = $this->supabase->select('partners', 'id,order');
        $usedOrders = array_column(
            $excludeId ? array_filter($all, fn($p) => $p['id'] != $excludeId) : $all,
            'order'
        );
        $total = count($all);
        return array_values(array_diff(range(1, $total + 1), $usedOrders));
    }

    public function index()
    {
        $raw = $this->supabase->select('partners', '*');
        usort($raw, fn($a, $b) => ($a['order'] ?? 0) - ($b['order'] ?? 0));
        $partners = collect($raw)->map(fn($p) => (object) $p);
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

        $this->supabase->insert('partners', $data);
        return redirect('/admin/partners')->with('success', 'Partner added successfully.');
    }

    public function show(int $id)
    {
        $rows = $this->supabase->select('partners', '*', ['id' => $id]);
        if (empty($rows)) abort(404);
        $partner = (object) $rows[0];
        return view('admin.partners.show', compact('partner'));
    }

    public function edit(int $id)
    {
        $rows = $this->supabase->select('partners', '*', ['id' => $id]);
        if (empty($rows)) abort(404);
        $partner = (object) $rows[0];
        $availableOrders = $this->getAvailableOrders($id);
        return view('admin.partners.edit', compact('partner', 'availableOrders'));
    }

    public function update(Request $request, int $id)
    {
        $rows = $this->supabase->select('partners', '*', ['id' => $id]);
        if (empty($rows)) abort(404);
        $existing = $rows[0];

        $request->validate([
            'name'       => 'required|string|max:255',
            'logo'       => 'nullable|string|max:10',
            'order'      => 'required|integer|min:1',
            'logo_image' => 'nullable|image|mimes:png,jpg,jpeg,gif,webp|max:5120',
        ]);

        $data = $request->only('name', 'order', 'logo');
        $data['is_active'] = $request->input('status') === 'Active';

        if ($request->hasFile('logo_image')) {
            if (!empty($existing['logo_image']) && Storage::disk('public')->exists($existing['logo_image'])) {
                Storage::disk('public')->delete($existing['logo_image']);
            }
            $data['logo_image'] = $request->file('logo_image')->store('partners', 'public');
        }

        $this->supabase->update('partners', $id, $data);
        return redirect('/admin/partners')->with('success', 'Partner updated successfully.');
    }

    public function destroy(int $id)
    {
        $rows = $this->supabase->select('partners', 'id,logo_image', ['id' => $id]);
        if (!empty($rows) && !empty($rows[0]['logo_image'])) {
            if (Storage::disk('public')->exists($rows[0]['logo_image'])) {
                Storage::disk('public')->delete($rows[0]['logo_image']);
            }
        }
        $this->supabase->delete('partners', $id);
        return redirect('/admin/partners')->with('success', 'Partner deleted successfully.');
    }
}
