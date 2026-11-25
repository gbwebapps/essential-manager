-- TABELLA admins_groups
create table if not exists `essential_manager`.`admins_groups` (
    `id` int unsigned auto_increment,
    `uuid` char(36) not null unique,
    `name` varchar(50) not null unique,
    `description` varchar(255) null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    primary key (`id`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins_groups_permissions
create table if not exists `essential_manager`.`admins_groups_permissions` (
    `id` int unsigned auto_increment,
    `group_id` int unsigned not null,
    `permission` varchar(50) not null,
    primary key (`id`),
    unique key `unique_group_permission` (`group_id`, `permission`),
    constraint `fk_group_permissions_group_id`
        foreign key (`group_id`) references `essential_manager`.`admins_groups`(`id`) on update cascade on delete cascade
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins
create table if not exists `essential_manager`.`admins` (
    `id` int unsigned auto_increment unique,
    `uuid` char(36) not null unique,
    `firstname` varchar(255) not null,
    `lastname` varchar(255) not null,
    `email` varchar(255) not null unique,
    `phone` varchar(255) not null,
    `status` tinyint(1) default 0 not null,
    `superadmin` tinyint(1) default 0 not null,
    `group_id` int unsigned not null,
    `note` text null,
    `password_hash` varchar(255) null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    `suspended_at` datetime null default null,
    `resetted_at` datetime null default null,
    `deleted_at` datetime null default null,
    `restored_at` datetime null default null,
    primary key (`uuid`),
    index (`id`),
    index (`firstname`),
    index (`lastname`),
    index (`email`),
    index (`phone`),
    index (`status`),
    index (`superadmin`),
    index (`group_id`),
    index `idx_admins_created_at` (`created_at`),
    index `idx_admins_updated_at` (`updated_at`),
    index `idx_admins_suspended_at` (`suspended_at`),
    index `idx_admins_resetted_at` (`resetted_at`),
    index `idx_admins_deleted_at` (`deleted_at`),
    index `idx_admins_restored_at` (`restored_at`),
    constraint `fk_admins_group_id`
        foreign key (`group_id`) references `essential_manager`.`admins_groups`(`id`) on update cascade on delete restrict
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins_permissions
create table if not exists `essential_manager`.`admins_permissions` (
    `id` int unsigned auto_increment,
    `permission` varchar(50) not null,
    `admin_uuid` char(36) not null,
    `allow` tinyint(1) not null default 1,
    primary key (`id`),
    unique key `unique_admin_permission` (`admin_uuid`, `permission`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins_tokens
create table if not exists `essential_manager`.`admins_tokens` (
    `id` int unsigned auto_increment,
    `admin_uuid` char(36) not null,
    `token_hash` varchar(64) not null,
    `token_create` datetime not null,
    `last_activity` datetime null default null,
    `token_expire` datetime not null,
    `token_type` enum('cookie', 'session', 'activation') not null,
    `user_agent` varchar(255) not null,
    `ip_address` varchar(45) not null,
    `created_at` datetime not null,
    primary key (`id`),
    index (`admin_uuid`),
    index (`token_hash`),
    index (`token_create`),
    index (`last_activity`),
    index (`token_expire`),
    index (`token_type`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins_attempts
create table if not exists `essential_manager`.`admins_attempts` (
    `id` int unsigned auto_increment,
    `admin_uuid` char(36) null,
    `email_hash` char(64) not null,
    `ip_address` varchar(45) not null,
    `timestamp` datetime not null,
    primary key (`id`),
    index `idx_admin_attempts_lookup` (`admin_uuid`, `timestamp`),
    index `idx_admin_attempts_email` (`email_hash`, `timestamp`),
    index `idx_admin_attempts_ip` (`ip_address`, `timestamp`),
    index `idx_admin_attempts_timestamp` (`timestamp`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins_reset_attempts
create table if not exists `essential_manager`.`admins_reset_attempts` (
    `id` int unsigned auto_increment,
    `email_hash` char(64) not null,
    `ip_address` varchar(45) not null,
    `timestamp` datetime not null,
    primary key (`id`),
    index `idx_admins_reset_attempts_email` (`email_hash`, `timestamp`),
    index `idx_admins_reset_attempts_ip` (`ip_address`, `timestamp`),
    index `idx_admins_reset_attempts_timestamp` (`timestamp`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins_2fa
create table if not exists `essential_manager`.`admins_2fa` (
    `id` int unsigned auto_increment,
    `admin_uuid` char(36) not null,
    `method` enum('email', 'totp') not null default 'email',
    `secret` varchar(255) null,
    `enabled` tinyint(1) not null default 1,
    primary key (`id`),
    unique key `uq_admin_method` (`admin_uuid`,`method`),
    key `idx_admin` (`admin_uuid`),
    index `idx_admin_enabled` (`admin_uuid`,`enabled`)
) engine = innodb default character set = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins_2fa_codes
create table if not exists `essential_manager`.`admins_2fa_codes` (
    `id` int unsigned auto_increment,
    `admin_uuid` char(36) not null,
    `code` varchar(8) not null,
    `expires_at` datetime not null,
    primary key (`id`),
    index(`admin_uuid`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins_2fa_attempts
create table if not exists `essential_manager`.`admins_2fa_attempts` (
    `id` int unsigned auto_increment,
    `admin_uuid` char(36) not null,
    `method` enum('email', 'totp') not null,
    `ip_address` varchar(45) not null,
    `timestamp` datetime not null,
    primary key (`id`),
    index `idx_admin_2fa_attempts_lookup` (`admin_uuid`, `method`, `timestamp`),
    index `idx_admin_2fa_attempts_ip` (`ip_address`, `timestamp`),
    index `idx_admin_2fa_attempts_timestamp` (`timestamp`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins_audits
create table if not exists `essential_manager`.`admins_audits` (
    `id` int unsigned auto_increment primary key,
    `admin_uuid` char(36) null default null,
    `username` varchar(100) not null default 'Guest',
    `action` varchar(50) not null,
    `section` varchar(100) not null,
    `details` text null default null,
    `ip_address` varchar(45) not null,
    `user_agent` varchar(255) not null,
    `created_at` datetime not null,
    index `idx_admin_uuid` (`admin_uuid`),
    index `idx_sezione` (`section`),
    index `idx_created_at` (`created_at`)
) engine = InnoDB default charset  =utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admins_logs
create table if not exists `essential_manager`.`admins_logs` (
    `id` int unsigned auto_increment,
    `token_id` int unsigned null default null, -- Modificato per accettare null
    `admin_uuid` char(36) null default null,
    `username` varchar(100) not null default 'Guest',
    `login` datetime not null,
    `logout` datetime null default null,
    `logout_reason` enum('manual', 'timeout', 'deleted', 'banned') null default null,
    `token_type` enum('cookie', 'session') not null,
    `user_agent` varchar(255) not null,
    `ip_address` varchar(45) not null,
    `created_at` datetime not null,
    primary key (`id`),
    index (`token_id`),
    index (`admin_uuid`),
    index (`username`),
    index (`login`),
    index (`logout`),
    index (`logout_reason`),
    -- Aggiunta del vincolo FK
    constraint `fk_logs_token`
        foreign key (`token_id`)
        references `essential_manager`.`admins_tokens`(`id`)
        on update cascade
        on delete set null
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA users
create table if not exists `essential_manager`.`users` (
    `id` int unsigned auto_increment unique,
    `uuid` char(36) not null unique,
    `firstname` varchar(255) not null,
    `lastname` varchar(255) not null,
    `email` varchar(255) not null unique,
    `phone` varchar(255) not null,
    `status` tinyint(1) default 0 not null,
    `note` text null,
    `password_hash` varchar(255) null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    `suspended_at` datetime null default null,
    `resetted_at` datetime null default null,
    `deleted_at` datetime null default null,
    `to_be_removed_at` datetime null default null,
    primary key (`uuid`),
    index (`id`),
    index (`firstname`),
    index (`lastname`),
    index (`email`),
    index (`phone`),
    index (`status`),
    index `idx_users_created_at` (`created_at`),
    index `idx_users_updated_at` (`updated_at`),
    index `idx_users_suspended_at` (`suspended_at`),
    index `idx_users_resetted_at` (`resetted_at`),
    index `idx_users_deleted_at` (`deleted_at`),
    index `idx_users_to_be_removed_at` (`to_be_removed_at`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA users_tokens
create table if not exists `essential_manager`.`users_tokens` (
    `id` int unsigned auto_increment,
    `user_uuid` char(36) not null,
    `token_hash` varchar(64) not null,
    `token_create` datetime not null,
    `last_activity` datetime null default null,
    `token_expire` datetime not null,
    `token_type` enum('cookie', 'session', 'activation') not null,
    `user_agent` varchar(255) not null,
    `ip_address` varchar(45) not null,
    `created_at` datetime not null,
    primary key (`id`),
    index (`user_uuid`),
    index (`token_hash`),
    index (`token_create`),
    index (`last_activity`),
    index (`token_expire`),
    index (`token_type`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA users_attempts
create table if not exists `essential_manager`.`users_attempts` (
    `id` int unsigned auto_increment,
    `user_uuid` char(36) null,
    `email_hash` char(64) not null,
    `ip_address` varchar(45) not null,
    `timestamp` datetime not null,
    primary key (`id`),
    index `idx_user_attempts_lookup` (`user_uuid`, `timestamp`),
    index `idx_user_attempts_email` (`email_hash`, `timestamp`),
    index `idx_user_attempts_ip` (`ip_address`, `timestamp`),
    index `idx_user_attempts_timestamp` (`timestamp`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA users_reset_attempts
create table if not exists `essential_manager`.`users_reset_attempts` (
    `id` int unsigned auto_increment,
    `email_hash` char(64) not null,
    `ip_address` varchar(45) not null,
    `timestamp` datetime not null,
    primary key (`id`),
    index `idx_users_reset_attempts_email` (`email_hash`, `timestamp`),
    index `idx_users_reset_attempts_ip` (`ip_address`, `timestamp`),
    index `idx_users_reset_attempts_timestamp` (`timestamp`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA users_2fa
create table if not exists `essential_manager`.`users_2fa` (
    `id` int unsigned auto_increment,
    `user_uuid` char(36) not null,
    `method` enum('email', 'totp') not null default 'email',
    `secret` varchar(255) null,
    `enabled` tinyint(1) not null default 1,
    primary key (`id`),
    unique key `uq_user_method` (`user_uuid`,`method`),
    key `idx_user` (`user_uuid`),
    index `idx_user_enabled` (`user_uuid`,`enabled`)
) engine = innodb default character set = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA users_2fa_codes
create table if not exists `essential_manager`.`users_2fa_codes` (
    `id` int unsigned auto_increment,
    `user_uuid` char(36) not null,
    `code` varchar(8) not null,
    `expires_at` datetime not null,
    primary key (`id`),
    index(`user_uuid`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA users_2fa_attempts
create table if not exists `essential_manager`.`users_2fa_attempts` (
    `id` int unsigned auto_increment,
    `user_uuid` char(36) not null,
    `method` enum('email', 'totp') not null,
    `ip_address` varchar(45) not null,
    `timestamp` datetime not null,
    primary key (`id`),
    index `idx_user_2fa_attempts_lookup` (`user_uuid`, `method`, `timestamp`),
    index `idx_user_2fa_attempts_ip` (`ip_address`, `timestamp`),
    index `idx_user_2fa_attempts_timestamp` (`timestamp`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA users_audits
create table if not exists `essential_manager`.`users_audits` (
    `id` int unsigned auto_increment primary key,
    `user_uuid` char(36) null default null,
    `username` varchar(100) not null default 'Guest',
    `action` varchar(50) not null,
    `section` varchar(100) not null,
    `details` text null default null,
    `ip_address` varchar(45) not null,
    `user_agent` varchar(255) not null,
    `created_at` datetime not null,
    index `idx_user_uuid` (`user_uuid`),
    index `idx_sezione` (`section`),
    index `idx_created_at` (`created_at`)
) engine = innodb default charset  =utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA users_logs
create table if not exists `essential_manager`.`users_logs` (
    `id` int unsigned auto_increment,
    `token_id` int unsigned null default null, -- Modificato per accettare null
    `user_uuid` char(36) null default null,
    `username` varchar(100) not null default 'Guest',
    `login` datetime not null,
    `logout` datetime null default null,
    `logout_reason` enum('manual', 'timeout', 'deleted', 'banned') null default null,
    `token_type` enum('cookie', 'session') not null,
    `user_agent` varchar(255) not null,
    `ip_address` varchar(45) not null,
    `created_at` datetime not null,
    primary key (`id`),
    index (`token_id`),
    index (`user_uuid`),
    index (`username`),
    index (`login`),
    index (`logout`),
    index (`logout_reason`),
    -- Aggiunta del vincolo FK
    constraint `fk_users_logs_token`
        foreign key (`token_id`)
        references `essential_manager`.`users_tokens`(`id`)
        on update cascade
        on delete set null
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA users_registration_attempts
create table if not exists `essential_manager`.`users_registration_attempts` (
    `id` int unsigned auto_increment,
    `email_hash` char(64) not null,
    `ip_address` varchar(45) not null,
    `timestamp` datetime not null,
    primary key (`id`),
    index `idx_users_registration_email` (`email_hash`, `timestamp`),
    index `idx_users_registration_ip` (`ip_address`, `timestamp`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA contact_messages
create table if not exists `essential_manager`.`contact_messages` (
    `id` int unsigned auto_increment,
    `name` varchar(120) not null,
    `email` varchar(254) not null,
    `phone` varchar(30) null default null,
    `message` text not null,
    `status` enum('unread', 'read', 'archived') not null default 'unread',
    `created_at` datetime not null default current_timestamp,
    `read_at` datetime null default null,
    `archived_at` datetime null default null,
    primary key (`id`),
    index `idx_contact_messages_status_id` (`status`, `id`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA sessions
create table if not exists `essential_manager`.`sessions` (
    `id` varchar(128) not null,
    `ip_address` varchar(45) not null,
    `timestamp` datetime not null default current_timestamp,
    `data` blob not null,
    primary key (`id`),
    key `sessions_timestamp` (`timestamp`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA images
create table if not exists `essential_manager`.`images` (
    `id` int unsigned auto_increment,
    `entity` varchar(255) not null,
    `entity_uuid` char(36) not null,
    `filename` varchar(255) not null,
    `is_cover` tinyint(1) not null default 0,
    `created_at` datetime not null,
    primary key (`id`),
    index idx_images_entity_uuid (entity, entity_uuid),
    index idx_images_cover (entity, entity_uuid, is_cover)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

create table if not exists `essential_manager`.`settings` (
    `id` int unsigned auto_increment primary key,
    `class` varchar(255) not null,
    `key` varchar(255) not null,
    `value` text null,
    `created_at` datetime default current_timestamp,
    `updated_at` datetime default current_timestamp on update current_timestamp,
    unique key `class_key_unique` (`class`, `key`),
    index `idx_class` (`class`)
) engine = InnoDB default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLE APPLICATIVE MANAGER

-- TABELLA projects
-- PERMESSI projects
-- projects_show_all
-- projects_show
-- projects_add
-- projects_edit
-- projects_delete
-- projects_restore
create table if not exists `essential_manager`.`projects` (
    `id` int unsigned auto_increment unique,
    `uuid` char(36) not null unique,
    `name` varchar(255) not null unique,
    `description` text null,
    `parent_project_uuid` char(36) null default null,
    `local_path` varchar(1024) null default null,
    `local_url` varchar(2048) null default null,
    `production_url` varchar(2048) null default null,
    `execute_name` varchar(100) null default null unique,
    `created_by_admin_uuid` char(36) null default null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    `deleted_at` datetime null default null,
    `restored_at` datetime null default null,
    primary key (`uuid`),
    index (`id`),
    index (`parent_project_uuid`),
    index (`created_by_admin_uuid`),
    index `idx_projects_created_at` (`created_at`),
    index `idx_projects_updated_at` (`updated_at`),
    index `idx_projects_deleted_at` (`deleted_at`),
    constraint `chk_projects_parent`
        check (`parent_project_uuid` is null or `parent_project_uuid` <> `uuid`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA applications
-- PERMESSI applications
-- applications_show_all
-- applications_show
-- applications_add
-- applications_edit
-- applications_delete
-- applications_restore
-- Ogni applicazione concreta deriva da un solo progetto.
-- Locale e produzione sono due ambienti della stessa applicazione.
create table if not exists `essential_manager`.`applications` (
    `id` int unsigned auto_increment unique,
    `uuid` char(36) not null unique,
    `project_uuid` char(36) not null,
    `name` varchar(255) not null unique,
    `description` text null,
    `local_path` varchar(1024) null default null,
    `local_url` varchar(2048) null default null,
    `production_url` varchar(2048) null default null,
    `execute_name` varchar(100) null default null unique,
    `created_by_admin_uuid` char(36) null default null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    `deleted_at` datetime null default null,
    `restored_at` datetime null default null,
    primary key (`uuid`),
    index (`id`),
    index (`created_by_admin_uuid`),
    index `idx_applications_project_deleted_at` (`project_uuid`, `deleted_at`),
    index `idx_applications_created_at` (`created_at`),
    index `idx_applications_updated_at` (`updated_at`),
    index `idx_applications_deleted_at` (`deleted_at`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA tasks
-- PERMESSI tasks (comprendono la gestione dei passaggi)
-- tasks_show_all
-- tasks_show
-- tasks_add
-- tasks_edit
-- tasks_delete
-- tasks_restore
-- tasks_complete
-- tasks_archive
-- tasks_set_current
-- MODULO Studio
-- PERMESSI studio
-- studio_show
-- Studio usa i permessi Tasks e Notes per le rispettive azioni.
-- Un task appartiene a Studio, a un progetto oppure a una applicazione: un solo contesto.
create table if not exists `essential_manager`.`tasks` (
    `id` int unsigned auto_increment unique,
    `uuid` char(36) not null unique,
    `project_uuid` char(36) null default null,
    `application_uuid` char(36) null default null,
    `is_studio` tinyint unsigned not null default 0,
    `title` varchar(255) not null,
    `description` text null,
    `status` enum('todo', 'in_progress', 'completed') not null default 'todo',
    `created_by_admin_uuid` char(36) null default null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    `completed_at` datetime null default null,
    `archived_at` datetime null default null,
    `deleted_at` datetime null default null,
    `restored_at` datetime null default null,
    primary key (`uuid`),
    index (`id`),
    index `idx_tasks_studio_created_at` (`is_studio`, `created_at`),
    index (`created_by_admin_uuid`),
    unique key `unique_tasks_project_uuid` (`project_uuid`, `uuid`),
    unique key `unique_tasks_application_uuid` (`application_uuid`, `uuid`),
    index `idx_tasks_application_status` (`application_uuid`, `status`, `created_at`),
    index `idx_tasks_project_status` (`project_uuid`, `status`, `created_at`),
    index `idx_tasks_status_created_at` (`status`, `created_at`),
    index `idx_tasks_updated_at` (`updated_at`),
    index `idx_tasks_archive_context` (`is_studio`, `archived_at`, `deleted_at`, `created_at`, `id`),
    index `idx_tasks_deleted_at` (`deleted_at`),
    constraint `chk_tasks_studio`
        check (`is_studio` in (0, 1)),
    constraint `chk_tasks_context`
        check ((`project_uuid` is not null) + (`application_uuid` is not null) + `is_studio` = 1),
    constraint `chk_tasks_completed_at`
        check ((`status` = 'completed' and `completed_at` is not null) or (`status` <> 'completed' and `completed_at` is null))
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA studio_task_references
-- Un solo riferimento informativo per task Studio; eliminare il riferimento non elimina il task.
create table if not exists `essential_manager`.`studio_task_references` (
    `task_uuid` char(36) not null,
    `project_uuid` char(36) null default null,
    `application_uuid` char(36) null default null,
    primary key (`task_uuid`),
    index (`project_uuid`),
    index (`application_uuid`),
    constraint `chk_studio_task_references_context`
        check ((`project_uuid` is not null) + (`application_uuid` is not null) = 1)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA checklists
-- PERMESSI checklists
-- checklists_show_all
-- checklists_show
-- checklists_add
-- checklists_edit
-- checklists_delete
-- checklists_restore
-- checklists_apply
create table if not exists `essential_manager`.`checklists` (
    `id` int unsigned auto_increment unique,
    `uuid` char(36) not null unique,
    `name` varchar(255) not null unique,
    `description` text null,
    `created_by_admin_uuid` char(36) null default null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    `deleted_at` datetime null default null,
    `restored_at` datetime null default null,
    primary key (`uuid`),
    index (`id`),
    index (`created_by_admin_uuid`),
    index `idx_checklists_created_at` (`created_at`),
    index `idx_checklists_updated_at` (`updated_at`),
    index `idx_checklists_deleted_at` (`deleted_at`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA checklist_items
-- I passaggi del modello usano i permessi del modulo checklists.
create table if not exists `essential_manager`.`checklist_items` (
    `id` int unsigned auto_increment unique,
    `uuid` char(36) not null unique,
    `checklist_uuid` char(36) not null,
    `title` varchar(255) not null,
    `position` int unsigned not null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    primary key (`uuid`),
    index (`id`),
    unique key `unique_checklist_items_position` (`checklist_uuid`, `position`),
    constraint `chk_checklist_items_position`
        check (`position` > 0)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA task_steps
-- I passaggi del task usano i permessi del modulo tasks.
-- Il titolo viene copiato dal modello e resta indipendente dalle modifiche alla checklist.
create table if not exists `essential_manager`.`task_steps` (
    `id` int unsigned auto_increment unique,
    `uuid` char(36) not null unique,
    `task_uuid` char(36) not null,
    `checklist_item_uuid` char(36) null default null,
    `title` varchar(255) not null,
    `position` int unsigned not null,
    `completed_by_admin_uuid` char(36) null default null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    `completed_at` datetime null default null,
    primary key (`uuid`),
    index (`id`),
    index (`checklist_item_uuid`),
    index (`completed_by_admin_uuid`),
    unique key `unique_task_steps_position` (`task_uuid`, `position`),
    unique key `unique_task_steps_task_uuid` (`task_uuid`, `uuid`),
    constraint `chk_task_steps_position`
        check (`position` > 0)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA notes
-- PERMESSI notes
-- notes_show_all
-- notes_show
-- notes_add
-- notes_edit
-- notes_delete
-- notes_restore
-- Ogni nota appartiene a un solo progetto, applicazione, task oppure passaggio.
create table if not exists `essential_manager`.`notes` (
    `id` int unsigned auto_increment unique,
    `uuid` char(36) not null unique,
    `project_uuid` char(36) null default null,
    `application_uuid` char(36) null default null,
    `is_studio` tinyint unsigned not null default 0,
    `task_uuid` char(36) null default null,
    `step_uuid` char(36) null default null,
    `content` text not null,
    `created_by_admin_uuid` char(36) null default null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    `deleted_at` datetime null default null,
    `restored_at` datetime null default null,
    primary key (`uuid`),
    index (`id`),
    index `idx_notes_studio_created_at` (`is_studio`, `created_at`),
    index (`created_by_admin_uuid`),
    index `idx_notes_application_created_at` (`application_uuid`, `created_at`),
    index `idx_notes_project_created_at` (`project_uuid`, `created_at`),
    index `idx_notes_task_created_at` (`task_uuid`, `created_at`),
    index `idx_notes_step_created_at` (`step_uuid`, `created_at`),
    index `idx_notes_deleted_at` (`deleted_at`),
    constraint `chk_notes_studio`
        check (`is_studio` in (0, 1)),
    constraint `chk_notes_context`
        check ((`project_uuid` is not null) + (`application_uuid` is not null) + (`task_uuid` is not null) + (`step_uuid` is not null) + `is_studio` = 1)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA admin_work_state
-- Usa tasks_set_current; ogni amministratore ha un task corrente per le attività e uno per Studio.
-- Il prossimo passaggio si ricava dai passaggi non completati del task, ordinati per position.
-- Il modulo Tasks esclude dalla selezione i task completati o eliminati.
create table if not exists `essential_manager`.`admin_work_state` (
    `admin_uuid` char(36) not null,
    `work_context` varchar(16) not null default 'activities',
    `task_uuid` char(36) null default null,
    `created_at` datetime not null,
    `updated_at` datetime null default null,
    primary key (`admin_uuid`, `work_context`),
    index (`task_uuid`),
    constraint `chk_admin_work_state_context`
        check (`work_context` in ('activities', 'studio'))
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- TABELLA operation_runs
-- PERMESSI operations
-- operations_show_all
-- operations_show
-- operations_check
-- operations_tests
-- operations_update
-- operations_commit
-- operations_push
-- operations_clone
-- I comandi ammessi e i relativi argomenti vengono controllati dal modulo Operations.
-- execute_name conserva il nome della destinazione usato da execute/installations.php.
create table if not exists `essential_manager`.`operation_runs` (
    `id` int unsigned auto_increment unique,
    `uuid` char(36) not null unique,
    `project_uuid` char(36) null default null,
    `application_uuid` char(36) null default null,
    `task_uuid` char(36) null default null,
    `step_uuid` char(36) null default null,
    `operation` varchar(50) not null,
    `execute_name` varchar(100) not null,
    `parameters` json null,
    `status` enum('pending', 'running', 'succeeded', 'failed', 'cancelled') not null default 'pending',
    `admin_uuid` char(36) null default null,
    `admin_name` varchar(255) not null,
    `created_at` datetime not null,
    `started_at` datetime null default null,
    `finished_at` datetime null default null,
    `exit_code` int null default null,
    `report_path` varchar(1024) null default null,
    `error_message` text null,
    primary key (`uuid`),
    index (`id`),
    index (`admin_uuid`),
    index `idx_operation_runs_application_created_at` (`application_uuid`, `created_at`),
    index `idx_operation_runs_project_task` (`project_uuid`, `task_uuid`),
    index `idx_operation_runs_application_task` (`application_uuid`, `task_uuid`),
    index `idx_operation_runs_project_created_at` (`project_uuid`, `created_at`),
    index `idx_operation_runs_task_step` (`task_uuid`, `step_uuid`),
    index (`step_uuid`),
    index `idx_operation_runs_status_created_at` (`status`, `created_at`),
    constraint `chk_operation_runs_context`
        check ((`project_uuid` is not null) + (`application_uuid` is not null) = 1),
    constraint `chk_operation_runs_task_step`
        check (`step_uuid` is null or `task_uuid` is not null),
    constraint `chk_operation_runs_dates`
        check (`finished_at` is null or `started_at` is null or `finished_at` >= `started_at`)
) engine = innodb default charset = utf8mb4 collate = utf8mb4_general_ci;

-- VINCOLI foreign key ESTERNI (Puntati su essential_manager)
alter table `essential_manager`.`admins_permissions`
add constraint `fk_admins_permissions_admin_uuid`
foreign key (`admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`admins_tokens`
add constraint `fk_admins_tokens_admin_uuid`
foreign key (`admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`admins_attempts`
add constraint `fk_admins_attempts_admin_uuid`
foreign key (`admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`admins_2fa`
add constraint `fk_admins_2fa_admin_uuid`
foreign key (`admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`admins_2fa_codes`
add constraint `fk_admins_2fa_codes_admin_uuid`
foreign key (`admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`admins_2fa_attempts`
add constraint `fk_user_2fa_attempts_admin_uuid`
foreign key (`admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`users_tokens`
add constraint `fk_users_tokens_user_uuid`
foreign key (`user_uuid`) references `essential_manager`.`users`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`users_attempts`
add constraint `fk_users_attempts_user_uuid`
foreign key (`user_uuid`) references `essential_manager`.`users`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`users_2fa`
add constraint `fk_users_2fa_user_uuid`
foreign key (`user_uuid`) references `essential_manager`.`users`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`users_2fa_codes`
add constraint `fk_users_2fa_codes_user_uuid`
foreign key (`user_uuid`) references `essential_manager`.`users`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`users_2fa_attempts`
add constraint `fk_users_2fa_attempts_user_uuid`
foreign key (`user_uuid`) references `essential_manager`.`users`(`uuid`) on update cascade on delete cascade;


-- VINCOLI foreign key MANAGER
-- restrict conserva le relazioni e impedisce cancellazioni automatiche dei dati di lavoro.
-- I riferimenti coinvolti nei check usano on update restrict, come richiesto da MySQL.

alter table `essential_manager`.`projects`
add constraint `fk_projects_parent_project_uuid`
foreign key (`parent_project_uuid`) references `essential_manager`.`projects`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`projects`
add constraint `fk_projects_created_by_admin_uuid`
foreign key (`created_by_admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete set null;

alter table `essential_manager`.`applications`
add constraint `fk_applications_project_uuid`
foreign key (`project_uuid`) references `essential_manager`.`projects`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`applications`
add constraint `fk_applications_created_by_admin_uuid`
foreign key (`created_by_admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete set null;

alter table `essential_manager`.`tasks`
add constraint `fk_tasks_application_uuid`
foreign key (`application_uuid`) references `essential_manager`.`applications`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`tasks`
add constraint `fk_tasks_project_uuid`
foreign key (`project_uuid`) references `essential_manager`.`projects`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`tasks`
add constraint `fk_tasks_created_by_admin_uuid`
foreign key (`created_by_admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete set null;

alter table `essential_manager`.`checklists`
add constraint `fk_checklists_created_by_admin_uuid`
foreign key (`created_by_admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete set null;

alter table `essential_manager`.`checklist_items`
add constraint `fk_checklist_items_checklist_uuid`
foreign key (`checklist_uuid`) references `essential_manager`.`checklists`(`uuid`) on update cascade on delete restrict;

alter table `essential_manager`.`task_steps`
add constraint `fk_task_steps_task_uuid`
foreign key (`task_uuid`) references `essential_manager`.`tasks`(`uuid`) on update cascade on delete restrict;

alter table `essential_manager`.`task_steps`
add constraint `fk_task_steps_checklist_item_uuid`
foreign key (`checklist_item_uuid`) references `essential_manager`.`checklist_items`(`uuid`) on update cascade on delete set null;

alter table `essential_manager`.`task_steps`
add constraint `fk_task_steps_completed_by_admin_uuid`
foreign key (`completed_by_admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete set null;

alter table `essential_manager`.`notes`
add constraint `fk_notes_application_uuid`
foreign key (`application_uuid`) references `essential_manager`.`applications`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`notes`
add constraint `fk_notes_project_uuid`
foreign key (`project_uuid`) references `essential_manager`.`projects`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`notes`
add constraint `fk_notes_task_uuid`
foreign key (`task_uuid`) references `essential_manager`.`tasks`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`notes`
add constraint `fk_notes_step_uuid`
foreign key (`step_uuid`) references `essential_manager`.`task_steps`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`notes`
add constraint `fk_notes_created_by_admin_uuid`
foreign key (`created_by_admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete set null;

alter table `essential_manager`.`admin_work_state`
add constraint `fk_admin_work_state_admin_uuid`
foreign key (`admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete cascade;

alter table `essential_manager`.`admin_work_state`
add constraint `fk_admin_work_state_task_uuid`
foreign key (`task_uuid`) references `essential_manager`.`tasks`(`uuid`) on update cascade on delete set null;

alter table `essential_manager`.`operation_runs`
add constraint `fk_operation_runs_application_uuid`
foreign key (`application_uuid`) references `essential_manager`.`applications`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`operation_runs`
add constraint `fk_operation_runs_project_task`
foreign key (`project_uuid`, `task_uuid`) references `essential_manager`.`tasks`(`project_uuid`, `uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`operation_runs`
add constraint `fk_operation_runs_application_task`
foreign key (`application_uuid`, `task_uuid`) references `essential_manager`.`tasks`(`application_uuid`, `uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`operation_runs`
add constraint `fk_operation_runs_project_uuid`
foreign key (`project_uuid`) references `essential_manager`.`projects`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`operation_runs`
add constraint `fk_operation_runs_task_uuid`
foreign key (`task_uuid`) references `essential_manager`.`tasks`(`uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`operation_runs`
add constraint `fk_operation_runs_task_step`
foreign key (`task_uuid`, `step_uuid`) references `essential_manager`.`task_steps`(`task_uuid`, `uuid`) on update restrict on delete restrict;

alter table `essential_manager`.`operation_runs`
add constraint `fk_operation_runs_admin_uuid`
foreign key (`admin_uuid`) references `essential_manager`.`admins`(`uuid`) on update cascade on delete set null;

alter table `essential_manager`.`studio_task_references`
add constraint `fk_studio_task_references_task_uuid`
foreign key (`task_uuid`) references `essential_manager`.`tasks`(`uuid`) on update restrict on delete cascade;

alter table `essential_manager`.`studio_task_references`
add constraint `fk_studio_task_references_project_uuid`
foreign key (`project_uuid`) references `essential_manager`.`projects`(`uuid`) on update restrict on delete cascade;

alter table `essential_manager`.`studio_task_references`
add constraint `fk_studio_task_references_application_uuid`
foreign key (`application_uuid`) references `essential_manager`.`applications`(`uuid`) on update restrict on delete cascade;
