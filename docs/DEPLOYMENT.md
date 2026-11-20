# Deploy the task application to InfinityFree

Complete local setup and run `composer test` before deployment. This process
requires your own InfinityFree account, website URL, and MySQL credentials.
None of those account-specific values are included in the source package.

## 1 Create a separate website and database

Create another free hosting account/subdomain for the task application, subject
to the account limits currently shown in your dashboard. Keep the existing POS
website intact. If no additional slot is available, decide on the destination
before replacing an existing website.

Create a NEW MySQL database from the hosting account's database section. Note
its full database name, username, password, and hostname. InfinityFree's database
host is the hostname it supplies, not localhost. Do not send these credentials
to another person or put them in GitHub.

Open phpMyAdmin for that database. Import:

```text
database/sql/infinityfree-import.sql
```

Use an empty database: the import deliberately creates tables without dropping
existing ones. It creates users, tasks, and migrations, then inserts one user and
nine tasks across three dates relative to the day of import in Philippine time.
Its final checks should report 9 tasks, 3 dates, 1 user, and 4 tasks today.

Do not import both the supplied SQL and a local database export into the same
populated database. Use one setup method. No hosted migration/seed web endpoint
is needed or included.

## 2 Prepare a deployment copy

Copy the entire locally working `laravel-tasks` folder to a separate sibling
folder named `laravel-tasks-deploy`. Do not make this copy inside the source
project. Open the deployment copy in VS Code and run:

```powershell
$env:Path = "C:\php;" + $env:Path
php artisan optimize:clear
composer install --no-dev --prefer-dist
```

Keep the source project's `composer.lock` in this copy. The install removes
test-only dependencies from the deployment copy while leaving your source
project's development installation intact. Composer's autoloader optimization
is disabled in this project's composer.json to reduce large generated class maps.

Do not run `composer test` in the production copy: PHPUnit is intentionally omitted.
No npm command is needed because CSS is already a regular public file.

## 3 Configure the hosted environment

Copy `deployment/infinityfree.env.example` over the deployment copy's `.env`.
Edit its placeholders with your actual values, including the APP_KEY from your
LOCAL `.env`. Never upload an empty or placeholder APP_KEY.

Keep these settings:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Manila
DB_CONNECTION=mysql
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

APP_URL must be the new tasks website's full URL. Configure and verify its SSL
certificate, then use an https URL with SESSION_SECURE_COOKIE=true. If initially
testing through http, temporarily use an http APP_URL and
SESSION_SECURE_COOKIE=false; switch both when HTTPS works.

Copy `deployment/htdocs.htaccess` to the ROOT of the deployment copy and rename
the copied file to `.htaccess`. It should contain:

```apache
RewriteEngine On
RewriteRule (.*) /public/$1 [L]
```

Keep `public/.htaccess` too. The root rule sends requests to public; the public
rule routes requests to Laravel. Do not replace public/index.php with custom
database credentials or move private source files into public.

## 4 Upload the application

Upload these items directly inside the new domain's htdocs directory:

```text
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
vendor/
.env
.htaccess
artisan
composer.json
composer.lock
```

For database/, the migration and seeder PHP files can be included; exclude the
local database.sqlite and SQL imports from the production copy. Keep SQL imports
in your SOURCE repository, not in public or the deployed website. You may also
omit database/ entirely from the upload after the MySQL import, since the four
runtime pages do not execute migrations or seeders.

Do not upload `.git`, tests, documentation, SETUP.ps1, phpunit.xml, node_modules,
or local session, compiled-view, cache, and log contents. Keep their runtime
directories, with the following locations present:

```text
bootstrap/cache/
storage/framework/cache/data/
storage/framework/sessions/
storage/framework/views/
storage/logs/
storage/app/private/
```

The result must be htdocs/public/index.php and htdocs/vendor/autoload.php, not
htdocs/laravel-tasks-deploy/public/index.php. Upload the CONTENTS of the copy.

If using the file manager's ZIP upload/extract feature, select those items inside
the copy, archive them, extract inside htdocs, and delete the uploaded ZIP
immediately afterwards. It contains the production .env. If browser uploads fail
with 400 or the archive exceeds its limits, use an FTP client with the account's
provided FTP settings. Check for failed transfers and hidden dotfiles.

## 5 Verify the hosted site

Visit the four URLs and repeat `docs/ACCEPTANCE-CHECKLIST.md` on the actual site.
Confirm the CSS loads. Requests to /.env, /composer.json, and
/storage/logs/laravel.log must not display private content.

Keep APP_DEBUG=false. If an error occurs, use the hosting file manager to read
storage/logs/laravel.log privately instead of showing stack traces publicly.

| Error | Check |
| --- | --- |
| 403 or host welcome page | Correct domain htdocs and root index/default welcome files |
| 404 on every Laravel page | Both .htaccess files, extraction folder, and public/index.php |
| Missing vendor/autoload.php | Composer installation and complete vendor upload |
| Database access denied | Exact DB_HOST, database name, username, and password from hosting |
| Table not found | Import the SQL into the same database configured in .env |
| Encryption key error | Actual APP_KEY copied to hosted .env |
| Cache/session write error | Required storage and bootstrap/cache directories exist and are writable |
| PHP file missing after upload | Check host file-size restrictions and failed transfers |
| Today is empty later | Dates are persistent; refresh the demo explicitly if needed |

Do not use recursive world-writable permissions as a general fix. Use the host's
normal writable-directory settings. Do not add a public command runner, seeder,
phpinfo, or debugging script to work around lack of terminal access.

## 6 Submit and update

Commit your application SOURCE to the new GitHub repository, including its
composer.lock, migrations, seeders, SQL import, README, and deployment guide.
The real production .env belongs only on the hosting account.

Add the actual GitHub and live website links to README before submission. Future
code changes must be committed and uploaded to keep the live copy consistent.

For a later classroom demonstration, run database/sql/refresh-demo-dates.sql
manually in the tasks database if you want the nine sample tasks centered on the
new date. This is optional; normally old dates remain old dates.

## Hosting reference

InfinityFree's Laravel guide documents the fixed htdocs web root, upload process,
terminal limitations, PHP file-size limit, and file-based session configuration:
<https://forum.infinityfree.com/t/how-to-install-a-laravel-site-on-infinityfree/118578>
