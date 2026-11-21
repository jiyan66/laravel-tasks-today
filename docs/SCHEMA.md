# TSA4 schema changes

The original users/tasks migrations and existing rows are preserved. The new
migration is 2026_11_21_000003_add_tsa4_auth_and_archiving.php.

## Tasks

Keep id, title VARCHAR(150), status VARCHAR(20), task_date and timestamps.
Add indexed is_archived BOOLEAN NOT NULL DEFAULT false. Normal listings explicitly
query active records; archived rows remain accessible to database administration
but have no edit/update route access. The model does not use Laravel SoftDeletes
because the assignment requires is_archived rather than deleted_at.

Convert pre-existing status completed to done. Forms accept pending, in_progress
and done. Date values remain stored as dates. No notification is triggered by SQL,
migrations or seeds: the update action owns the notification rule.

## Users and password resets

Keep id, unique username VARCHAR(50), full_name VARCHAR(100), email VARCHAR(100)
and timestamps. Add a unique email index, nullable password VARCHAR(255), nullable
email_verified_at timestamp and nullable remember_token VARCHAR(100). The nullable
password preserves the demo profile without assigning a shared password. Such an
account cannot log in until explicitly provisioned. Password hashes/remember tokens
are hidden; registration requires credentials. Email verification is not enforced.

Add password_reset_tokens with email VARCHAR(100) as primary key, token VARCHAR(255)
and nullable created_at. The Breeze reset flow uses this table and a 60-minute expiry.
Migration stops for duplicate email records instead of merging/deleting them.

## Data and upgrades

TSA3 seeded nine tasks on three relative dates, including four on today, and one
demo user. The updated seeds keep these defaults but use status done. They preserve
other login accounts and existing passwords. They refresh demo task dates/statuses
if manually rerun, so the upgrade installer does not reseed existing installations.
The configured public demo username defaults to gian.mistica.

Use the new migration locally. For the already-hosted TSA3 MySQL database, apply
database/sql/tsa4-upgrade.sql ONCE instead. Do not apply both to the same database.
For a brand-new hosted database, import the original infinityfree-import.sql and
then tsa4-upgrade.sql, in that order. Neither SQL script contains a login password.
