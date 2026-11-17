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

-- GRUPPI INIZIALI
insert into `essential_manager`.`admins_groups` (`id`, `uuid`, `name`, `description`, `created_at`) values
(1, '94b47851-6667-4bb1-930a-87a2e9507b68', 'Administrator', 'Administrator', now()),
(2, 'ffaccc6b-134e-4fa8-95e6-d9e4881a9064', 'Editor Manager', 'Editor Manager', now()),
(3, 'd9293970-9189-4576-88e8-484161137840', 'Operator / Support', 'Operator / Support', now());
