<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Show the full notification history for the current user.
     */
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark a specific notification as read and redirect to the related model.
     */
    public function read($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        if (!empty($notification->data['url'])) {
            return redirect($notification->data['url']);
        }

        if (isset($notification->data['task_id'])) {
            return redirect()->route('tasks.show', $notification->data['task_id']);
        }

        return back();
    }

    /**
     * Mark all notifications as read.
     */
    public function readAll()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back()->with('success', 'All notifications marked as read.');
    }
}
