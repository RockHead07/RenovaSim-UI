<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('user')->latest()->get();
        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'room_type' => 'required|string|max:255',
            'area_size' => 'required|numeric|min:1',
        ]);

        Project::create([
            'user_id'    => auth()->id(),
            'name'       => $request->name,
            'room_type'  => $request->room_type,
            'area_size'  => $request->area_size,
            'total_cost' => 0,
            'status'     => 'draft',
        ]);

        return redirect()->route('admin.projects.index')
                         ->with('success', 'Project added successfully.');
    }

    public function show(string $id)
    {
        $project = Project::with(['user', 'materials'])->findOrFail($id);
        return view('admin.projects.show', compact('project'));
    }

    public function edit(string $id)
    {
        $project = Project::findOrFail($id);
        return view('admin.projects.edit', compact('project'));
    }

    public function update(Request $request, string $id)
    {
        $project = Project::findOrFail($id);

        $request->validate([
            'name'      => 'required|string|max:255',
            'room_type' => 'required|string|max:255',
            'area_size' => 'required|numeric|min:1',
            'status'    => 'in:draft,estimated,completed',
        ]);

        $project->update($request->only('name', 'room_type', 'area_size', 'status'));

        return redirect()->route('admin.projects.index')
                         ->with('success', 'Project updated successfully.');
    }

    public function destroy(string $id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return redirect()->route('admin.projects.index')
                         ->with('success', 'Project deleted successfully.');
    }
}