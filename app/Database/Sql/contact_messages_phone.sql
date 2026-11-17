-- Apply once to an existing application database after taking a backup.
-- Existing messages and their subjects are preserved.
-- Composer does not run this script automatically.
alter table contact_messages
    add column phone varchar(30) null default null after email,
    alter column subject set default '';
