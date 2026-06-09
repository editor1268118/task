<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\TaskService;
use App\Http\Requests\StoreTaskAttachmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskAttachmentController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * Store a newly created attachment in storage.
     */
    public function store(StoreTaskAttachmentRequest $request, Task $task)
    {
        $request->task = $task; // Required to pass to Policy logic via request mapping
        
        $this->taskService->uploadAttachment($task, $request->file('file'), Auth::user());

        return back()->with('success', 'File attached successfully.');
    }

    /**
     * Download the specified attachment.
     */
    public function download(TaskAttachment $attachment)
    {
        // Must have access to view the task
        $this->authorize('view', $attachment->task);

        $attachment->increment('download_count');

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }

    /**
     * Remove the specified attachment from storage.
     */
    public function destroy(TaskAttachment $attachment)
    {
        if (Auth::user()->hasRole('super-admin') || Auth::id() === $attachment->uploaded_by) {
            // Soft delete
            $attachment->delete();
            return back()->with('success', 'Attachment deleted successfully.');
        }

        abort(403, 'Unauthorized action.');
    }
}
