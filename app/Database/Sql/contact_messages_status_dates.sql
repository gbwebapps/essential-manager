-- Apply once to an existing application database after taking a backup.
-- Previous read and archive times are unknown and remain null.
-- Composer does not run this script automatically.
alter table contact_messages
    add column read_at datetime null default null after created_at,
    add column archived_at datetime null default null after read_at;
