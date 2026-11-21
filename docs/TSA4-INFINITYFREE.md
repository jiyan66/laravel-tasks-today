# Upgrade the existing hosted TSA3 Tasks application

Complete and verify TSA4 locally first. Use your TASKS website/database, preserving
the separate POS website. Export the existing hosted MySQL database privately and
back up hosted files before the upgrade. The update ZIP alone is not deployable:
use the fully upgraded local project with its generated Breeze files and locks.

## Database

Select the existing Tasks database in phpMyAdmin. Check for duplicate emails:

```sql
SELECT email, COUNT(*) FROM users GROUP BY email HAVING COUNT(*) > 1;
```

It should return no rows. Resolve any conflicting account emails before proceeding.
Import database/sql/tsa4-upgrade.sql ONCE. It adds auth/archive fields and a token
table, converts completed to done, and records the migration without deleting data
or assigning passwords. Do not rerun the original TSA3 import into populated tables.
For a brand-new empty Tasks database, import the original infinityfree-import.sql
and then tsa4-upgrade.sql. Use SQL OR artisan migrate for a database, never both.
If an import partly fails, inspect which statements completed before attempting
further changes; do not blindly rerun all ALTER/CREATE statements.

## Deployment copy

Copy the completed development project to a separate folder. In that COPY:

```powershell
. .\scripts\Use-TaskPhp.ps1
npm.cmd ci
npm.cmd run build
php artisan optimize:clear
composer install --no-dev --prefer-dist
```

Check public/build/manifest.json and its referenced asset files exist. The original
TSA3 instruction that npm was unnecessary no longer applies: Breeze needs the built
authentication CSS/JavaScript. Node and Breeze's installer are used locally, not run
on the host. Published Breeze source works with production vendor dependencies.
Do not run tests in the no-dev deployment copy.

## Upload and environment

Upload the updated app, config, routes, resources, public and production vendor
contents into their matching locations under the already-working domain htdocs.
Include actual Breeze Auth controllers, LoginRequest, View components, auth.php,
auth views/components, the custom PublicProfileController and built public/build.
Keep the working root .htaccess, public/.htaccess and public/index.php layout.
Upload composer.json and the generated composer.lock with the production vendor.
Do not add another outer project folder inside htdocs.

Preserve the live .env and APP_KEY/MySQL connection. Edit only needed settings:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://YOUR_ACTUAL_TASKS_SITE
APP_TIMEZONE=Asia/Manila
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
MAIL_LOG_CHANNEL=single
TASK_COMPLETION_RECIPIENT=tasks@example.test
DEMO_PROFILE_USERNAME=gian.mistica
```

Use secure cookies with working HTTPS. Do not replace the hosted .env with local
SQLite configuration or commit the server credentials. The log driver produces
private evidence without requiring outbound SMTP. Check storage/logs/laravel.log
through private hosting tools; do not create a public log or artisan execution page.

Do not overwrite runtime storage with local logs/sessions. Preserve writable
bootstrap/cache and storage/framework/{cache/data,sessions,views} and storage/logs.
If stale generated caches exist, remove only bootstrap/cache/config.php and
bootstrap/cache/routes-*.php, plus compiled .php views in storage/framework/views.
Keep those directories. Remove stale public/hot so Vite uses the build manifest.
Do not upload .git, tests, node_modules, SQLite files, private backups or local .env.
Remove any deployment ZIP after extraction and check that no uploads failed.

## Login and hosted checks

The preserved demo profile has no password unless you explicitly provisioned one.
The simplest hosted login setup is /register with a NEW username/email/password.
Alternatively set the legacy password locally and deploy the resulting hash through
a private database export/import under your control. Never paste plaintext passwords
into SQL or a public setup endpoint. Registration does not change the public demo
profile; additional login accounts are allowed after the one-user demo seed.

Logged out, all four original pages must open; Create/Edit must redirect to login.
After login, create/edit/archive a temporary task, confirm its retained archived
row and hidden listing, and demonstrate Pending -> Done generates one private log
email while another Done edit generates none. Login styling must load without a
Vite manifest error. Verify the hosted code matches the source before submission.

Commit the complete upgraded Tasks repository normally, including generated
Breeze source, both lock files, migrations/seeders and docs/TSA4-INSTALLATION.json.
Review git status/staged files for secrets first. A GitHub push does not update
InfinityFree automatically. Fill the root README's two submission URLs after testing.
