<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;
    public $assignerName;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->assignerName = $task->assigner ? $task->assigner->name : 'System Admin';
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // Using database notifications for internal system.
        // Mail can be added by uncommenting 'mail' if SMTP is configured.
        return ['database' /*, 'mail' */];
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
                    ->subject('New Task Assigned: ' . $this->task->task_no)
                    ->greeting('Hello ' . $notifiable->name . ',')
                    ->line('You have been assigned a new task by ' . $this->assignerName . '.')
                    ->line('**Task:** ' . $this->task->title)
                    ->line('**Priority:** ' . ucfirst($this->task->priority))
                    ->line('**Due Date:** ' . ($this->task->due_date ? $this->task->due_date->format('M d, Y') : 'Not set'))
                    ->action('View Task', route('tasks.show', $this->task))
                    ->line('Please log in to the system to review and start working on this task.');
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
            'message' => 'You were assigned a new task by ' . $this->assignerName,
            'type' => 'assigned',
        ];
    }
}
