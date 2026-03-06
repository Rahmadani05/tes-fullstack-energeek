<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    //fetch project
    public function index(Request $request)
    {
        $query = Project::query();

        if ($request->has('search')) {
            $query->where('name', 'ilike', '%' . $request->search . '%');
        }

        $projects = $query->with('creator')->latest()->get();

        return response()->json([
            'message' => 'Berhasil mengambil data project',
            'data' => $projects
        ]);
    }

    //buat project baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'nullable|in:active,archived'
        ], [
            'name.required' => 'Nama project wajib diisi.',
            'description.required' => 'Deskripsi project wajib diisi.',
            'status.in' => 'Status hanya boleh active atau archived.'
        ]);

        $project = Project::create([
            'created_by' => Auth::id(), //ambil dari token user login
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 'active',
        ]);

        return response()->json([
            'message' => 'Project berhasil dibuat',
            'data' => $project
        ], 201);
    }

    //mengambil detail project
    public function show($id)
    {
        $project = Project::with(['tasks.category', 'creator'])->find($id);

        if (!$project) {
            return response()->json(['message' => 'Project tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail project',
            'data' => $project
        ]);
    }

    //update project (active/archive)
    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json(['message' => 'Project tidak ditemukan'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:active,archived'
        ], [
            'name.required' => 'Nama project tidak boleh kosong.',
            'description.required' => 'Deskripsi project tidak boleh kosong.',
            'status.in' => 'Status hanya boleh active atau archived.'
        ]);

        $project->update($request->only(['name', 'description', 'status']));

        return response()->json([
            'message' => 'Project berhasil diupdate',
            'data' => $project
        ]);
    }
}