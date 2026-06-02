<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    public function index()
    {
        $materials = Material::orderBy('category')->orderBy('name')->get();
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

        Material::create($request->only('name', 'category', 'price_per_unit', 'unit'));
        return redirect('/admin/materials')->with('success', 'Material added successfully.');
    }

    public function show(int $id)
    {
        $material = Material::findOrFail($id);
        return view('admin.materials.show', compact('material'));
    }

    public function edit(int $id)
    {
        $material = Material::findOrFail($id);
        return view('admin.materials.edit', compact('material'));
    }

    public function update(Request $request, int $id)
    {
        $material = Material::findOrFail($id);

        $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|string|max:255',
            'price_per_unit' => 'required|numeric|min:0',
            'unit'           => 'required|string|max:50',
        ]);

        $material->update($request->only('name', 'category', 'price_per_unit', 'unit'));
        return redirect('/admin/materials')->with('success', 'Material updated successfully.');
    }

    public function destroy(int $id)
    {
        Material::findOrFail($id)->delete();
        return redirect('/admin/materials')->with('success', 'Material deleted successfully.');
    }
}
