<?php

namespace App\Http\Controllers\Api;

use App\Exports\TaskExport;
use App\Http\Controllers\Controller;
use App\Imports\TaskImport;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class TaskController extends Controller
{
    /**
     * List tasks for current user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        try {
            $tasks = $user->role === 'admin'
                ? Task::with('assignedUser')->latest()->get()
                : Task::with('assignedUser')->where('assigned_to', $user->id)->latest()->get();

            return response()->json($tasks);
        } catch (\Exception $e) {
            Log::error('Failed to fetch tasks', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch tasks'], 500);
        }
    }

    /**
     * Create a new task — Admin only
     */
    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'assigned_to' => 'required|exists:users,id',
            'status'      => 'required|in:pending,in_progress,completed',
            'due_date'    => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $task = Task::create($validator->validated());

            return response()->json([
                'message' => 'Task created successfully',
                'task'    => $task,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Task creation failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create task'], 500);
        }
    }

    /**
     * Update task — Admin or Member
     */
    public function update(Request $request, $id)
    {
        try {
            $task = Task::findOrFail($id);
            $user = $request->user();

            // Member: can only update status of their own task
            if ($user->role === 'member') {
                if ($task->assigned_to !== $user->id) {
                    return response()->json(['message' => 'Unauthorized access'], 403);
                }

                $validator = Validator::make($request->all(), [
                    'status' => 'required|in:pending,in_progress,completed',
                ]);

                if ($validator->fails()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }

                $task->update(['status' => $request->status]);

                return response()->json(['message' => 'Task status updated']);
            }

            // Admin: full update access
            $validator = Validator::make($request->all(), [
                'title'       => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'assigned_to' => 'sometimes|exists:users,id',
                'status'      => 'sometimes|in:pending,in_progress,completed',
                'due_date'    => 'sometimes|date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $task->update($validator->validated());

            return response()->json([
                'message' => 'Task updated successfully',
                'task'    => $task
            ]);
        } catch (\Exception $e) {
            Log::error('Task update failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to update task'], 500);
        }
    }


    
public function export(Request $request)
{
    return Excel::download(new TaskExport($request->user()), 'tasks.xlsx');
}



public function import(Request $request)
{
    if ($request->user()->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $request->validate([
        'file' => 'required|mimes:xlsx,xls',
    ]);

    try {
        Excel::import(new TaskImport, $request->file('file'));

        return response()->json(['message' => 'Tasks imported successfully']);
    } catch (\Exception $e) {
        Log::error('Import failed: ' . $e->getMessage());
        return response()->json(['message' => 'Failed to import tasks'], 500);
    }
}


    /**
     * Soft delete task — Admin only
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $task = Task::findOrFail($id);
            $task->delete();

            return response()->json(['message' => 'Task soft-deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to delete task', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to delete task'], 500);
        }
    }

    /**
     * View all soft-deleted tasks — Admin only
     */
    public function deletedTasks(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $tasks = Task::onlyTrashed()->with('assignedUser')->get();
            return response()->json($tasks);
        } catch (\Exception $e) {
            Log::error('Failed to fetch deleted tasks', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to fetch deleted tasks'], 500);
        }
    }

    /**
     * Restore a soft-deleted task — Admin only
     */
    public function restore(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $task = Task::onlyTrashed()->findOrFail($id);
            $task->restore();

            return response()->json(['message' => 'Task restored successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to restore task', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to restore task'], 500);
        }
    }

    /**
     * Permanently delete a task — Admin only
     */
    public function forceDelete(Request $request, $id)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        try {
            $task = Task::onlyTrashed()->findOrFail($id);
            $task->forceDelete();

            return response()->json(['message' => 'Task permanently deleted']);
        } catch (\Exception $e) {
            Log::error('Failed to permanently delete task', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to permanently delete task'], 500);
        }
    }
}
