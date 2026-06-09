<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use App\Services\TaskService;
use App\Http\Requests\StoreTaskCommentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskCommentController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Store a newly created comment in storage.
     */
    public function store(StoreTaskCommentRequest $request, Task $task)
    {
        $request->task = $task; // Required to pass to Policy logic via request mapping
        
        $this->taskService->addComment($task, $request->comment, Auth::user());

        return back()->with('success', 'Comment added successfully.');
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(TaskComment $comment)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::id() === $comment->user_id) {
            $comment->delete();
            return back()->with('success', 'Comment deleted successfully.');
        }

        abort(403, 'Unauthorized action.');
    }
}
