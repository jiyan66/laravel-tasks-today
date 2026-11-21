# Verify the completion notification

## Log transport recommended for this assessment

In the existing private .env, set these keys once (edit existing values):

```dotenv
MAIL_MAILER=log
MAIL_LOG_CHANNEL=single
MAIL_FROM_ADDRESS=noreply@example.test
MAIL_FROM_NAME="Tasks for Today"
TASK_COMPLETION_RECIPIENT=tasks@example.test
```

Run `php artisan config:clear` from a terminal using the correct PHP, then restart
the local server. A real email address, SMTP password and mail queue are unnecessary
for this mode. No email is sent to a real inbox. This is explicitly allowed by TSA4.

## Demonstrate the required flow

1. Log in and create a task with a distinctive title, such as TSA4 email test 01,
   status Pending and today's date.
2. Edit it to Done and save. Expect the completion-notification success message.
3. Inspect the private log in a separate project terminal:

```powershell
Get-Content .\storage\logs\laravel.log -Tail 120
```

4. Find the new message with subject Task completed, the configured To address,
   task ID, title and scheduled date. Count the new message, not the original text
   inside a single MIME body. Other log messages/password-reset emails may exist.
5. Keep its status Done, change the title, and save again. No additional completion
   message should appear. The success message now says only Task updated.
6. Invalid form submissions, archiving and non-completion edits must not generate
   a completion email. Existing completed rows normalized by migration send nothing.
7. Reopening to Pending and later setting Done creates another real completion
   transition and therefore another notification. This behavior is intentional.

Do not expose the log through a public route or commit it; it can include reset
links and other private information. On hosting, inspect it privately through the
hosting file manager/FTP. Keep APP_DEBUG=false on the live application.

## Optional test SMTP sandbox

Use the exact settings supplied by a mail sandbox provider, not a real recipient's
mailbox. The placeholders below are examples, not working credentials:

```dotenv
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=YOUR_SANDBOX_SMTP_HOST
MAIL_PORT=YOUR_SANDBOX_PORT
MAIL_USERNAME=YOUR_SANDBOX_USERNAME
MAIL_PASSWORD="YOUR_SANDBOX_PASSWORD"
MAIL_FROM_ADDRESS=noreply@example.test
TASK_COMPLETION_RECIPIENT=recipient@example.test
```

Match MAIL_SCHEME/port to the sandbox's instructions (for example smtps for implicit
TLS when specified). Clear cached config after edits. Hosting SMTP availability
is account-specific; the log transport remains sufficient for this assessment.
Never use actual SMTP credentials in committed files or example screenshots.

## Implementation and automated coverage

TaskController::update compares the latest locked stored status before saving.
Only non-done -> done calls Mail::to(config('tasks.completion_recipient'))->send(...).
TaskCompleted is a Mailable with a scalar snapshot of task details, rendered with
an escaped Blade email view. There is no background worker requirement.
A mail exception rolls back the update and retains form input; the private log
contains the diagnostic and the browser gets a generic error. A row lock prevents
two ordinary concurrent updates from both seeing the task as pending on MySQL.
External SMTP and database commit cannot guarantee exactly-once delivery under
all infrastructure failures; the default log/test setup is the assessment scope.

Feature tests use Mail::fake for transition/no-resend/recipient assertions and a
simulated transport failure to check rollback. A separate render assertion checks
email details and HTML escaping. Run VERIFY.ps1 for actual local execution, then
perform the log demonstration above with the running application.
