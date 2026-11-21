<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Task completed</title></head>
<body>
    <h1>A task has been completed</h1>
    <p>The following task changed to <strong>done</strong>.</p>
    <dl>
        <dt>Task ID</dt><dd>{{ $taskId }}</dd>
        <dt>Title</dt><dd>{{ $taskTitle }}</dd>
        <dt>Scheduled date</dt><dd>{{ $scheduledDate }}</dd>
    </dl>
    <p>Tasks for Today Management System</p>
</body></html>
