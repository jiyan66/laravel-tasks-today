# Tasks for Today Management System

A four-page Laravel 13 application for IT0049 Technical Summative Assessment 3.
Task and profile data are stored in a real database and retrieved with Eloquent.

Developer: Joseph Gian Carlo Mistica  
Course: BS Information Technology, Cybersecurity  
School: FEU Institute of Technology

## Pages

| URL | Page | Behavior |
| --- | --- | --- |
| `/` | Welcome / Today | Only tasks scheduled for today in Asia/Manila |
| `/tasks` | Task List | Every task, ordered by date ascending, then ID |
| `/profile` | Profile | The single seeded user |
| `/about` | About | Static project and developer information |

Navigation is shared across all four Blade views. Account and task text is
escaped with Blade's `{{ }}` output. The layout works on desktop and mobile.
The project includes no login or task-editing forms because the assignment
requires displaying and filtering records.

## Requirements

- PHP 8.3 or newer; the existing standalone `C:\php` PHP 8.4 setup is suitable.
- Composer 2.
- PDO SQLite for the default local database (`pdo_sqlite` and `sqlite3`).
- PDO MySQL for MySQL hosting (`pdo_mysql`).
- Standard Laravel extensions including Ctype, cURL, DOM, Fileinfo, Filter,
  Hash, Mbstring, OpenSSL, PCRE, PDO, Session, Tokenizer, and XML.
- ZIP support is recommended for Composer downloads.
- Git for the repository submission.

CSS is served directly from `public/css/app.css`. No Node.js installation,
Vite build, external font download, or MySQL server is needed for local SQLite use.

## Start on Windows

This archive is a separate project, not a folder to paste into `laravel-pos`.
Extract it so the path is `C:\xampp\htdocs\laravel-tasks\artisan`.
Keep the completed POS project and its database intact.

Open `C:\xampp\htdocs\laravel-tasks` in VS Code. In its PowerShell terminal:

```powershell
$env:Path = "C:\php;" + $env:Path
php -v
powershell -NoProfile -ExecutionPolicy Bypass -File .\SETUP.ps1
composer test
php artisan serve
```

`-ExecutionPolicy Bypass` applies only to that script process. Review the included
script before running it. It installs dependencies, creates a local `.env` when
missing, generates a key when missing, creates the SQLite file, and runs migrations
and seeders. It does not modify your global PHP installation or delete existing data.

Open <http://127.0.0.1:8000>. Keep that terminal running. If port 8000 is occupied
by the POS app, stop that server with Ctrl+C, or use:

```powershell
php artisan serve --port=8001
```

Then open <http://127.0.0.1:8001>.

Composer dependencies and `composer.lock` are not preinstalled in the downloaded
source. The initial `composer install` resolves Laravel 13 and creates the lock
file. Include the generated `composer.lock` in your GitHub commit.

## Manual setup instead of SETUP.ps1

In the project directory, run:

```powershell
$env:Path = "C:\php;" + $env:Path
Copy-Item .env.example .env
New-Item -ItemType File database\database.sqlite
composer install --prefer-dist
php artisan key:generate
php artisan optimize:clear
php artisan migrate --seed
composer test
php artisan serve
```

Copy `.env.example` and create the SQLite file only if those files do not already
exist. Do not overwrite an existing configured `.env`.

## What the seeder creates

| Scheduled date | Tasks |
| --- | ---: |
| Yesterday | 2 |
| Today | 4 |
| Tomorrow | 3 |
| Total | 9 |

Exactly one profile is seeded:

- Username: `gian.mistica`
- Full name: Joseph Gian Carlo Mistica
- Email: `gian.mistica@example.com` (sample address)

The default task status is `pending`. Example statuses include `completed`
and `in_progress`. Re-running `php artisan db:seed` updates the nine known demo
titles relative to the current date without duplicating them. It also updates the
same profile. If another username exists, the seeder stops and preserves those
records. Use a dedicated tasks database rather than an existing POS database.

## How today works

