-- BuntyFun social publishing — 16 Nov 2026
-- Same as database/migrations/2026_11_16_000003_create_social_tables.php.
-- Adds 2 new tables + 1 automation job row. Changes nothing that exists. Run once:
-- phpMyAdmin → xsgowajv_buntyfun → SQL tab → paste → Go.

create table `social_accounts` (`id` bigint unsigned not null auto_increment primary key, `platform` enum('facebook', 'instagram') not null, `external_id` varchar(64) not null, `name` varchar(255) not null, `username` varchar(255) null, `access_token` text not null, `token_expires_at` timestamp null, `page_id` varchar(64) null, `enabled` tinyint(1) not null default '1', `last_error` varchar(300) null, `connected_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `social_accounts` add unique `social_accounts_platform_external_id_unique`(`platform`, `external_id`);
create table `social_posts` (`id` bigint unsigned not null auto_increment primary key, `platform` enum('facebook', 'instagram') not null, `social_account_id` bigint unsigned not null, `source_type` varchar(40) null, `source_id` bigint unsigned null, `caption` text not null, `link` varchar(500) null, `image_url` varchar(500) null, `status` enum('draft', 'approved', 'publishing', 'published', 'failed', 'cancelled') not null default 'draft', `scheduled_at` timestamp null, `published_at` timestamp null, `external_id` varchar(100) null, `permalink` varchar(500) null, `error` varchar(500) null, `attempts` tinyint unsigned not null default '0', `created_by` bigint unsigned null, `approved_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `social_posts` add index `social_posts_status_scheduled_at_index`(`status`, `scheduled_at`);
alter table `social_posts` add index `social_posts_source_type_source_id_index`(`source_type`, `source_id`);
insert into `automation_jobs` (`key`, `name`, `frequency`, `run_at`, `description`, `enabled`, `max_retries`, `created_at`, `updated_at`) values ('social_publish', 'Publish approved social posts', 'every_5_minutes', null, 'Posts approved Facebook/Instagram posts whose time has come, through the official Graph API. Failed posts retry 3 times.', 1, 3, NOW(), NOW());

insert into `migrations` (`migration`, `batch`) select '2026_11_16_000003_create_social_tables', coalesce(max(`batch`), 0) + 1 from `migrations`;
