-- BuntyFun automation core — 16 Nov 2026
-- Same change as database/migrations/2026_11_16_000001_create_automation_core_tables.php
-- (generated from it with Laravel's MySQL grammar; do not hand-edit one without the other).
--
-- BEFORE RUNNING: phpMyAdmin → database xsgowajv_buntyfun → Export → Quick → SQL → Go
--   (keep the download; that is the rollback).
-- RUN ONCE: phpMyAdmin → xsgowajv_buntyfun → SQL tab → paste this whole file → Go.
-- MySQL commits each CREATE/ALTER on its own, so if anything errors part-way, stop and
-- send the error text; do not re-run the whole file.
--
-- Only ADDS tables. The one change to an existing table: audit_logs.user_id becomes
-- nullable (automation writes audit rows with no user). No existing row changes.

create table `content_meta` (`id` bigint unsigned not null auto_increment primary key, `metable_type` varchar(40) not null, `metable_id` bigint unsigned not null, `seo_title` varchar(70) null, `seo_description` varchar(170) null, `og_image` varchar(255) null, `faq` json null, `is_featured` tinyint(1) not null default '0', `is_trending` tinyint(1) not null default '0', `noindex` tinyint(1) not null default '0', `publish_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `content_meta` add unique `content_meta_metable_type_metable_id_unique`(`metable_type`, `metable_id`);
alter table `content_meta` add index `content_meta_publish_at_index`(`publish_at`);
create table `messages` (`id` bigint unsigned not null auto_increment primary key, `category` varchar(40) not null, `language` varchar(2) not null default 'en', `title` varchar(255) null, `body` text not null, `slug` varchar(255) not null, `status` enum('draft', 'in_review', 'published') not null default 'draft', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `messages` add index `messages_category_status_index`(`category`, `status`);
alter table `messages` add unique `messages_slug_unique`(`slug`);
create table `ai_drafts` (`id` bigint unsigned not null auto_increment primary key, `content_type` varchar(30) not null, `params` json null, `prompt` text not null, `raw_output` longtext null, `payload` json null, `quality_score` tinyint unsigned null, `checks` json null, `status` enum('generated', 'failed', 'approved', 'rejected') not null default 'generated', `target_type` varchar(40) null, `target_id` bigint unsigned null, `error` varchar(500) null, `input_tokens` int unsigned null, `output_tokens` int unsigned null, `cost_usd` decimal(10, 6) null, `automation_run_id` bigint unsigned null, `reviewed_by` bigint unsigned null, `reviewed_at` timestamp null, `review_note` varchar(500) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `ai_drafts` add index `ai_drafts_status_content_type_index`(`status`, `content_type`);
create table `automation_jobs` (`id` bigint unsigned not null auto_increment primary key, `key` varchar(40) not null, `name` varchar(255) not null, `description` varchar(500) null, `enabled` tinyint(1) not null default '1', `frequency` enum('every_5_minutes', 'hourly', 'daily', 'weekly') not null default 'daily', `run_at` varchar(5) null, `max_retries` tinyint unsigned not null default '3', `consecutive_failures` tinyint unsigned not null default '0', `last_run_at` timestamp null, `last_status` varchar(10) null, `next_run_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `automation_jobs` add unique `automation_jobs_key_unique`(`key`);
create table `automation_runs` (`id` bigint unsigned not null auto_increment primary key, `job_key` varchar(40) not null, `triggered_by` varchar(40) not null default 'cron', `started_at` timestamp not null, `finished_at` timestamp null, `status` enum('running', 'success', 'failed', 'skipped') not null default 'running', `items` int unsigned not null default '0', `message` text null, `details` json null, `input_tokens` int unsigned not null default '0', `output_tokens` int unsigned not null default '0', `cost_usd` decimal(10, 6) not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `automation_runs` add index `automation_runs_job_key_started_at_index`(`job_key`, `started_at`);
create table `settings` (`key` varchar(60) not null, `value` json null, `created_at` timestamp null, `updated_at` timestamp null, primary key (`key`)) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `audit_logs` modify `user_id` bigint unsigned null;
insert into `automation_jobs` (`created_at`, `description`, `enabled`, `frequency`, `key`, `max_retries`, `name`, `run_at`, `updated_at`) values (NOW(), 'Publishes approved items whose publish time has come. Sitemap and RSS are built live from the database, so they update at once.', 1, 'every_5_minutes', 'publish_scheduled', 3, 'Publish scheduled content', null, NOW()), (NOW(), 'Picks content gaps by category priority and recent views, writes AI drafts, runs quality checks. Drafts wait for approval; nothing is published.', 1, 'daily', 'generate_drafts', 3, 'Daily ideas + AI drafts', '02:00', NOW()), (NOW(), 'Counts content, failed queue jobs, failed runs, AI spend and drafts waiting.', 1, 'daily', 'health_report', 3, 'Database + automation health', '06:00', NOW()), (NOW(), 'Requests every sitemap URL and lists any that do not return 200.', 1, 'weekly', 'broken_links', 3, 'Broken-link check', '03:00', NOW()), (NOW(), 'Lists seasonal items older than 11 months and published items with no views in 90 days. Report only.', 1, 'weekly', 'content_review', 3, 'Expired / weak content review', '04:00', NOW());

-- Tell Laravel this migration has run.
insert into `migrations` (`migration`, `batch`) select '2026_11_16_000001_create_automation_core_tables', coalesce(max(`batch`), 0) + 1 from `migrations`;

-- Check: should list 6 new tables and 5 jobs.
select `key`, `frequency`, `run_at`, `enabled` from `automation_jobs`;