`APP_TIMEZONE=Asia/Manila` controls the calendar day used by the Welcome query
and by the seeder. The controller applies a date filter in the database through
the `Task` model's `dueOn` scope. The full list is retrieved separately with
`orderBy('task_date')->orderBy('id')`.

Dates are persistent. A task for yesterday does not silently become a task for
today. The Welcome page can legitimately become empty after the seeded dates
have passed. To refresh the sample schedule for a later local demonstration:

```powershell
php artisan db:seed
```

On hosting, the optional `database/sql/refresh-demo-dates.sql` performs that
explicit demo refresh through phpMyAdmin. It is never run by a public web route.

## Use local MySQL instead

Start MySQL in XAMPP and create a new empty database called `laravel_tasks`.
Change these entries in your local `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_tasks
DB_USERNAME=root
DB_PASSWORD=
```

Use your actual local credentials if different. Then run:

```powershell
php artisan config:clear
php artisan migrate --seed
```

The migration and seeder files support both SQLite and MySQL. The database types
and the MVC responsibilities are documented in `docs/SCHEMA.md`.

## Verification

```powershell
php artisan route:list --except-vendor
composer test
```

Tests use an isolated in-memory SQLite database; they do not use your local or
hosted task database. The 11 feature tests cover seed counts and dates, today's
filter, chronological ordering, the profile and About page, repeat seeding,
empty results, default status, unique usernames, escaped titles, and preservation
of unrelated users. The manual checklist is in `docs/ACCEPTANCE-CHECKLIST.md`.

PHP and Composer were unavailable in the package-authoring environment, so the
PHPUnit suite must be run on your PHP-equipped computer. Do not treat the
included tests as a claim that runtime execution has already passed.

## InfinityFree deployment

Follow `docs/DEPLOYMENT.md`. It covers a separate hosting account/subdomain,
creating a MySQL database, importing `database/sql/infinityfree-import.sql`,
production environment settings, the two `.htaccess` files, and final checks.

Do not upload the downloaded source ZIP directly as the final website. Run
Composer first so the deployment copy includes production `vendor` dependencies.
Do not replace the existing POS website unless you explicitly intend to retire it.

## GitHub submission

Create a new empty GitHub repository, then run these commands from this project:

```powershell
git init
git add .
git status
git commit -m "Build Laravel Tasks for Today management system"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/laravel-tasks.git
git push -u origin main
```

Substitute your actual repository URL. Before committing, confirm `.env`,
`vendor`, the SQLite database, logs, and deployment ZIPs are excluded; migrations,
seeders, `.env.example`, SQL import files, and `composer.lock` should be included.

Add your real GitHub and live website URLs to this README before submission.
The application source and migrations/seeders satisfy the source-file deliverable;
the hosted URL must be created and checked separately.

## Troubleshooting

| Symptom | Action |
| --- | --- |
| PHP 8.2 appears | Prepend `C:\php` to PATH in the current terminal; confirm `composer --version` also uses PHP 8.4. |
| Could not find driver | Enable `pdo_sqlite` for SQLite, or `pdo_mysql` for MySQL, in the PHP configuration loaded by `php --ini`. |
| No application encryption key | Run `php artisan key:generate`. |
| No such table: tasks | Run `php artisan migrate --seed` against the intended database. |
| Welcome page empty | Check the full list, dates, and timezone; reseed only when deliberately refreshing demo data. |
| Missing vendor/autoload.php | Run `composer install`. |
| Other users already exist | Select a new dedicated database; do not delete your POS data. |
| Error after changing `.env` | Run `php artisan config:clear` locally. |

## References

- Laravel migrations: <https://laravel.com/docs/13.x/migrations>
- Laravel seeders: <https://laravel.com/docs/13.x/seeding>
- Eloquent: <https://laravel.com/docs/13.x/eloquent>
- Laravel testing: <https://laravel.com/docs/13.x/testing>
- InfinityFree deployment guide: <https://forum.infinityfree.com/t/how-to-install-a-laravel-site-on-infinityfree/118578>
