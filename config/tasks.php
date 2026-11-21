<?php
return [
    // Safe demonstration destination when MAIL_MAILER=log; set explicitly for SMTP.
    'completion_recipient' => env('TASK_COMPLETION_RECIPIENT', 'tasks@example.test'),
    'profile_username' => env('DEMO_PROFILE_USERNAME', 'gian.mistica'),
];
