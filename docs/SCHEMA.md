# Database and MVC structure

## Tables

| Table | Field | Migration type | Rule |
| --- | --- | --- | --- |
| users | id | id / unsigned big integer | Auto-increment primary key |
| users | username | string(50) | Required and unique |
| users | full_name | string(100) | Required |
| users | email | string(100) | Required |
| users | created_at, updated_at | timestamps | Maintained by Eloquent |
| tasks | id | id / unsigned big integer | Auto-increment primary key |
| tasks | title | string(150) | Required |
| tasks | status | string(20) | Required; default pending |
| tasks | task_date | date | Required; indexed for filtering |
| tasks | created_at, updated_at | timestamps | Maintained by Eloquent |

Users include both Eloquent timestamps. No password or authentication fields are
needed for this profile-only activity. The assignment provides one demo user and
a shared task list, so it does not require a task owner foreign key.

`task_date` is cast to a date on the Task model. Views format the date for display;
the controller and query scope select records. The database supplies the default
status when no status is passed.

## Responsibilities

| Component | Files | Responsibility |
| --- | --- | --- |
| Routes | routes/web.php | Map the four GET URLs to controller methods |
| Controllers | TaskController, ProfileController, PageController | Retrieve records and select a view |
| Models | Task, User | Eloquent access, fillable fields, task date casting and scope |
| Migrations | database/migrations | Create the schema |
| Seeders | DatabaseSeeder, TaskSeeder, UserSeeder | Populate persistent demo data |
| Views | resources/views | Render escaped data and shared navigation |
| Styles | public/css/app.css | Responsive appearance without an asset build |

Seed arrays are temporary input to database inserts/updates. Controllers never
use static task arrays as their displayed data source.

The MySQL import also records both migrations in the `migrations` table so a
later Artisan migration run can recognize the imported schema.
