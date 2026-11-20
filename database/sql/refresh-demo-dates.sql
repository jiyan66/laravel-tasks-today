-- OPTIONAL: run manually in the TASKS database for a later classroom demo.
-- This changes only the nine demo tasks' dates; no task is automatically moved.
-- Normal use does NOT require this: older tasks correctly leave the Today page.
SET time_zone = '+08:00';
SET @demo_today = CURRENT_DATE();

UPDATE tasks SET
    task_date = CASE
        WHEN title IN ('Review Laravel MVC notes', 'Prepare the database schema')
            THEN DATE_SUB(@demo_today, INTERVAL 1 DAY)
        WHEN title IN ('Check the hosted application', 'Review the profile details', 'Submit the repository and website links')
            THEN DATE_ADD(@demo_today, INTERVAL 1 DAY)
        ELSE @demo_today
    END,
    updated_at = NOW()
WHERE title IN (
    'Review Laravel MVC notes', 'Prepare the database schema',
    'Run migrations and seeders', 'Build the Welcome page',
    'Verify the task date filter', 'Update the project README',
    'Check the hosted application', 'Review the profile details',
    'Submit the repository and website links'
);
