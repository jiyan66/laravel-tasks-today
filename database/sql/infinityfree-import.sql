-- IT0049 TSA3 - Tasks for Today - MySQL / MariaDB
-- Select a NEW, EMPTY tasks database in phpMyAdmin before importing.
-- This intentionally does not drop or delete existing tables.
-- Dates are relative to the day of import in Philippine time.

SET NAMES utf8mb4;
SET time_zone = '+08:00';
SET @demo_today = CURRENT_DATE();
SET @demo_now = NOW();

CREATE TABLE `users` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tasks` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(150) NOT NULL,
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    `task_date` DATE NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `tasks_task_date_index` (`task_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `migrations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `migration` VARCHAR(255) NOT NULL,
    `batch` INT NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

START TRANSACTION;

INSERT INTO `users` (`username`, `full_name`, `email`, `created_at`, `updated_at`) VALUES
('gian.mistica', 'Joseph Gian Carlo Mistica', 'gian.mistica@example.com', @demo_now, @demo_now);

INSERT INTO `tasks` (`title`, `status`, `task_date`, `created_at`, `updated_at`) VALUES
('Review Laravel MVC notes', 'completed', DATE_SUB(@demo_today, INTERVAL 1 DAY), @demo_now, @demo_now),
('Prepare the database schema', 'completed', DATE_SUB(@demo_today, INTERVAL 1 DAY), @demo_now, @demo_now),
('Run migrations and seeders', 'completed', @demo_today, @demo_now, @demo_now),
('Build the Welcome page', 'in_progress', @demo_today, @demo_now, @demo_now),
('Verify the task date filter', 'pending', @demo_today, @demo_now, @demo_now),
('Update the project README', 'pending', @demo_today, @demo_now, @demo_now),
('Check the hosted application', 'pending', DATE_ADD(@demo_today, INTERVAL 1 DAY), @demo_now, @demo_now),
('Review the profile details', 'pending', DATE_ADD(@demo_today, INTERVAL 1 DAY), @demo_now, @demo_now),
('Submit the repository and website links', 'pending', DATE_ADD(@demo_today, INTERVAL 1 DAY), @demo_now, @demo_now);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_01_01_000001_create_users_table', 1),
('2026_01_01_000002_create_tasks_table', 1);

COMMIT;

SELECT COUNT(*) AS task_count, COUNT(DISTINCT task_date) AS distinct_dates FROM tasks;
SELECT COUNT(*) AS user_count FROM users;
SELECT COUNT(*) AS tasks_today FROM tasks WHERE task_date = @demo_today;
-- Expected: 9 tasks, 3 dates, 1 user, 4 tasks today.
