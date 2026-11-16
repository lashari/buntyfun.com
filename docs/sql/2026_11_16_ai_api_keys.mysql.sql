-- BuntyFun AI API keys table — 16 Nov 2026
-- Same as database/migrations/2026_11_16_000002_create_ai_api_keys_table.php.
-- Only ADDS one new table; changes nothing that exists. Run once:
-- phpMyAdmin → xsgowajv_buntyfun → SQL tab → paste → Go.

create table `ai_api_keys` (`id` bigint unsigned not null auto_increment primary key, `provider` enum('gemini', 'openai') not null default 'gemini', `label` varchar(60) not null, `api_key` text not null, `last4` varchar(4) not null, `enabled` tinyint(1) not null default '1', `priority` smallint unsigned not null default '10', `use_count` int unsigned not null default '0', `fail_count` smallint unsigned not null default '0', `last_used_at` timestamp null, `last_error` varchar(120) null, `cooldown_until` timestamp null, `created_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci' engine = InnoDB;
alter table `ai_api_keys` add index `ai_api_keys_provider_enabled_index`(`provider`, `enabled`);

insert into `migrations` (`migration`, `batch`) select '2026_11_16_000002_create_ai_api_keys_table', coalesce(max(`batch`), 0) + 1 from `migrations`;
