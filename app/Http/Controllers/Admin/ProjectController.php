<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupabaseService;

class ProjectController extends Controller
{
    public function __construct(protected SupabaseService $supabase) {}

    public function index()
    {
        $raw = $this->supabase->select('projects', '*');
        $users = $this->supabase->select('users', 'id,username,email');
        $userMap = array_column($users, null, 'id');

        usort($raw, fn($a, $b) => strcmp($b['created_at'] ?? '', $a['created_at'] ?? ''));

        $projects = collect($raw)->map(function ($p) use ($userMap) {
            $u = $userMap[$p['user_id']] ?? null;
            $p['user'] = $u ? (object) $u : null;
            $p['estimations_count'] = 0;
            return (object) $p;
        });

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.create');
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'room_type' => 'required|string|max:255',
            'area_size' => 'required|numeric|min:1',
        ]);

        $this->supabase->insert('projects', [
            'user_id'   => auth()->id(),
            'name'      => $request->name,
            'room_type' => $request->room_type,
            'area_size' => (float) $request->area_size,
            'status'    => 'draft',
        ]);

        return redirect('/admin/projects')->with('success', 'Project added successfully.');
    }

    public function show(int $id)
    {
        $rows = $this->supabase->select('projects', '*', ['id' => $id]);
        if (empty($rows)) abort(404);
        $project = (object) $rows[0];
        return view('admin.projects.show', compact('project'));
    }

    public function edit(int $id)
    {
        $rows = $this->supabase->select('projects', '*', ['id' => $id]);
        if (empty($rows)) abort(404);
        $project = (object) $rows[0];
        return view('admin.projects.edit', compact('project'));
    }

    public function update(\Illuminate\Http\Request $request, int $id)
    {
        $rows = $this->supabase->select('projects', 'id', ['id' => $id]);
        if (empty($rows)) abort(404);

        $request->validate([
            'name'      => 'required|string|max:255',
            'room_type' => 'required|string|max:255',
            'area_size' => 'required|numeric|min:1',
        ]);

        $this->supabase->update('projects', $id, $request->only('name', 'room_type', 'area_size'));
        return redirect('/admin/projects')->with('success', 'Project updated successfully.');
    }

    public function destroy(int $id)
    {
        $this->supabase->deleteWhere('estimations', ['project_id' => $id]);
        $this->supabase->delete('projects', $id);
        return redirect('/admin/projects')->with('success', 'Project deleted successfully.');
    }
}
