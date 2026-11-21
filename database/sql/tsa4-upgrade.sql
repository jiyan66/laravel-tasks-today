-- TSA4 upgrade ONLY: apply ONCE to your existing TSA3 TASKS MySQL database.
-- Back up first. Never apply to the POS database. No records are deleted.
-- Preflight: this must return ZERO rows; resolve duplicate emails before proceeding.
-- SELECT email, COUNT(*) FROM users GROUP BY email HAVING COUNT(*) > 1;
ALTER TABLE users
    ADD UNIQUE KEY users_email_unique (email),
    ADD COLUMN password VARCHAR(255) NULL,
    ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL,
    ADD COLUMN remember_token VARCHAR(100) NULL;
CREATE TABLE password_reset_tokens (
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE tasks
    ADD COLUMN is_archived TINYINT(1) NOT NULL DEFAULT 0,
    ADD KEY tasks_is_archived_index (is_archived);
UPDATE tasks SET status = 'done' WHERE status = 'completed';
INSERT INTO migrations (migration, batch)
SELECT '2026_11_21_000003_add_tsa4_auth_and_archiving', COALESCE(MAX(batch), 0) + 1
FROM (SELECT batch FROM migrations) AS previous_batches;
-- Existing users retain their IDs/email and have no assigned default password.
-- Register a new username/email or set a password for a legacy user LOCALLY,
-- then securely deploy that user hash through a private database export if needed.
