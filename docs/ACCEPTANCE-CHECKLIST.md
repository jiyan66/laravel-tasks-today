# Acceptance checklist

Run local setup and `composer test` first. The tests use a separate in-memory
database and require Composer's development dependencies.

## Required data

- [ ] Nine tasks exist in the application database.
- [ ] There are three distinct task dates after seeding.
- [ ] Four tasks have today's date in Asia/Manila.
- [ ] Exactly one user exists, with a unique username.
- [ ] The database default for task status is pending.

## Required pages

- [ ] `/` shows four tasks immediately after seeding, with no yesterday/tomorrow rows.
- [ ] `/tasks` shows all nine tasks in ascending date order.
- [ ] `/profile` shows the one seeded user's ID, username, full name, and email.
- [ ] `/about` identifies Joseph Gian Carlo Mistica as developer.
- [ ] All four navigation links work from every page.
- [ ] At mobile width, navigation wraps and task tables scroll within their panel.
- [ ] The current page is highlighted in navigation.
- [ ] The empty Today state works when no records match the current date.

## Hosted application

- [ ] The hosted copy uses a separate MySQL tasks database.
- [ ] APP_ENV is production, APP_DEBUG is false, and APP_URL matches the website.
- [ ] HTTPS works before SESSION_SECURE_COOKIE is set to true.
- [ ] `public/css/app.css` loads and all pages match the submitted source.
- [ ] `/.env`, `/composer.json`, `/database/sql/infinityfree-import.sql`,
      and `/storage/logs/laravel.log` do not reveal file contents.
- [ ] The uploaded archive has been deleted after extraction.
- [ ] PHP source larger than InfinityFree's file limit has not disappeared on upload.

## Submission

- [ ] GitHub repository contains application files, migrations, seeders, and README.
- [ ] The locally generated composer.lock is committed.
- [ ] No real .env, credentials, vendor directory, or database file is committed.
- [ ] README includes the actual repository URL and hosted URL.
- [ ] Both submitted links open successfully for the intended reviewer.

The completed POS application can remain in its own folder, repository, and
hosting account/subdomain throughout this work.
