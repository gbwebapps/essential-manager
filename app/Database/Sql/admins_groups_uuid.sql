-- Eseguire una sola volta sul database dell'applicazione, dopo un backup.
-- Gli id e le associazioni amministratore-gruppo restano invariati.
-- Composer non esegue questo file automaticamente.
alter table admins_groups add column uuid char(36) null after id;
update admins_groups set uuid = uuid() where uuid is null;
alter table admins_groups modify column uuid char(36) not null, add unique key uq_admins_groups_uuid (uuid);
