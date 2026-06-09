<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;
    public $changerName;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Task $task, User $changer, string $oldStatus, string $newStatus)
    {
        $this->task = $task;
        $this->changerName = $changer->name;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
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
                    ->subject('Task Status Updated: ' . $this->task->task_no)
                    ->line('The status of task **' . $this->task->title . '** has been updated by ' . $this->changerName . '.')
                    ->line('**Old Status:** ' . ucfirst(str_replace('_', ' ', $this->oldStatus)))
                    ->line('**New Status:** ' . ucfirst(str_replace('_', ' ', $this->newStatus)))
                    ->action('View Task', route('tasks.show', $this->task));
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
            'message' => $this->changerName . ' changed status to ' . ucfirst(str_replace('_', ' ', $this->newStatus)),
            'type' => 'status_update',
        ];
    }
}
