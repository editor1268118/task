<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class TaskCommentAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;
    public $comment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task, TaskComment $comment)
    {
        $this->task = $task;
        $this->comment = $comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('New Comment on Task: ' . $this->task->task_no)
                    ->line($this->comment->user->name . ' commented on task **' . $this->task->title . '**.')
                    ->line('"' . Str::limit($this->comment->comment, 100) . '"')
                    ->action('View Discussion', route('tasks.show', $this->task));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'task_id' => $this->task->id,
            'task_no' => $this->task->task_no,
            'title' => $this->task->title,
            'message' => $this->comment->user->name . ' commented: ' . Str::limit($this->comment->comment, 30),
            'type' => 'comment',
        ];
    }
}
