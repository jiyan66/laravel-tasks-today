<?php
namespace App\Mail;
use App\Models\Task;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
class TaskCompleted extends Mailable
{
    public int $taskId;
    public string $taskTitle;
    public string $scheduledDate;
    public function __construct(Task $task)
    {
        // Snapshot the completed values; do not expose User/password data to the email.
        $this->taskId = (int) $task->getKey();
        $this->taskTitle = $task->title;
        $this->scheduledDate = $task->task_date->toDateString();
    }
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Task completed - Tasks for Today');
    }
    public function content(): Content
    {
        return new Content(view: 'emails.task-completed');
    }
}
