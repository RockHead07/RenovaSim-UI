<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function index()
    {
        $partners = Partner::orderBy('order')->get();
        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'logo'  => 'nullable|image|mimes:png,jpg,svg|max:2048',
            'order' => 'integer|min:0',
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
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, string $id)
    {
        $partner = Partner::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'logo'  => 'nullable|image|mimes:png,jpg,svg|max:2048',
            'order' => 'integer|min:0',
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