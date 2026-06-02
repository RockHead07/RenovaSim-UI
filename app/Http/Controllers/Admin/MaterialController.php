<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    public function index()
    {
        $raw = $this->supabase->select('materials', '*');
        usort($raw, fn($a, $b) => strcmp($a['category'] ?? '', $b['category'] ?? ''));
        $materials = collect($raw)->map(fn($m) => (object) $m);
        return view('admin.materials.index', compact('materials'));
    }

    public function create()
    {
        return view('admin.materials.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|string|max:255',
            'price_per_unit' => 'required|numeric|min:0',
            'unit'           => 'required|string|max:50',
        ]);

        $this->supabase->insert('materials', $request->only('name', 'category', 'price_per_unit', 'unit'));
        return redirect('/admin/materials')->with('success', 'Material added successfully.');
    }

    public function show(int $id)
    {
        $rows = $this->supabase->select('materials', '*', ['id' => $id]);
        if (empty($rows)) abort(404);
        $material = (object) $rows[0];
        return view('admin.materials.show', compact('material'));
    }

    public function edit(int $id)
    {
        $rows = $this->supabase->select('materials', '*', ['id' => $id]);
        if (empty($rows)) abort(404);
        $material = (object) $rows[0];
        return view('admin.materials.edit', compact('material'));
    }

    public function update(Request $request, int $id)
    {
        if (empty($this->supabase->select('materials', 'id', ['id' => $id]))) abort(404);

        $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|string|max:255',
            'price_per_unit' => 'required|numeric|min:0',
            'unit'           => 'required|string|max:50',
        ]);

        $this->supabase->update('materials', $id, $request->only('name', 'category', 'price_per_unit', 'unit'));
        return redirect('/admin/materials')->with('success', 'Material updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->supabase->delete('materials', $id);
        return redirect('/admin/materials')->with('success', 'Material deleted successfully.');
    }
}
