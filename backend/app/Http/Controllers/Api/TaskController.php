<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    //menampilkan list task 
    public function index(Request $request)
    {
        $query = Task::with(['category', 'project', 'creator']);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('search')) {
            $query->where('title', 'ilike', '%' . $request->search . '%');
        }

        $tasks = $query->latest()->get();

        return response()->json([
            'message' => 'Berhasil mengambil data task',
            'data' => $tasks
        ]);
    }

    //buat task baru
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
        ], [
            'project_id.required' => 'Project wajib dipilih.',
            'category_id.required' => 'Kategori task wajib dipilih.',
            'title.required' => 'Judul task wajib diisi.',
            'description.required' => 'Deskripsi task wajib diisi.',
            'due_date.required' => 'Tenggat waktu (due date) wajib diisi.',
            'due_date.date' => 'Format tanggal tidak valid.'
        ]);

        $task = Task::create([
            'project_id' => $request->project_id,
            'category_id' => $request->category_id,
            'created_by' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
        ]);

        return response()->json([
            'message' => 'Task berhasil dibuat',
            'data' => $task
        ], 201);
    }

    //menampilkan detail task
    public function show($id)
    {
        $task = Task::with(['category', 'project', 'creator'])->find($id);

        if (!$task) {
            return response()->json(['message' => 'Task tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail task',
            'data' => $task
        ]);
    }

    //update task
    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task tidak ditemukan'], 404);
        }

        $request->validate([
            'category_id' => 'sometimes|required|exists:categories,id',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'due_date' => 'sometimes|required|date',
        ], [
            'category_id.required' => 'Kategori task tidak boleh kosong.',
            'title.required' => 'Judul task tidak boleh kosong.',
            'description.required' => 'Deskripsi task tidak boleh kosong.',
            'due_date.required' => 'Tenggat waktu tidak boleh kosong.',
        ]);

        $task->update($request->only(['category_id', 'title', 'description', 'due_date']));

        return response()->json([
            'message' => 'Task berhasil diperbarui',
            'data' => $task
        ]);
    }

    //hapus task dengan soft deletes
    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task tidak ditemukan'], 404);
        }

        $task->update(['deleted_by' => Auth::id()]);
        
        $task->delete();

        return response()->json([
            'message' => 'Task berhasil dihapus'
        ]);
    }
}