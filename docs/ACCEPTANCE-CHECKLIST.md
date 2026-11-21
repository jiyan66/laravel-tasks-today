# TSA4 acceptance checklist

## Existing public behavior

- Welcome shows only today's non-archived tasks, based on APP_TIMEZONE=Asia/Manila.
- Task List displays all non-archived tasks ordered by date then ID.
- Profile displays the configured demo user and remains accessible without login.
- About identifies the developer and TSA4. All four pages have working navigation.
- A fresh demo seed has nine tasks over three dates and one user. Existing live
  data counts can differ after real CRUD; the upgrader does not reset those counts.

## Authentication and task management

- Logged-out visitors can view the four pages, login and registration.
- GET /tasks/create and /tasks/ID/edit redirect guests to login.
- Guest POST/PUT/PATCH/DELETE attempts cannot change records or send mail.
- Login returns the visitor to the originally requested protected page.
- Registration requires username/full_name, unique lowercase email and confirmed
  password. Login uses EMAIL, not username. No shared/default password is supplied.
- Logged-in users can create and edit valid tasks. Invalid/missing fields, unknown
  statuses, titles over 150 characters and impossible dates show errors/old input.
- Forms include CSRF tokens; update/archive forms specify their HTTP method.
- Archiving retains the row with is_archived=1, hides it from both public lists,
  and rejects direct edit/update/repeated archive requests for that record.
- Logout uses the form, invalidates the session and blocks protected pages again.
- The public profile remains the demo profile when a different user logs in.

## Completion notification

- Use MAIL_MAILER=log and a configured TASK_COMPLETION_RECIPIENT.
- Pending -> Done in Edit generates a log message with correct task details.
- Saving Done again (including a title/date edit) generates no new completion mail.
- Invalid updates, archiving and ordinary non-completion edits send nothing.
- See TSA4-MAIL.md for exact demonstration steps and optional sandbox SMTP.

## Verification and submission

- Run VERIFY.ps1 successfully on the development machine.
- Repeat public/protected access, task CRUD/archive and mail-log checks on hosting.
- Commit complete generated Breeze source plus custom changes and BOTH lock files.
- Keep migrations/seeders in Git; keep .env, data, private logs and backups out.
- README explains setup and mail configuration, and contains actual GitHub and live URLs.
- No claims of a live deployment should be made before testing the hosted site.
