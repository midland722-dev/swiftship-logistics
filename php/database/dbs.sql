-- phpMyAdmin SQL Dump
-- Merged: complete_setup.sql + dbs.sql + ship.sql + minimal.sql + add_receiptFields.sql
-- Target database: shipping_db
-- No duplicate tables or records
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `activity_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `api_integration_logs` (
  `id` bigint(20) NOT NULL,
  `integration_id` int(11) NOT NULL,
  `endpoint_hit` varchar(500) NOT NULL,
  `http_method` varchar(10) NOT NULL,
  `request_headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_headers`)),
  `request_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_body`)),
  `response_code` int(11) DEFAULT NULL,
  `response_headers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_headers`)),
  `response_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_body`)),
  `error_message` text DEFAULT NULL,
  `duration_ms` int(11) DEFAULT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `api_integrations` (
  `id` int(11) NOT NULL,
  `integration_name` varchar(100) NOT NULL,
  `provider` varchar(100) NOT NULL COMMENT 'FedEx, DHL, customs system, etc.',
  `integration_type` enum('tracking','rating','shipping','customs','payment','sms','email','other') NOT NULL,
  `endpoint_url` varchar(500) NOT NULL,
  `api_key_encrypted` text NOT NULL,
  `api_secret_encrypted` text DEFAULT NULL,
  `webhook_url` varchar(500) DEFAULT NULL,
  `auth_type` enum('basic','bearer','oauth','api_key','none') DEFAULT 'api_key',
  `auth_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`auth_config`)),
  `request_format` enum('json','xml','form_data') DEFAULT 'json',
  `response_format` enum('json','xml') DEFAULT 'json',
  `rate_limit_per_minute` int(11) DEFAULT 60,
  `timeout_seconds` int(11) DEFAULT 30,
  `retry_count` int(11) DEFAULT 3,
  `retry_delay_seconds` int(11) DEFAULT 5,
  `is_active` tinyint(1) DEFAULT 1,
  `last_sync_at` timestamp NULL DEFAULT NULL,
  `last_error` text DEFAULT NULL,
  `consecutive_failures` int(11) DEFAULT 0,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `key_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Allowed endpoints/permissions' CHECK (json_valid(`permissions`)),
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `api_usage_logs` (
  `id` bigint(20) NOT NULL,
  `api_key_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `endpoint` varchar(255) NOT NULL,
  `method` varchar(10) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `request_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_body`)),
  `response_code` int(11) DEFAULT 200,
  `response_time_ms` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `uuid` char(36) DEFAULT uuid(),
  `entity_type` enum('shipment','contact','invoice','user','other') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL COMMENT 'Bytes',
  `uploaded_by` int(11) DEFAULT NULL,
  `public_url` varchar(500) DEFAULT NULL,
  `access_level` enum('private','internal','public') DEFAULT 'private',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `audit_logs_v2` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `action` enum('create','read','update','delete','login','logout','export','import','bulk','api_call') NOT NULL,
  `description` text NOT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `changed_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`changed_fields`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `request_url` text DEFAULT NULL,
  `request_body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_body`)),
  `response_code` int(11) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL COMMENT 'Application module',
  `source` enum('web','api','cli','cron','webhook') DEFAULT 'web',
  `is_sensitive` tinyint(1) DEFAULT 0 COMMENT 'Contains sensitive data',
  `compliance_category` varchar(50) DEFAULT NULL COMMENT 'GDPR, PCI, etc.',
  `retention_days` int(11) DEFAULT 365,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bulk_operations` (
  `id` int(11) NOT NULL,
  `operation_uuid` char(36) DEFAULT uuid(),
  `user_id` int(11) NOT NULL,
  `operation_type` enum('status_update','label_print','invoice_generate','export','delete','custom') NOT NULL,
  `description` varchar(255) NOT NULL,
  `criteria` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Filter criteria applied' CHECK (json_valid(`criteria`)),
  `affected_count` int(11) DEFAULT 0,
  `success_count` int(11) DEFAULT 0,
  `failure_count` int(11) DEFAULT 0,
  `result_summary` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`result_summary`)),
  `status` enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `bulk_uploads` (
  `id` int(11) NOT NULL,
  `uuid` char(36) DEFAULT uuid(),
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_size` int(11) NOT NULL,
  `rows_total` int(11) DEFAULT 0,
  `rows_processed` int(11) DEFAULT 0,
  `rows_failed` int(11) DEFAULT 0,
  `status` enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
  `error_log` text DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calculator` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `currency` varchar(10) NOT NULL,
  `normal` decimal(10,4) NOT NULL,
  `express` decimal(10,4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `communication_logs` (
  `id` bigint(20) NOT NULL,
  `type` enum('email','sms','push','in_app') DEFAULT 'email',
  `template_key` varchar(100) DEFAULT NULL,
  `recipient_type` enum('customer','staff','admin','other') DEFAULT 'customer',
  `recipient_id` int(11) DEFAULT NULL COMMENT 'User ID or external',
  `recipient_address` varchar(255) NOT NULL COMMENT 'Email or phone number',
  `subject` varchar(500) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `status` enum('pending','sent','delivered','failed','bounced') DEFAULT 'pending',
  `provider` varchar(100) DEFAULT NULL COMMENT 'SMTP, Twilio, etc.',
  `provider_message_id` varchar(255) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `communication_logs_enhanced` (
  `id` bigint(20) NOT NULL,
  `batch_id` varchar(36) DEFAULT NULL,
  `type` enum('email','sms','push','in_app','whatsapp','telegram') DEFAULT 'email',
  `provider` varchar(100) DEFAULT NULL,
  `provider_message_id` varchar(255) DEFAULT NULL,
  `recipient_type` enum('customer','staff','admin','supplier','other') DEFAULT 'customer',
  `recipient_id` int(11) DEFAULT NULL,
  `recipient_address` varchar(500) NOT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `template_key` varchar(100) DEFAULT NULL,
  `template_variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`template_variables`)),
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `status` enum('pending','queued','sent','delivered','failed','bounced','spam','opened','clicked') DEFAULT 'pending',
  `error_code` varchar(50) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `max_retries` int(11) DEFAULT 3,
  `scheduled_for` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `clicked_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `company` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `uuid` char(36) DEFAULT uuid(),
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `category` enum('general','support','sales','partnership','complaint') DEFAULT 'general',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('new','read','replied','closed','spam') DEFAULT 'new',
  `assigned_to` int(11) DEFAULT NULL,
  `reply_text` text DEFAULT NULL,
  `reply_sent_at` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courier` (
  `cid` int(6) NOT NULL AUTO_INCREMENT,
  `cons_no` varchar(20) NOT NULL,
  `ship_name` varchar(100) NOT NULL,
  `phone` varchar(90) NOT NULL,
  `s_add` varchar(300) NOT NULL,
  `cc` varchar(120) NOT NULL,
  `rev_name` varchar(100) NOT NULL,
  `r_phone` varchar(90) NOT NULL,
  `r_add` varchar(300) NOT NULL,
  `cc_r` varchar(120) NOT NULL,
  `email` varchar(255) NOT NULL,
  `type` varchar(40) NOT NULL,
  `weight` double NOT NULL,
  `variable` varchar(255) NOT NULL,
  `shipping_subtotal` varchar(255) NOT NULL,
  `invice_no` varchar(150) NOT NULL,
  `qty` int(10) NOT NULL,
  `book_mode` varchar(20) NOT NULL,
  `declarate` varchar(120) NOT NULL,
  `freight` varchar(150) NOT NULL,
  `mode` varchar(20) NOT NULL,
  `pick_date` varchar(250) NOT NULL,
  `schedule` varchar(250) NOT NULL,
  `pick_time` varchar(250) NOT NULL,
  `status` varchar(20) NOT NULL,
  `comments` varchar(250) NOT NULL,
  `book_date` date NOT NULL,
  `status_delivered` varchar(100) NOT NULL DEFAULT '0',
  `officename` varchar(255) NOT NULL,
  `user` varchar(255) DEFAULT NULL,
  `pimage` varchar(255) DEFAULT NULL,
  `senderimg` varchar(255) DEFAULT NULL,
  `reciverimage` varchar(255) DEFAULT NULL,
  `percent` varchar(150) NOT NULL DEFAULT '80',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courier_online` (
  `cid` int(10) NOT NULL AUTO_INCREMENT,
  `deliveryboy` varchar(100) NOT NULL,
  `receivedby` varchar(100) NOT NULL,
  `drs` varchar(100) NOT NULL,
  `cons_no` varchar(20) NOT NULL,
  `ship_name` varchar(100) NOT NULL,
  `s_phone` varchar(12) NOT NULL,
  `s_add` varchar(200) NOT NULL,
  `fromcity` varchar(200) NOT NULL,
  `rev_name` varchar(100) NOT NULL,
  `r_phone` varchar(12) NOT NULL,
  `r_add` varchar(200) NOT NULL,
  `tocity` varchar(200) NOT NULL,
  `type` varchar(40) NOT NULL,
  `note` varchar(255) NOT NULL,
  `weight` varchar(100) NOT NULL,
  `book_mode` varchar(20) NOT NULL,
  `freight` double NOT NULL,
  `Qnty` varchar(255) NOT NULL,
  `variable` varchar(255) NOT NULL,
  `shipping_subtotal` varchar(255) NOT NULL,
  `mode` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `deliverydate` varchar(100) NOT NULL,
  `time` varchar(10) NOT NULL,
  `status` varchar(20) NOT NULL,
  `payment` varchar(255) NOT NULL,
  `paymode` varchar(255) NOT NULL,
  `comments` varchar(250) NOT NULL,
  `office` varchar(100) NOT NULL,
  `user` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courier_paid` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cid` int(10) NOT NULL,
  `cons_no` varchar(20) NOT NULL,
  `book_mode` varchar(30) NOT NULL,
  `on_delivery` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cid` (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courier_track` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `cid` int(10) NOT NULL,
  `cons_no` varchar(20) NOT NULL,
  `pick_time` varchar(100) NOT NULL,
  `status` varchar(30) NOT NULL,
  `comments` varchar(255) NOT NULL,
  `bk_time` varchar(150) DEFAULT NULL,
  `user` varchar(250) DEFAULT NULL,
  `day` varchar(150) DEFAULT NULL,
  `details` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customs_checkpoints` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `checkpoint_name` varchar(255) NOT NULL,
  `checkpoint_code` varchar(50) NOT NULL,
  `country_code` char(2) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `arrival_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `departure_time` timestamp NULL DEFAULT NULL,
  `current_status` enum('arrived','processing','cleared','held','seized','released') DEFAULT 'arrived',
  `officer_name` varchar(100) DEFAULT NULL,
  `officer_id` int(11) DEFAULT NULL,
  `clearance_type` enum('automated','manual','expedited','inspection') DEFAULT 'automated',
  `duties_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `documents_verified` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_verified`)),
  `inspection_performed` tinyint(1) DEFAULT 0,
  `inspection_type` enum('xray','physical','documentary','none') DEFAULT 'none',
  `risk_level` enum('low','medium','high','critical') DEFAULT 'medium',
  `notes` text DEFAULT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customs_declarations` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `hs_code` varchar(50) DEFAULT NULL,
  `commodity_description` text NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit_value` decimal(10,2) DEFAULT 0.00,
  `total_value` decimal(12,2) DEFAULT 0.00,
  `country_of_origin` char(2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `weight` decimal(8,3) DEFAULT NULL,
  `is_commercial` tinyint(1) DEFAULT 0,
  `exporter_name` varchar(255) DEFAULT NULL,
  `exporter_address` text DEFAULT NULL,
  `importer_name` varchar(255) DEFAULT NULL,
  `importer_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `customs_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`customs_documents`)),
  `cleared` tinyint(1) DEFAULT 0,
  `cleared_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customs_documents` (
  `id` int(11) NOT NULL,
  `customs_record_id` int(11) NOT NULL,
  `document_type` enum('commercial_invoice','packing_list','certificate_of_origin','bill_of_lading','insurance_certificate','import_permit','export_permit','other') NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `version` int(11) DEFAULT 1,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `is_required` tinyint(1) DEFAULT 1,
  `is_submitted` tinyint(1) DEFAULT 0,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `ocr_text` longtext DEFAULT NULL COMMENT 'OCR extracted text',
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `customs_duty_calculations` (
  `id` int(11) NOT NULL,
  `customs_record_id` int(11) NOT NULL,
  `hs_code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `declared_value` decimal(12,2) NOT NULL,
  `duty_rate` decimal(8,4) NOT NULL,
  `duty_amount` decimal(10,2) NOT NULL,
  `tax_rate` decimal(8,4) NOT NULL,
  `tax_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `calculation_method` varchar(50) DEFAULT 'standard',
  `calculation_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`calculation_details`)),
  `calculated_by` int(11) DEFAULT NULL,
  `calculated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customs_inspections` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `customs_record_id` int(11) DEFAULT NULL,
  `inspection_type` enum('standard','intensive','xray','physical','documentary','targeted') DEFAULT 'standard',
  `inspector_id` int(11) DEFAULT NULL,
  `inspector_name` varchar(100) DEFAULT NULL,
  `inspection_date` date NOT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `end_time` timestamp NULL DEFAULT NULL,
  `result` enum('pass','fail','pending','conditional') DEFAULT 'pending',
  `risk_level` enum('low','medium','high','critical') DEFAULT 'medium',
  `items_found` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`items_found`)),
  `documents_reviewed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_reviewed`)),
  `photographs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`photographs`)),
  `xray_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`xray_images`)),
  `lab_results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`lab_results`)),
  `violations_found` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`violations_found`)),
  `recommended_action` enum('clear','hold','seize','inspect_again') DEFAULT 'clear',
  `followup_required` tinyint(1) DEFAULT 0,
  `followup_date` date DEFAULT NULL,
  `supervisor_review` tinyint(1) DEFAULT 0,
  `supervisor_id` int(11) DEFAULT NULL,
  `supervisor_notes` text DEFAULT NULL,
  `quality_score` decimal(3,1) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customs_notification_templates` (
  `id` int(11) NOT NULL,
  `template_key` varchar(100) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `event_type` enum('checkpoint_arrival','hold','release','seizure','clearance','inspection_required','inspection_complete') NOT NULL,
  `subject_template` longtext NOT NULL,
  `body_template` longtext NOT NULL,
  `sms_template` text DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `send_to_customer` tinyint(1) DEFAULT 1,
  `send_to_officer` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customs_officer_logs` (
  `id` int(11) NOT NULL,
  `officer_id` int(11) NOT NULL,
  `action_type` varchar(100) NOT NULL,
  `shipment_id` int(11) DEFAULT NULL,
  `checkpoint_id` int(11) DEFAULT NULL,
  `seizure_id` int(11) DEFAULT NULL,
  `inspection_id` int(11) DEFAULT NULL,
  `action_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`action_details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customs_records` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `customs_reference` varchar(50) NOT NULL,
  `origin_country` char(2) DEFAULT 'US',
  `destination_country` char(2) DEFAULT 'US',
  `total_value` decimal(12,2) DEFAULT 0.00,
  `weight` decimal(8,2) DEFAULT 0.00,
  `duties_amount` decimal(10,2) DEFAULT 0.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `hs_code` varchar(50) DEFAULT NULL,
  `commodity_description` text DEFAULT NULL,
  `importer_name` varchar(255) DEFAULT NULL,
  `exporter_name` varchar(255) DEFAULT NULL,
  `status` enum('pending','under_review','cleared','held','seized') DEFAULT 'pending',
  `inspection_required` tinyint(1) DEFAULT 0,
  `inspection_result` enum('pending','pass','fail','conditional') DEFAULT 'pending',
  `required_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`required_documents`)),
  `submitted_documents` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`submitted_documents`)),
  `hold_until` date DEFAULT NULL,
  `cleared_by` int(11) DEFAULT NULL,
  `cleared_at` timestamp NULL DEFAULT NULL,
  `clearance_type` enum('automated','manual','expedited','inspection') DEFAULT 'automated',
  `compliance_flags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`compliance_flags`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customs_seizures` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `seizure_reference` varchar(100) NOT NULL,
  `seizure_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `seizing_officer_id` int(11) DEFAULT NULL,
  `seizing_officer_name` varchar(100) DEFAULT NULL,
  `seizure_reason_code` varchar(50) NOT NULL,
  `seizure_reason_description` text NOT NULL,
  `legal_basis` varchar(255) NOT NULL,
  `restricted_item_type` enum('firearms','narcotics','explosives','wildlife','currency','counterfeit','other') DEFAULT 'other',
  `threat_level` enum('low','medium','high','critical') DEFAULT 'medium',
  `action_taken` enum('seized','detained','forfeited','released') DEFAULT 'seized',
  `held_at_facility` varchar(255) DEFAULT NULL,
  `custody_status` enum('seized','under_review','court_pending','released','forfeited','destroyed') DEFAULT 'seized',
  `release_possible` tinyint(1) DEFAULT 1,
  `release_conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`release_conditions`)),
  `court_case_number` varchar(100) DEFAULT NULL,
  `legal_representative` varchar(255) DEFAULT NULL,
  `documents_submitted` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`documents_submitted`)),
  `hearing_date` date DEFAULT NULL,
  `final_disposition` enum('pending','released','forfeited','destroyed','sold') DEFAULT 'pending',
  `disposition_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customs_tariffs` (
  `id` int(11) NOT NULL,
  `hs_code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `standard_rate` decimal(5,4) DEFAULT 0.0000,
  `preferential_rate` decimal(5,4) DEFAULT 0.0000,
  `country_code` char(2) DEFAULT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `daily_stats` (
  `id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `total_shipments` int(11) DEFAULT 0,
  `total_revenue` decimal(12,2) DEFAULT 0.00,
  `total_weight` decimal(12,3) DEFAULT 0.000,
  `delivered_count` int(11) DEFAULT 0,
  `pending_count` int(11) DEFAULT 0,
  `in_transit_count` int(11) DEFAULT 0,
  `cancelled_count` int(11) DEFAULT 0,
  `new_customers` int(11) DEFAULT 0,
  `payment_pending` decimal(12,2) DEFAULT 0.00,
  `top_origin_country` char(2) DEFAULT NULL,
  `top_destination_country` char(2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `delivery_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipment_id` int(11) NOT NULL,
  `attempt_number` int(11) NOT NULL,
  `attempted_at` datetime NOT NULL,
  `result` enum('success','failed','partial','rescheduled') NOT NULL,
  `signature_name` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `attempted_by` int(11) DEFAULT NULL,
  `rescheduled_to` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `shipment_id` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `delivery_zones` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(20) NOT NULL,
  `country_code` char(2) NOT NULL,
  `region_type` enum('domestic','international','regional','metro') DEFAULT 'domestic',
  `base_rate_multiplier` decimal(5,3) DEFAULT 1.000,
  `tax_rate` decimal(6,4) DEFAULT 0.0000,
  `customs_fee` decimal(10,2) DEFAULT 0.00,
  `handling_fee` decimal(10,2) DEFAULT 0.00,
  `min_delivery_days` int(11) DEFAULT 1,
  `max_delivery_days` int(11) DEFAULT 7,
  `active` tinyint(1) DEFAULT 1,
  `geo_boundaries` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'GeoJSON polygon for zone' CHECK (json_valid(`geo_boundaries`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `document_queue` (
  `id` bigint(20) NOT NULL,
  `queue_uuid` char(36) DEFAULT uuid(),
  `document_type` enum('invoice','receipt','packing_slip','customs_form','label','report','other') NOT NULL,
  `entity_type` varchar(50) NOT NULL COMMENT 'Shipment, Invoice, etc.',
  `entity_id` int(11) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `format` enum('pdf','html','docx','xlsx','csv') DEFAULT 'pdf',
  `output_path` varchar(500) DEFAULT NULL,
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `attempts` int(11) DEFAULT 0,
  `max_attempts` int(11) DEFAULT 3,
  `last_error` text DEFAULT NULL,
  `status` enum('pending','processing','completed','failed','cancelled') DEFAULT 'pending',
  `scheduled_for` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `failed_at` timestamp NULL DEFAULT NULL,
  `generated_by` int(11) DEFAULT NULL,
  `delivered_to` varchar(500) DEFAULT NULL COMMENT 'Email or URL',
  `delivered_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `flagging_rules` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `rule_type` enum('value_threshold','country_risk','customer_behavior','route_pattern','frequency','custom') DEFAULT 'value_threshold',
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Rule conditions in JSON format' CHECK (json_valid(`conditions`)),
  `flag_type` varchar(50) NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `risk_score` int(11) DEFAULT NULL COMMENT '0-100',
  `is_active` tinyint(1) DEFAULT 1,
  `is_system` tinyint(1) DEFAULT 0 COMMENT 'Cannot be deleted',
  `created_by` int(11) DEFAULT NULL,
  `triggers_count` int(11) DEFAULT 0,
  `last_triggered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `shipment_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `tax_amount` decimal(12,2) DEFAULT 0.00,
  `discount_amount` decimal(12,2) DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `status` enum('draft','sent','paid','overdue','cancelled','refunded') DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('hub','warehouse','service_point','pickup_point') DEFAULT 'hub',
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` char(2) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `operating_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`operating_hours`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `manager_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `manager_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `company` varchar(150) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `mode_bookings` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `services` varchar(15) NOT NULL,
  `deliverytime` varchar(12) NOT NULL,
  `observations` text NOT NULL,
  `estado` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('shipment_created','picked_up','in_transit','out_for_delivery','delivered','cancelled','payment_due','payment_received','system') DEFAULT 'system',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `offices` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `off_name` varchar(100) NOT NULL,
  `address` varchar(230) NOT NULL,
  `city` varchar(100) NOT NULL,
  `ph_no` varchar(20) NOT NULL,
  `office_time` varchar(100) NOT NULL,
  `contact_person` varchar(100) NOT NULL,
  `estado` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `online_booking` (
  `id` int(100) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `address` varchar(200) NOT NULL,
  `note` varchar(200) NOT NULL,
  `name_delivery` varchar(200) NOT NULL,
  `email_delivery` varchar(200) NOT NULL,
  `phone_delivery` varchar(200) NOT NULL,
  `company_delivery` varchar(200) NOT NULL,
  `address_delivery` varchar(200) NOT NULL,
  `scountry` varchar(100) NOT NULL,
  `sstate` varchar(100) NOT NULL,
  `dcountry` varchar(100) NOT NULL,
  `dstate` varchar(100) NOT NULL,
  `type` varchar(100) NOT NULL,
  `service` varchar(200) NOT NULL,
  `courier_name` varchar(200) NOT NULL,
  `freight` varchar(20) NOT NULL,
  `Qnty` varchar(255) NOT NULL,
  `width` varchar(100) NOT NULL,
  `height` varchar(100) NOT NULL,
  `weight` varchar(100) NOT NULL,
  `length` varchar(100) NOT NULL,
  `booking_date` date NOT NULL,
  `collection_date` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `reasons` varchar(255) NOT NULL,
  `delivery` varchar(255) NOT NULL,
  `tracking` varchar(100) NOT NULL,
  `officename` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tracking` (`tracking`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `package_dimensions_history` (
  `id` bigint(20) NOT NULL,
  `package_id` int(11) NOT NULL,
  `weight` decimal(8,3) NOT NULL,
  `length` decimal(8,2) DEFAULT NULL,
  `width` decimal(8,2) DEFAULT NULL,
  `height` decimal(8,2) DEFAULT NULL,
  `volume` decimal(10,4) DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `change_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipment_id` int(11) NOT NULL,
  `package_number` varchar(50) NOT NULL,
  `weight` decimal(10,2) DEFAULT NULL,
  `length` decimal(10,2) DEFAULT NULL,
  `width` decimal(10,2) DEFAULT NULL,
  `height` decimal(10,2) DEFAULT NULL,
  `dimension_unit` enum('cm','in') DEFAULT 'cm',
  `volume_weight` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `shipment_id` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference_number` varchar(50) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `shipment_id` int(11) DEFAULT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `payment_method` enum('cash','credit_card','debit_card','bank_transfer','paypal','stripe','check','other') NOT NULL,
  `status` enum('pending','completed','failed','refunded','cancelled') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference_number` (`reference_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `picking_orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `status` enum('pending','picking','picked','packing','packed','shipped','cancelled') DEFAULT 'pending',
  `priority` enum('low','normal','high','urgent') DEFAULT 'normal',
  `assigned_to` int(11) DEFAULT NULL,
  `picker_id` int(11) DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `total_items` int(11) DEFAULT 0,
  `picked_items` int(11) DEFAULT 0,
  `picking_route` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Optimized picking path' CHECK (json_valid(`picking_route`)),
  `picking_list` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Items to pick' CHECK (json_valid(`picking_list`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `price_history` (
  `id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `origin_zone_id` int(11) DEFAULT NULL,
  `destination_zone_id` int(11) DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `price_per_kg` decimal(8,2) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `change_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed','free_shipping') DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_value` decimal(10,2) DEFAULT 0.00,
  `max_discount_amount` decimal(10,2) DEFAULT NULL,
  `applicable_services` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of service IDs' CHECK (json_valid(`applicable_services`)),
  `applicable_zones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of zone IDs' CHECK (json_valid(`applicable_zones`)),
  `usage_limit` int(11) DEFAULT NULL COMMENT 'Max uses',
  `usage_count` int(11) DEFAULT 0,
  `per_customer_limit` int(11) DEFAULT 1,
  `valid_from` date NOT NULL,
  `valid_to` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `receipt_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `template_key` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `html_template` longtext NOT NULL,
  `css_styles` longtext DEFAULT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Available template variables' CHECK (json_valid(`variables`)),
  `company_logo_url` varchar(500) DEFAULT NULL,
  `company_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`company_details`)),
  `footer_text` text DEFAULT NULL,
  `terms_conditions` text DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `receipts` (
  `id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `payment_method` enum('card','bank_transfer','mobile_money','cash','paypal','stripe') NOT NULL,
  `receipt_type` enum('payment','refund','adjustment') DEFAULT 'payment',
  `status` enum('generated','emailed','printed','voided') DEFAULT 'generated',
  `generated_by` int(11) DEFAULT NULL,
  `pdf_path` varchar(500) DEFAULT NULL,
  `email_sent_at` timestamp NULL DEFAULT NULL,
  `printed_at` timestamp NULL DEFAULT NULL,
  `voided_at` timestamp NULL DEFAULT NULL,
  `void_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `report_schedules` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('shipments','revenue','users','performance','custom') DEFAULT 'custom',
  `query_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`query_config`)),
  `schedule_type` enum('daily','weekly','monthly','quarterly','yearly','once') DEFAULT 'daily',
  `schedule_time` time DEFAULT '00:00:00',
  `schedule_day` tinyint(4) DEFAULT NULL COMMENT 'Day of week (0=Sunday) or month (1-31)',
  `recipients` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Array of email addresses' CHECK (json_valid(`recipients`)),
  `format` enum('csv','pdf','excel','json') DEFAULT 'csv',
  `last_run_at` timestamp NULL DEFAULT NULL,
  `next_run_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `restricted_item_detections` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `detection_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `detection_method` enum('keyword','ai_ml','hs_code','manual','risk_engine') DEFAULT 'keyword',
  `restricted_category` varchar(100) NOT NULL,
  `item_description` text DEFAULT NULL,
  `hs_code` varchar(50) DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT 0.00,
  `is_true_positive` tinyint(1) DEFAULT NULL,
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `action_taken` enum('flag','hold','seize','release') DEFAULT 'flag',
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `flagged_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`flagged_rules`)),
  `screening_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`screening_result`)),
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `scheduledpickup` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `name_courier` varchar(20) NOT NULL,
  `courier` varchar(255) NOT NULL,
  `rate` varchar(200) NOT NULL,
  `services` varchar(20) NOT NULL,
  `Length` varchar(20) NOT NULL,
  `Width` varchar(20) NOT NULL,
  `Height` varchar(20) NOT NULL,
  `Weight` int(20) NOT NULL,
  `WeightType` varchar(25) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `price_per_kg` decimal(8,2) NOT NULL,
  `estimated_days_min` int(11) DEFAULT NULL,
  `estimated_days_max` int(11) DEFAULT NULL,
  `priority` enum('low','standard','high','express') DEFAULT 'standard',
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `setting_changes` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `old_value` longtext DEFAULT NULL,
  `new_value` longtext NOT NULL,
  `changed_by` int(11) NOT NULL,
  `change_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settlement_payments` (
  `id` int(11) NOT NULL,
  `settlement_id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` enum('shipment','adjustment','refund','fee','tax') DEFAULT 'shipment',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settlements` (
  `id` int(11) NOT NULL,
  `reference_number` varchar(100) NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `total_shipments` int(11) DEFAULT 0,
  `total_revenue` decimal(12,2) DEFAULT 0.00,
  `total_expenses` decimal(12,2) DEFAULT 0.00,
  `net_profit` decimal(12,2) DEFAULT 0.00,
  `currency` varchar(3) DEFAULT 'USD',
  `status` enum('draft','pending','paid','overdue','cancelled') DEFAULT 'draft',
  `payment_due_date` date DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `generated_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipment_discounts` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `promotion_id` int(11) DEFAULT NULL,
  `discount_code` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `applied_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipment_exceptions` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `exception_type` enum('delay','damage','loss','customs_hold','address_issue','payment_issue','weather','other') NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `reported_by` int(11) DEFAULT NULL,
  `reported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `impact_on_delivery` int(11) DEFAULT NULL COMMENT 'Additional days',
  `notification_sent` tinyint(1) DEFAULT 0,
  `escalation_level` tinyint(4) DEFAULT 0 COMMENT '0=None, 1=Supervisor, 2=Manager, 3=Director',
  `escalated_at` timestamp NULL DEFAULT NULL,
  `escalated_to` int(11) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipment_flags` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `flag_type` enum('high_value','suspicious','restricted','compliance','address_issue','payment_risk','customs_risk','customer_risk','route_risk','other') NOT NULL,
  `flag_code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `risk_score` decimal(4,2) DEFAULT NULL COMMENT '0-100 risk score',
  `auto_generated` tinyint(1) DEFAULT 0,
  `rule_id` int(11) DEFAULT NULL COMMENT 'Reference to flagging rule',
  `flagged_by` int(11) DEFAULT NULL COMMENT 'Manual flag by staff',
  `flagged_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `acknowledged_by` int(11) DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `related_shipment_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Related shipments for pattern detection' CHECK (json_valid(`related_shipment_ids`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipment_holds` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `hold_type` enum('customs','payment','address_verification','inspection','regulatory','weather','operational','customer_request','other') NOT NULL,
  `reason_code` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `held_by` int(11) DEFAULT NULL COMMENT 'Staff who placed hold',
  `held_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expected_release_date` date DEFAULT NULL,
  `auto_release` tinyint(1) DEFAULT 0 COMMENT 'Auto-release after X hours',
  `auto_release_after_hours` int(11) DEFAULT NULL,
  `released_by` int(11) DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `release_notes` text DEFAULT NULL,
  `sla_deadline` timestamp NULL DEFAULT NULL,
  `sla_breached` tinyint(1) DEFAULT 0,
  `related_document_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Related docs or references' CHECK (json_valid(`related_document_ids`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipment_routes` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `route_number` varchar(50) NOT NULL,
  `origin_warehouse_id` int(11) DEFAULT NULL,
  `destination_warehouse_id` int(11) DEFAULT NULL,
  `origin_address` text NOT NULL,
  `destination_address` text NOT NULL,
  `origin_city` varchar(100) NOT NULL,
  `destination_city` varchar(100) NOT NULL,
  `origin_country` char(2) NOT NULL DEFAULT 'US',
  `destination_country` char(2) NOT NULL DEFAULT 'US',
  `origin_postal_code` varchar(20) DEFAULT NULL,
  `destination_postal_code` varchar(20) DEFAULT NULL,
  `total_distance_km` decimal(8,2) DEFAULT NULL,
  `estimated_duration_minutes` int(11) DEFAULT NULL,
  `route_type` enum('direct','multi_stop','hub_and_spoke') DEFAULT 'direct',
  `optimization_status` enum('pending','optimized','manual','dispatched') DEFAULT 'pending',
  `driver_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `actual_distance_km` decimal(8,2) DEFAULT NULL,
  `actual_duration_minutes` int(11) DEFAULT NULL,
  `route_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'GeoJSON route geometry' CHECK (json_valid(`route_data`)),
  `stops` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of stop coordinates' CHECK (json_valid(`stops`)),
  `optimization_score` decimal(5,2) DEFAULT NULL,
  `planned_departure` timestamp NULL DEFAULT NULL,
  `planned_arrival` timestamp NULL DEFAULT NULL,
  `actual_departure` timestamp NULL DEFAULT NULL,
  `actual_arrival` timestamp NULL DEFAULT NULL,
  `status` enum('planned','in_progress','completed','cancelled') DEFAULT 'planned',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shipment_status_definitions` (
  `status_code` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `category` enum('pre_transit','in_transit','customs','out_for_delivery','delivered','exception','returned') NOT NULL,
  `is_terminal` tinyint(1) DEFAULT 0,
  `color` varchar(20) DEFAULT 'secondary',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `shipment_status_history` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `status` enum('pending','processing','picked_up','at_warehouse','in_transit','at_hub','customs_inspection','customs_clearance','customs_delayed','customs_seized','held','security_check','shipment_stopped','out_for_delivery','delivery_attempt_failed','rescheduled','delivered','returned','cancelled') NOT NULL,
  `location` varchar(255) DEFAULT NULL COMMENT 'City, Country',
  `latitude` decimal(10,7) DEFAULT NULL COMMENT 'GPS coordinates',
  `longitude` decimal(10,7) DEFAULT NULL COMMENT 'GPS coordinates',
  `location_name` varchar(255) DEFAULT NULL,
  `location_type` enum('address','gps','warehouse','hub','customs','other') DEFAULT 'address',
  `reason_code` varchar(50) DEFAULT NULL,
  `reason_description` text DEFAULT NULL,
  `requires_followup` tinyint(1) DEFAULT 0,
  `followup_due_date` date DEFAULT NULL,
  `occurred_at` timestamp NULL DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `notes` text DEFAULT NULL,
  `event_type` enum('scan','gps','manual','system') DEFAULT 'scan',
  `occurred_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shipment_status_history_v2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shipment_id` int(11) NOT NULL,
  `status_code` varchar(50) NOT NULL,
  `occurred_at` datetime NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `occurred_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `shipment_id` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shipments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tracking_number` varchar(100) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `status` enum('pending','processing','picked_up','at_warehouse','in_transit','at_hub','customs_inspection','customs_clearance','customs_delayed','customs_seized','held','out_for_delivery','delivered','returned','cancelled') DEFAULT 'pending',
  `service_type` varchar(50) DEFAULT 'standard',
  `priority` enum('low','standard','high','express') DEFAULT 'standard',
  `origin_country` char(2) NOT NULL,
  `origin_city` varchar(100) NOT NULL,
  `destination_country` char(2) NOT NULL,
  `destination_city` varchar(100) NOT NULL,
  `total_weight` decimal(10,2) DEFAULT NULL,
  `total_volume` decimal(10,2) DEFAULT NULL,
  `declared_value` decimal(12,2) DEFAULT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `pieces` int(11) DEFAULT 1,
  `is_fragile` tinyint(1) DEFAULT 0,
  `is_insured` tinyint(1) DEFAULT 0,
  `insurance_amount` decimal(12,2) DEFAULT NULL,
  `payment_status` enum('pending','paid','partial','refunded','cancelled') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `total_amount` decimal(12,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `special_instructions` text DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `actual_delivery` datetime DEFAULT NULL,
  `delivered_by` int(11) DEFAULT NULL,
  `signature_image` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
   -- Courier management extension (added by migration 0002; applied via sync_schema.php)
   `shipment_type` varchar(50) DEFAULT 'parcel',
  `shipment_date` date DEFAULT NULL,
  `sender_name` varchar(255) DEFAULT NULL,
  `sender_company` varchar(255) DEFAULT NULL,
  `sender_phone` varchar(90) DEFAULT NULL,
  `sender_email` varchar(255) DEFAULT NULL,
  `sender_address` varchar(300) DEFAULT NULL,
  `sender_city` varchar(100) DEFAULT NULL,
  `sender_state` varchar(120) DEFAULT NULL,
  `sender_postal` varchar(20) DEFAULT NULL,
  `sender_country` char(2) DEFAULT 'US',
  `receiver_name` varchar(255) DEFAULT NULL,
  `receiver_company` varchar(255) DEFAULT NULL,
  `receiver_phone` varchar(90) DEFAULT NULL,
  `receiver_email` varchar(255) DEFAULT NULL,
  `receiver_address` varchar(300) DEFAULT NULL,
  `receiver_city` varchar(100) DEFAULT NULL,
  `receiver_state` varchar(120) DEFAULT NULL,
  `receiver_postal` varchar(20) DEFAULT NULL,
  `receiver_country` char(2) DEFAULT 'US',
  `package_name` varchar(255) DEFAULT NULL,
  `package_description` text DEFAULT NULL,
  `length` decimal(10,2) DEFAULT NULL,
  `width` decimal(10,2) DEFAULT NULL,
  `height` decimal(10,2) DEFAULT NULL,
  `volumetric_weight` decimal(10,2) DEFAULT NULL,
  `contents` text DEFAULT NULL,
  `cod_amount` decimal(12,2) DEFAULT 0.00,
  `driver_id` int(11) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `current_city` varchar(100) DEFAULT NULL,
  `current_country` char(2) DEFAULT NULL,
  `current_branch` varchar(255) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `delivery_time` time DEFAULT NULL,
  `pod_photo` varchar(255) DEFAULT NULL,
  `internal_notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tracking_number` (`tracking_number`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `staff_logs` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_analytics_daily` (
  `id` int(11) NOT NULL,
  `stat_date` date NOT NULL,
  `status_code` varchar(50) NOT NULL,
  `total_count` int(11) DEFAULT 0,
  `new_count` int(11) DEFAULT 0 COMMENT 'Entered this status today',
  `completed_count` int(11) DEFAULT 0 COMMENT 'Left this status today',
  `avg_duration_hours` decimal(8,2) DEFAULT 0.00 COMMENT 'Avg time in status',
  `max_duration_hours` decimal(8,2) DEFAULT 0.00,
  `min_duration_hours` decimal(8,2) DEFAULT 0.00,
  `sla_breach_count` int(11) DEFAULT 0,
  `manual_count` int(11) DEFAULT 0 COMMENT 'Manual transitions',
  `auto_count` int(11) DEFAULT 0 COMMENT 'Automatic transitions',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_assignment_rules` (
  `id` int(11) NOT NULL,
  `rule_name` varchar(255) NOT NULL,
  `trigger_status` varchar(50) NOT NULL COMMENT 'When status becomes this',
  `assign_to_role` varchar(50) DEFAULT NULL COMMENT 'Role to assign',
  `assign_to_specific_user` int(11) DEFAULT NULL,
  `assign_to_team` varchar(100) DEFAULT NULL,
  `assignment_logic` enum('round_robin','least_busy','specific_user','specific_team','random') DEFAULT 'least_busy',
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional conditions' CHECK (json_valid(`conditions`)),
  `priority` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_automation_log` (
  `id` bigint(20) NOT NULL,
  `rule_id` int(11) DEFAULT NULL,
  `shipment_id` int(11) NOT NULL,
  `action_taken` varchar(100) NOT NULL,
  `action_result` enum('success','failed','skipped') DEFAULT 'success',
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_automation_rules` (
  `id` int(11) NOT NULL,
  `rule_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `trigger_status` varchar(50) NOT NULL COMMENT 'When status becomes this',
  `condition_type` enum('always','time_elapsed','custom_field','customs_hold','payment_due','risk_score','geofence') DEFAULT 'always',
  `condition_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Condition parameters' CHECK (json_valid(`condition_config`)),
  `action_type` enum('transition','notify','hold','flag','escalate','assign','update_field','custom') NOT NULL,
  `action_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Action parameters' CHECK (json_valid(`action_config`)),
  `priority` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_system` tinyint(1) DEFAULT 0,
  `execution_count` int(11) DEFAULT 0,
  `last_executed_at` timestamp NULL DEFAULT NULL,
  `last_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`last_result`)),
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_change_audit` (
  `id` bigint(20) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `change_id` int(11) NOT NULL COMMENT 'Reference to status_history_v2.id',
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) NOT NULL,
  `changed_by` int(11) DEFAULT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `session_id` varchar(128) DEFAULT NULL,
  `request_method` varchar(10) DEFAULT NULL,
  `request_url` text DEFAULT NULL,
  `reason_text` text DEFAULT NULL,
  `validation_passed` tinyint(1) DEFAULT NULL COMMENT 'Did transition validation pass?',
  `validation_errors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`validation_errors`)),
  `automation_source` varchar(100) DEFAULT NULL COMMENT 'Which automation triggered',
  `data_snapshot` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Shipment data at transition' CHECK (json_valid(`data_snapshot`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_change_requests` (
  `id` int(11) NOT NULL,
  `request_uuid` char(36) DEFAULT uuid(),
  `shipment_id` int(11) NOT NULL,
  `from_status` varchar(50) NOT NULL,
  `to_status` varchar(50) NOT NULL,
  `requested_by` int(11) NOT NULL,
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reason_code` varchar(50) DEFAULT NULL,
  `reason_description` text DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `required_approval_role` varchar(50) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_notes` text DEFAULT NULL,
  `denied_by` int(11) DEFAULT NULL,
  `denied_at` timestamp NULL DEFAULT NULL,
  `denial_reason` text DEFAULT NULL,
  `status` enum('pending','approved','denied','cancelled') DEFAULT 'pending',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_dashboard_configs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `widget_type` varchar(50) NOT NULL COMMENT 'chart, table, metric, map',
  `widget_name` varchar(100) NOT NULL,
  `config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Chart config, filters, etc.' CHECK (json_valid(`config`)),
  `position` int(11) DEFAULT 0,
  `size` enum('xs','small','medium','large','xl') DEFAULT 'medium',
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_notification_logs` (
  `id` bigint(20) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `status_history_id` bigint(20) NOT NULL,
  `notification_type` enum('email','sms','push','in_app') NOT NULL,
  `recipient_type` enum('customer','staff','admin','other') DEFAULT 'customer',
  `recipient_id` int(11) DEFAULT NULL,
  `recipient_address` varchar(500) NOT NULL,
  `template_key` varchar(100) DEFAULT NULL,
  `subject` varchar(500) DEFAULT NULL,
  `body` longtext DEFAULT NULL,
  `status` enum('pending','sent','delivered','failed','bounced','opened') DEFAULT 'pending',
  `provider_message_id` varchar(255) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `opened_at` timestamp NULL DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_notification_templates` (
  `id` int(11) NOT NULL,
  `status_code` varchar(50) NOT NULL,
  `notification_type` enum('email','sms','push','in_app') DEFAULT 'email',
  `template_key` varchar(100) NOT NULL,
  `subject_template` longtext DEFAULT NULL,
  `body_template` longtext NOT NULL,
  `sms_template` text DEFAULT NULL COMMENT 'For SMS, shorter version',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `send_to_customer` tinyint(1) DEFAULT 1,
  `send_to_staff` tinyint(1) DEFAULT 0,
  `send_to_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Roles to notify' CHECK (json_valid(`send_to_roles`)),
  `delay_minutes` int(11) DEFAULT 0 COMMENT 'Delay before sending',
  `max_frequency_per_hour` int(11) DEFAULT NULL COMMENT 'Rate limit',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_projections` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `current_status` varchar(50) NOT NULL,
  `projected_next_status` varchar(50) DEFAULT NULL,
  `estimated_transition_at` timestamp NULL DEFAULT NULL,
  `confidence_score` decimal(5,2) DEFAULT 0.00 COMMENT '0-100 prediction confidence',
  `prediction_model` varchar(100) DEFAULT 'rule_based' COMMENT 'Algorithm used',
  `factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Factors considered' CHECK (json_valid(`factors`)),
  `actual_transition_at` timestamp NULL DEFAULT NULL,
  `accuracy_hours` int(11) DEFAULT NULL COMMENT 'Difference in hours',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_rate_impacts` (
  `id` int(11) NOT NULL,
  `status_code` varchar(50) NOT NULL,
  `impact_type` enum('surcharge','discount','penalty','bonus','adjustment') DEFAULT 'surcharge',
  `amount_type` enum('fixed','percentage','per_day','per_hour') DEFAULT 'fixed',
  `amount_value` decimal(10,2) NOT NULL,
  `currency` varchar(3) DEFAULT 'USD',
  `min_duration_minutes` int(11) DEFAULT NULL COMMENT 'Only applies after this duration',
  `max_duration_minutes` int(11) DEFAULT NULL COMMENT 'Only applies before this duration',
  `applicable_services` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Service IDs or null = all' CHECK (json_valid(`applicable_services`)),
  `applicable_zones` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Zone IDs or null = all' CHECK (json_valid(`applicable_zones`)),
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `valid_from` date NOT NULL,
  `valid_to` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_slas` (
  `id` int(11) NOT NULL,
  `status_code` varchar(50) NOT NULL,
  `target_status` varchar(50) DEFAULT NULL COMMENT 'Status to reach within SLA',
  `max_duration_minutes` int(11) NOT NULL COMMENT 'Max time in this status',
  `warning_threshold_pct` decimal(5,2) DEFAULT 75.00 COMMENT 'Alert before SLA breach',
  `breach_escalation_level` tinyint(4) DEFAULT 1 COMMENT '1=Staff, 2=Supervisor, 3=Manager',
  `auto_escalate` tinyint(1) DEFAULT 0,
  `escalation_to_role` varchar(50) DEFAULT NULL,
  `sla_type` enum('operational','customer','compliance','financial') DEFAULT 'operational',
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `status_transition_rules` (
  `id` int(11) NOT NULL,
  `from_status` varchar(50) NOT NULL,
  `to_status` varchar(50) NOT NULL,
  `is_allowed` tinyint(1) DEFAULT 1,
  `requires_validation` enum('none','location','signature','photo','customs_documents','payment','inventory') DEFAULT 'none',
  `validation_message` text DEFAULT NULL,
  `can_be_reversed` tinyint(1) DEFAULT 0 COMMENT 'Can go back to from_status',
  `reverse_transition_id` int(11) DEFAULT NULL,
  `priority` int(11) DEFAULT 0 COMMENT 'Higher = more preferred',
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON conditions for transition' CHECK (json_valid(`conditions`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subir_imagen` (
  `id` int(11) NOT NULL,
  `nombre_imagen` text NOT NULL,
  `imagen` mediumblob NOT NULL,
  `tipo` text NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `support_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `sla_hours` int(11) DEFAULT NULL COMMENT 'Response time SLA in hours',
  `assign_to_team` varchar(100) DEFAULT NULL,
  `auto_close_days` int(11) DEFAULT NULL COMMENT 'Auto-close after X days of inactivity',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `support_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_internal` tinyint(1) DEFAULT 0,
  `attachment_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ticket_id` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_number` varchar(50) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `status` enum('open','in_progress','waiting_customer','resolved','closed','reopened') DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `category` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `sla_breach` tinyint(1) DEFAULT 0,
  `resolution` text DEFAULT NULL,
  `resolved_at` datetime DEFAULT NULL,
  `closed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_number` (`ticket_number`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_alerts` (
  `id` int(11) NOT NULL,
  `alert_type` enum('error','warning','info','security','performance','business') DEFAULT 'info',
  `severity` enum('debug','low','medium','high','critical') DEFAULT 'medium',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `metric_name` varchar(100) DEFAULT NULL,
  `metric_value` decimal(15,4) DEFAULT NULL,
  `threshold_value` decimal(15,4) DEFAULT NULL,
  `triggered_by` varchar(100) DEFAULT NULL,
  `affected_entities` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`affected_entities`)),
  `action_required` tinyint(1) DEFAULT 0,
  `auto_resolve` tinyint(1) DEFAULT 0,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `acknowledged_by` int(11) DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(150) NOT NULL,
  `value` longtext DEFAULT NULL,
  `data_type` enum('string','integer','float','boolean','json','encrypted') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL COMMENT 'general, shipping, customs, notification, etc.',
  `is_public` tinyint(1) DEFAULT 0,
  `is_system` tinyint(1) DEFAULT 0,
  `is_encrypted` tinyint(1) DEFAULT 0,
  `validation_rules` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`validation_rules`)),
  `ui_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Display hints for UI' CHECK (json_valid(`ui_config`)),
  `version` int(11) DEFAULT 1 COMMENT 'For optimistic locking',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `system_notifications` (
  `id` int(11) NOT NULL,
  `type` enum('info','warning','error','success','maintenance') DEFAULT 'info',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `target_roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of roles to notify, null = all' CHECK (json_valid(`target_roles`)),
  `target_user_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Specific users' CHECK (json_valid(`target_user_ids`)),
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `action_url` varchar(500) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-bell',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `company` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `templates` (
  `id` int(11) NOT NULL,
  `type` enum('email','sms') DEFAULT 'email',
  `name` varchar(100) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `template_key` varchar(100) NOT NULL,
  `content` longtext NOT NULL,
  `variables` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Available template variables' CHECK (json_valid(`variables`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tracking_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tracking_number` varchar(100) NOT NULL,
  `shipment_id` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 1,
  `occurred_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tracking_number` (`tracking_number`),
  KEY `shipment_id` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `trade_verifications` (
  `id` int(11) NOT NULL,
  `shipment_id` int(11) NOT NULL,
  `verification_type` enum('import','export','transit','re-export') NOT NULL,
  `verification_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_by` int(11) DEFAULT NULL,
  `status` enum('pending','verified','failed','requires_review') DEFAULT 'pending',
  `trade_compliance` tinyint(1) DEFAULT 1,
  `sanctions_check` tinyint(1) DEFAULT 1,
  `embargo_check` tinyint(1) DEFAULT 1,
  `export_control` tinyint(1) DEFAULT 1,
  `limitations_apply` tinyint(1) DEFAULT 0,
  `limitations_reason` text DEFAULT NULL,
  `license_required` tinyint(1) DEFAULT 0,
  `license_number` varchar(100) DEFAULT NULL,
  `certificate_of_origin` tinyint(1) DEFAULT 0,
  `preferential_treatment` tinyint(1) DEFAULT 0,
  `origin_country_verified` tinyint(1) DEFAULT 0,
  `destination_country_verified` tinyint(1) DEFAULT 0,
  `checks_performed` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`checks_performed`)),
  `issues_found` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`issues_found`)),
  `resolution_notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `type_shipments` (
  `id` int(5) NOT NULL,
  `name` varchar(45) NOT NULL,
  `packaging` varchar(15) NOT NULL,
  `dimensions` varchar(12) NOT NULL,
  `estado` tinyint(1) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `upload_image_bank` (
  `cid` int(11) NOT NULL AUTO_INCREMENT,
  `cons_no` varchar(150) NOT NULL,
  `nombre_imagen` text NOT NULL,
  `imagen` mediumblob NOT NULL,
  `tipo` text NOT NULL,
  `office` varchar(255) NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','staff','admin') DEFAULT 'customer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` int(11) NOT NULL,
  `registration_number` varchar(50) NOT NULL,
  `type` enum('van','truck','car','motorcycle','other') DEFAULT 'van',
  `make` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `capacity_kg` decimal(8,3) DEFAULT 0.000,
  `volume_m3` decimal(6,3) DEFAULT 0.000,
  `assigned_to` int(11) DEFAULT NULL COMMENT 'Staff assigned to vehicle',
  `location_id` int(11) DEFAULT NULL COMMENT 'Current hub',
  `status` enum('active','maintenance','inactive') DEFAULT 'active',
  `gps_device_id` varchar(100) DEFAULT NULL,
  `insurance_expiry` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `warehouse_zones` (
  `id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `zone_code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `zone_type` enum('receiving','storage','picking','packing','shipping','quarantine','returns','oversized') DEFAULT 'storage',
  `description` text DEFAULT NULL,
  `max_capacity` int(11) DEFAULT NULL,
  `current_capacity` int(11) DEFAULT 0,
  `temperature_controlled` tinyint(1) DEFAULT 0,
  `requires_racking` tinyint(1) DEFAULT 0,
  `access_restricted` tinyint(1) DEFAULT 0,
  `authorized_user_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`authorized_user_ids`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('fulfillment','distribution','cross_dock','return_processing','bonded') DEFAULT 'fulfillment',
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` char(2) NOT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT 'UTC',
  `operating_hours` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Daily operating hours' CHECK (json_valid(`operating_hours`)),
  `capacity_sqft` decimal(10,2) DEFAULT NULL,
  `capacity_pallets` int(11) DEFAULT NULL,
  `current_pallets` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `is_primary` tinyint(1) DEFAULT 0 COMMENT 'Main hub for region',
  `customs_approved` tinyint(1) DEFAULT 0 COMMENT 'Bonded warehouse',
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Storage features, equipment, etc.' CHECK (json_valid(`features`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `webhook_events` (
  `id` bigint(20) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`payload`)),
  `target_url` varchar(500) NOT NULL,
  `method` enum('POST','PUT','PATCH') DEFAULT 'POST',
  `status` enum('pending','sent','failed','retrying') DEFAULT 'pending',
  `response_code` int(11) DEFAULT NULL,
  `response_body` text DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `zones` (
  `id` int(11) NOT NULL,
  `code` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `country_code` char(2) NOT NULL,
  `region_type` enum('domestic','international','regional') DEFAULT 'domestic',
  `base_rate_multiplier` decimal(5,2) DEFAULT 1.00,
  `tax_rate` decimal(5,4) DEFAULT 0.0000,
  `customs_fee` decimal(10,2) DEFAULT 0.00,
  `handling_fee` decimal(10,2) DEFAULT 0.00,
  `active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================
-- INSERTS
-- ==========================================

INSERT IGNORE INTO `offices` (`id`, `off_name`, `address`, `city`, `ph_no`, `office_time`, `contact_person`, `estado`) VALUES
(1, 'Americans Shipping & Courier Logistics - USA West Coast Hub', '4500 Harbor Boulevard, Long Beach, CA 90802', 'Long Beach', '+1-562-544-7890', 'Mon-Fri 8:00AM - 6:00PM', 'Sarah Mitchell', 1),
(2, 'Americans Shipping & Courier Logistics - United Kingdom', 'Port of Felixstowe, Suffolk, IP11 3SY', 'Felixstowe', '+44 (0)1394 671 890', 'Mon-Fri 8:00AM - 5:00PM', 'Michael Chen', 1),
(3, 'Americans Shipping & Courier Logistics - Germany Central Europe', 'Port of Hamburg, Kurt-Wagner-Strasse 1, 21129', 'Hamburg', '+49 40 3118 0', 'Mon-Fri 8:00AM - 5:00PM', 'Hans Mueller', 1),
(4, 'Americans Shipping & Courier Logistics - UAE Middle East', 'Jebel Ali Port, Dubai, United Arab Emirates', 'Dubai', '+971 4 881 4400', 'Sun-Thu 8:00AM - 5:00PM', 'Ahmed Al-Rashid', 1),
(5, 'Americans Shipping & Courier Logistics - China Shanghai', 'Yangshan Deep-Water Port, Shanghai 201307', 'Shanghai', '+86 21 6829 7800', 'Mon-Fri 8:00AM - 5:30PM', 'Wei Zhang', 1);

INSERT IGNORE INTO `mode_bookings` (`id`, `name`, `services`, `deliverytime`, `observations`, `estado`) VALUES
(1, 'SHIP', 'Container shipping', 'FROM MONDAY', 'Handling, palletizing, storage, packaging, door-to-door services', 1),
(2, 'HIGH ROAD', 'Vans and tracto', 'FROM MONDAY', 'Dedicated fleet for time-sensitive road freight', 1),
(3, 'COURIER', 'Economic and express', 'FROM MONDAY', 'Same-city and next-day courier services', 1),
(4, 'EXPRESS', 'Operating at high speed', 'FROM MONDAY', 'Priority handling with guaranteed delivery windows', 1);

INSERT IGNORE INTO `calculator` (`id`, `currency`, `normal`, `express`) VALUES
(1, 'USD', 0.0700, 0.0900),
(2, 'EUR', 0.0650, 0.0850);

INSERT IGNORE INTO `company` (`id`, `name`, `email`, `phone`, `address`, `website`, `tax_id`) VALUES
(1, 'Americans Shipping & Courier Logistics', 'info@ascl-logistics.com', '+12158159791', '4500 Harbor Boulevard, Long Beach, CA 90802, USA', 'https://www.ascl-logistics.com', 'US-123456789');

INSERT IGNORE INTO `shipments` (`id`, `tracking_number`, `customer_id`, `status`, `service_type`, `priority`, `origin_country`, `origin_city`, `destination_country`, `destination_city`, `total_weight`, `declared_value`, `currency`, `payment_status`, `payment_method`, `total_amount`, `notes`, `estimated_delivery`, `created_by`, `created_at`) VALUES
(1, 'LX-2024-001', 1, 'in_transit', 'express', 'high', 'US', 'Miami', 'US', 'Chicago', 2.50, 500.00, 'USD', 'paid', 'credit_card', 45.00, 'Time-sensitive documents', '2024-01-18', 1, NOW()),
(2, 'LX-2024-002', 1, 'delivered', 'standard', 'standard', 'US', 'Los Angeles', 'US', 'Seattle', 150.00, 15000.00, 'USD', 'paid', 'bank_transfer', 320.00, 'Commercial freight', '2024-01-12', 1, NOW()),
(3, 'LX-2024-003', 2, 'out_for_delivery', 'standard', 'standard', 'US', 'New York', 'US', 'Dallas', 0.50, 150.00, 'USD', 'paid', 'credit_card', 28.00, 'Important documents', '2024-01-18', 1, NOW());

INSERT IGNORE INTO `tracking_logs` (`id`, `tracking_number`, `shipment_id`, `status`, `location`, `description`, `is_public`, `occurred_at`) VALUES
(1, 'LX-2024-001', 1, 'Booked', 'Miami Hub', 'Shipment booked at Americans Shipping & Courier Logistics Miami Hub', 1, NOW()),
(2, 'LX-2024-001', 1, 'In Transit', 'Miami Sorting Center', 'Package departed Miami', 1, NOW()),
(3, 'LX-2024-001', 1, 'At Hub', 'Chicago Distribution Hub', 'Arrived at destination hub', 1, NOW()),
(4, 'LX-2024-002', 2, 'Booked', 'Los Angeles Warehouse', 'Freight booking confirmed', 1, NOW()),
(5, 'LX-2024-002', 2, 'Delivered', 'Seattle Warehouse', 'Delivered to consignee', 1, NOW()),
(6, 'LX-2024-003', 3, 'Booked', 'New York Distribution Center', 'Documents collected', 1, NOW()),
(7, 'LX-2024-003', 3, 'Out for Delivery', 'Dallas Route', 'Courier en route to recipient', 1, NOW());

INSERT IGNORE INTO `support_tickets` (`id`, `ticket_number`, `subject`, `description`, `status`, `priority`, `category`, `user_id`, `created_at`) VALUES
(1, 'TKT-2024-001', 'Tracking not updating for LX-2024-001', 'Tracking information has not updated in 24 hours.', 'open', 'high', 'tracking-issue', 1, NOW()),
(2, 'TKT-2024-002', 'Customs hold documentation needed', 'Need to submit additional documents for shipment held at customs.', 'in_progress', 'urgent', 'customs-hold', 2, NOW());

INSERT INTO `customs_notification_templates` (`id`, `template_key`, `template_name`, `event_type`, `subject_template`, `body_template`, `sms_template`, `priority`, `send_to_customer`, `send_to_officer`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'checkpoint.arrival', 'Checkpoint Arrival Notification', 'checkpoint_arrival', 'Shipment {{tracking_number}} arrived at {{checkpoint}}', '<p>Your shipment <strong>{{tracking_number}}</strong> has arrived at {{checkpoint}}.</p><p><strong>Current Status:</strong> {{status}}<br><strong>Estimated Processing:</strong> {{estimated_hours}} hours</p><p>You will be notified when clearance is complete.</p>', 'Shipment {{tracking_number}} arrived.', 'medium', 1, 0, 1, '2026-05-07 06:19:08', '2026-05-07 06:19:08'),
(2, 'customs.hold', 'Customs Hold Notice', 'hold', 'Shipment {{tracking_number}} placed on hold', '<p>Your shipment <strong>{{tracking_number}}</strong> has been placed on hold at customs.</p><p><strong>Reason:</strong> {{reason}}<br><strong>Expected Release:</strong> {{expected_release_date}}</p>', 'Hold placed on {{tracking_number}}', 'high', 1, 0, 1, '2026-05-07 06:19:08', '2026-05-07 06:19:08'),
(3, 'customs.seizure', 'Seizure Notification', 'seizure', 'URGENT: Shipment {{tracking_number}} Seized', '<p>Shipment <strong>{{tracking_number}}</strong> has been seized by customs authorities.</p><p><strong>Reference:</strong> {{seizure_reference}}</p><p>Contact us immediately.</p>', 'URGENT: Seizure of {{tracking_number}}', '', 1, 0, 1, '2026-05-07 06:19:09', '2026-05-07 06:19:09'),
(4, 'customs.clearance', 'Customs Clearance Approved', 'clearance', 'Shipment {{tracking_number}} Cleared', '<p>Good news! Your shipment <strong>{{tracking_number}}</strong> has been cleared by customs.</p><p>Your package is now in transit.</p>', '{{tracking_number}} cleared by customs.', 'medium', 1, 0, 1, '2026-05-07 06:19:09', '2026-05-07 06:19:09'),
(5, 'inspection.required', 'Inspection Required', 'inspection_required', 'Shipment {{tracking_number}} Selected for Inspection', '<p>Your shipment <strong>{{tracking_number}}</strong> has been selected for customs inspection.</p><p>Estimated duration: 1-3 business days.</p>', 'Inspection required for {{tracking_number}}', 'medium', 1, 0, 1, '2026-05-07 06:19:09', '2026-05-07 06:19:09');

INSERT INTO `customs_tariffs` (`id`, `hs_code`, `description`, `unit`, `standard_rate`, `preferential_rate`, `country_code`, `effective_from`, `effective_to`, `is_active`, `notes`, `created_at`, `updated_at`) VALUES
(1, '8517.12.00', 'Telephones for cellular networks', NULL, 0.0000, 0.0000, 'US', '2024-01-01', NULL, 1, NULL, '2026-05-07 06:47:20', '2026-05-07 06:47:20'),
(2, '8517.62.00', 'Smartphones', NULL, 0.0000, 0.0000, 'US', '2024-01-01', NULL, 1, NULL, '2026-05-07 06:47:20', '2026-05-07 06:47:20'),
(3, '8703.23.10', 'Motor vehicles for passengers', NULL, 0.0250, 0.0000, 'US', '2024-01-01', NULL, 1, NULL, '2026-05-07 06:47:20', '2026-05-07 06:47:20'),
(4, '6109.10.00', 'T-shirts, of cotton', NULL, 0.1600, 0.0000, 'US', '2024-01-01', NULL, 1, NULL, '2026-05-07 06:47:20', '2026-05-07 06:47:20'),
(5, '6211.42.10', 'Women\'s trousers, cotton', NULL, 0.1600, 0.0000, 'US', '2024-01-01', NULL, 1, NULL, '2026-05-07 06:47:20', '2026-05-07 06:47:20');

INSERT INTO `services` (`id`, `code`, `name`, `description`, `base_price`, `price_per_kg`, `estimated_days_min`, `estimated_days_max`, `priority`, `active`, `created_at`, `updated_at`) VALUES
(1, 'STANDARD', 'Standard Shipping', 'Economy delivery service', 10.00, 2.50, 3, 7, 'standard', 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(2, 'EXPRESS', 'Express Delivery', 'Fast delivery service', 25.00, 5.00, 1, 3, 'high', 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(3, 'OVERNIGHT', 'Overnight Shipping', 'Next business day delivery', 40.00, 8.00, 1, 1, 'express', 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(4, 'ECONOMY', 'Economy Parcel', 'Budget-friendly shipping', 5.00, 1.50, 7, 14, 'low', 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(5, 'SAME-DAY', 'Same Day Delivery', 'Same day delivery within city', 50.00, 10.00, 0, 0, 'express', 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06');

INSERT INTO `support_categories` (`id`, `name`, `slug`, `description`, `parent_id`, `sort_order`, `sla_hours`, `assign_to_team`, `auto_close_days`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'General Inquiry', 'general', 'General questions about our services', NULL, 0, 24, NULL, 7, 1, '2026-05-07 06:47:19', '2026-05-07 06:47:19'),
(2, 'Shipment Issue', 'shipment-issue', 'Problems with your shipment', NULL, 0, 4, NULL, 14, 1, '2026-05-07 06:47:19', '2026-05-07 06:47:19'),
(3, 'Billing & Payment', 'billing', 'Questions about invoices and payments', NULL, 0, 8, NULL, 7, 1, '2026-05-07 06:47:19', '2026-05-07 06:47:19'),
(4, 'Technical Support', 'technical', 'Website or app technical issues', NULL, 0, 8, NULL, 7, 1, '2026-05-07 06:47:19', '2026-05-07 06:47:19'),
(5, 'Customs & Compliance', 'customs', 'Customs clearance questions', NULL, 0, 12, NULL, 30, 1, '2026-05-07 06:47:19', '2026-05-07 06:47:19'),
(6, 'Feedback', 'feedback', 'Suggestions and feedback', NULL, 0, 48, NULL, 7, 1, '2026-05-07 06:47:19', '2026-05-07 06:47:19'),
(7, 'Complaint', 'complaint', 'Formal complaints', NULL, 0, 2, NULL, 30, 1, '2026-05-07 06:47:19', '2026-05-07 06:47:19'),
(8, 'Partnership', 'partnership', 'Business partnership inquiries', NULL, 0, 48, NULL, 30, 1, '2026-05-07 06:47:19', '2026-05-07 06:47:19');

INSERT INTO `system_config` (`id`, `config_key`, `value`, `data_type`, `description`, `category`, `is_public`, `is_system`, `is_encrypted`, `validation_rules`, `ui_config`, `version`, `created_at`, `updated_at`) VALUES
(1, 'receipt.auto_generate', '1', 'boolean', 'Automatically generate receipts on payment', 'receipt', 0, 0, 0, NULL, NULL, 1, '2026-05-07 06:47:21', '2026-05-07 06:47:21'),
(2, 'receipt.email_attachment', '1', 'boolean', 'Attach receipt PDF to confirmation email', 'receipt', 0, 0, 0, NULL, NULL, 1, '2026-05-07 06:47:21', '2026-05-07 06:47:21'),
(3, 'support.auto_assign', '1', 'boolean', 'Auto-assign tickets to teams', 'support', 0, 0, 0, NULL, NULL, 1, '2026-05-07 06:47:21', '2026-05-07 06:47:21'),
(4, 'support.sla_critical_hours', '1', 'integer', 'SLA response time for critical tickets (hours)', 'support', 0, 0, 0, NULL, NULL, 1, '2026-05-07 06:47:21', '2026-05-07 06:47:21'),
(5, 'customs.auto_clear', '0', 'boolean', 'Auto-clear low-risk customs shipments', 'customs', 0, 0, 0, NULL, NULL, 1, '2026-05-07 06:47:21', '2026-05-07 06:47:21'),
(6, 'warehouse.low_stock_threshold', '10', 'integer', 'Low stock alert threshold', 'warehouse', 0, 0, 0, NULL, NULL, 1, '2026-05-07 06:47:21', '2026-05-07 06:47:21'),
(7, 'delivery.max_attempts', '3', 'integer', 'Maximum delivery attempts per shipment', 'delivery', 0, 0, 0, NULL, NULL, 1, '2026-05-07 06:47:21', '2026-05-07 06:47:21'),
(8, 'flagging.auto_flag_high_value', '1', 'boolean', 'Auto-flag high-value shipments', 'risk', 0, 0, 0, NULL, NULL, 1, '2026-05-07 06:47:21', '2026-05-07 06:47:21'),
(9, 'high_value_threshold', '10000', 'float', 'Threshold for high-value shipment flag', 'risk', 0, 0, 0, NULL, NULL, 1, '2026-05-07 06:47:21', '2026-05-07 06:47:21'),
(10, 'retention.shipment_archive_days', '730', 'integer', 'Days to keep shipments before archiving', 'data_retention', 0, 0, 0, NULL, NULL, 1, '2026-05-07 06:47:21', '2026-05-07 06:47:21');

INSERT INTO `templates` (`id`, `type`, `name`, `subject`, `template_key`, `content`, `variables`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'email', 'Shipment Created', 'Your Shipment Has Been Created', 'shipment_created', 'Dear {customer_name},\n\nYour shipment #{tracking_number} has been successfully created.\n\nTracking Number: {tracking_number}\nService: {service_name}\nEstimated Delivery: {estimated_delivery}\n\nYou can track your shipment at: {tracking_url}\n\nThank you for choosing our service!\n\nBest regards,\nShipping Team', NULL, 1, '2026-05-07 06:40:07', '2026-05-07 06:40:07'),
(2, 'email', 'Out for Delivery', 'Your Package is Out for Delivery', 'out_for_delivery', 'Dear {customer_name},\n\nGood news! Your package is out for delivery today.\n\nTracking Number: {tracking_number}\nEstimated Delivery Window: {delivery_window}\n\nTrack live: {tracking_url}\n\nThank you!', NULL, 1, '2026-05-07 06:40:07', '2026-05-07 06:40:07'),
(3, 'email', 'Delivered', 'Package Delivered', 'delivered', 'Dear {customer_name},\n\nYour package has been delivered.\n\nTracking Number: {tracking_number}\nDelivered At: {delivered_at}\n\nThank you for choosing our service!', NULL, 1, '2026-05-07 06:40:07', '2026-05-07 06:40:07'),
(4, 'sms', 'Shipment Created SMS', NULL, 'shipment_created_sms', 'Shipment #{tracking_number} created! Track: {tracking_url} Est: {estimated_delivery}', NULL, 1, '2026-05-07 06:40:07', '2026-05-07 06:40:07'),
(5, 'sms', 'Out for Delivery SMS', NULL, 'out_for_delivery_sms', 'Out for delivery today! Track #{tracking_number}: {tracking_url}', NULL, 1, '2026-05-07 06:40:07', '2026-05-07 06:40:07'),
(6, 'sms', 'Delivered SMS', NULL, 'delivered_sms', 'Package delivered! Thank you for shipping with us.', NULL, 1, '2026-05-07 06:40:07', '2026-05-07 06:40:07');

INSERT INTO `zones` (`id`, `code`, `name`, `country_code`, `region_type`, `base_rate_multiplier`, `tax_rate`, `customs_fee`, `handling_fee`, `active`, `created_at`, `updated_at`) VALUES
(1, 'US-DOM', 'United States Domestic', 'US', 'domestic', 1.00, 0.0800, 0.00, 5.00, 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(2, 'US-PRI', 'US Priority', 'US', 'domestic', 1.25, 0.0800, 0.00, 8.00, 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(3, 'EU-DOM', 'European Union Domestic', 'EU', 'domestic', 1.10, 0.2000, 0.00, 7.00, 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(4, 'EU-INT', 'European Union International', 'EU', 'international', 1.50, 0.2000, 15.00, 12.00, 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(5, 'ASIA-DOM', 'Asia Pacific Domestic', 'AP', 'domestic', 0.90, 0.1000, 0.00, 6.00, 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(6, 'ASIA-INT', 'Asia International', 'AP', 'international', 1.40, 0.1000, 12.00, 10.00, 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(7, 'AF-DOM', 'Africa Domestic', 'AF', 'domestic', 0.85, 0.1500, 0.00, 8.00, 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(8, 'AF-INT', 'Africa International', 'AF', 'international', 1.60, 0.1500, 20.00, 15.00, 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(9, 'LAT-DOM', 'Latin America Domestic', 'LA', 'domestic', 0.95, 0.1200, 0.00, 7.00, 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06'),
(10, 'LAT-INT', 'Latin America International', 'LA', 'international', 1.55, 0.1200, 18.00, 13.00, 1, '2026-05-07 06:40:06', '2026-05-07 06:40:06');

INSERT INTO `mode_bookings` (`id`, `name`, `services`, `deliverytime`, `observations`, `estado`) VALUES
(6, 'SHIP', 'Container shipp', 'FROM MONDAY ', 'Handling, palletizing, storage, packaging,  TO Address Services', 1),
(7, 'HIGH ROAD', 'VANS AND tracto', 'FROM MONDAY ', 'NONE', 1),
(8, 'COURIER', 'ECONOMIC AND SP', 'FROM MONDAY ', 'NONES', 1),
(9, 'EXPRESS', 'Operating at hi', 'FROM MONDAY ', 'NONE', 1);

INSERT INTO `offices` (`id`, `off_name`, `address`, `city`, `ph_no`, `office_time`, `contact_person`, `estado`) VALUES
(17, 'ChaseXpressLogistics -NORWAY', 'Vangsordet 63, 1811 Askin, Norway', 'LAGOS', '+4743581509', '8:00AM - 12:00PM | 2:00PM - 4:00PM', 'MARK BULLY', 1),
(50, 'ChaseXpressLogistics -CHINA', 'Vangsordet 6378, 1811 Askin,China', 'china', '+4743581509', '8:00AM - 12:00PM | 2:00PM - 4:00PM', 'JOHAN Banky', 1),
(15, 'ChaseXpressLogistics-UNITED KINGDOM ', 'Vangsordet 63, 1811 Askin, United Kingdom', 'UNITED KINGDOM', '+4743581509', '8:00AM - 12:00PM | 2:00PM - 4:00PM', 'Mikel Cham', 1),
(14, 'ChaseXpressLogistics- NETHERLANDS', 'Vangsordet 63, 1811 Askin,Netherlands', 'NETHERLANDS', '+4743581509', '8:00AM - 12:00PM | 2:00PM - 4:00PM', 'PATRICK BANKY', 1),
(54, 'Express Logistics United States', 'United States', 'United States', '+12623462829', '8:00AM - 12:00PM | 2:00PM - 4:00PM', 'Longman', 1),
(55, 'Express Logistics Afghanistan', 'Afghanistan', 'Afghanistan', '+12623462829', '8:00AM - 12:00PM | 2:00PM - 4:00PM', 'Mohammed', 1),
(56, 'Express Logistics Syria', 'Syria', 'Syria', '+12623462829', '8:00AM - 12:00PM | 2:00PM - 4:00PM', 'Mohammed', 1),
(57, 'Express Logistics Indonesia', 'Indonesia', 'Jakarta', '+12623462829', '8:00AM - 12:00PM | 2:00PM - 4:00PM', 'Johnna', 1),
(58, 'Express Logistics Turkey', 'Turkey', 'Istanbul', '+90623462829', '8:00AM - 12:00PM | 2:00PM - 4:00PM', 'Johnna', 1);

INSERT INTO `scheduledpickup` (`cid`, `name_courier`, `courier`, `rate`, `services`, `Length`, `Width`, `Height`, `Weight`, `WeightType`, `date`) VALUES
(1, 'Fastrack', 'imagen/hermes-logo.png', '15.00', 'Normal', '23', '12', '15', 12, 'Kg', '2016-02-21'),
(2, 'World Courier', 'imagen/collectplus-logo.png', '15.01', 'Express', '34', '19', '25', 3, 'Kg', '2016-02-21'),
(3, 'Delivery Express', 'imagen/dx-logo.png', '14.12', 'Express', '29', '11', '14', 12, 'Kg', '2016-02-21'),
(4, 'Red Line', 'imagen/ajg-logo.jpg', '7', 'Express', '12', '22', '9', 1, 'Kg', '0000-00-00'),
(5, 'Full Services', 'imagen/dpd-pickup-logo.png', '10', 'Normal', '22', '23', '12', 2, 'Kg', '0000-00-00');

INSERT INTO `subir_imagen` (`id`, `nombre_imagen`, `imagen`, `tipo`) VALUES
(1, 'unitedpercelexpress (1).svg', 0x3c7376672076657273696f6e3d22312e322220786d6c6e733d22687474703a2f2f7777772e77332e6f72672f323030302f737667222076696577426f783d2230203020333030203830222077696474683d2233303022206865696768743d223830223e0a093c7469746c653e756e6974656470657263656c657870726573733c2f7469746c653e0a093c646566733e0a09093c696d616765202077696474683d22353622206865696768743d223732222069643d22696d67312220687265663d22646174613a696d6167652f706e673b6261736536342c6956424f5277304b47676f414141414e5355684555674141414467414141424943414d4141414355657362744141414141584e535230494232636b736677414141767051544652464c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c7378784c737878394d55527177414141503530556b35544141495248424d48415155745a596d464f41386b6c73665730364a4344414d4b4245376b3361534d7564757356416b795368743337356f314a566172374d6851496c2b62746278784b427148366f676444556a777441364c39664c756b796f576665577652526c7076754b59497767475774585a726f7166326e6f58626468765a4d7255646a425a6873577766454165503656536e4b314479594c65736163766f394b564f63525645484c6365425533707378374e6b2b7035376854456e6e526746633043324231495a54744743377271704a4a6b5575654b3862706347496d774c736730436d79357272446273357a4c4f4443343645786a75686f2f666448526b4842586e36326b4676686e522f58506b777a584952596c3733366a7a322f6176754e555a6c6d464774737149484c4f743945592f487a4a38314e737a7967394633343976363364494e6e663839684f2f6c7137346a6b4141414a48306c45515652346e4a5658657a79553652352f33786e6551635a747a5474546f3661476b55736b785352546a5171705952684d6a4b474d532b736770636d6c30615170636f324d744a4a5359794d527969586431715930544a5473696130325256474b342b7965366d7a6e6e4d2f6e6d41756c397031746e372f6539336c2f332b66332b7a362f36777341414143693047674e54516a347177756a7061307a533173582b35655265766f47686b594733786a6a5074754838515431436d6650495a724d6e5565617677413159353973616b61423165416744664f4646705a57316f74736242642f6c4950746c7467765865626769464744704335335775454d3056596172467074717477427958524c46394b6174657549726d3571694c757639396977455949596e6c354d6278385742506c69376679382f5a6e73545147424848595146786d494367377833307944594f71575546365949786b56486847356c636a374e75707630544778524b3974794d5a69644f4f493233666749494b6253306a38546a39502f56412b6535656e4a5155507532394c594d636c737043414547703255764a75415136433962795a52696b384a7364376a364d514d306b4f7075786c702b354449366f4552624d4e65414837795943763851462b616c70432b6b45614c4c3854454a57526d5a57646b34764d457377395a4a53586a3459416c764863714d4d4646453346726c6972634c362f3752453764523442696f352b78346c4151514465703368613770674c723852456977437177774559776647517264716652673742737054457a4479687152596d6c77732f756371673747506b4341744f7a6546356e30613830656b463052644c536a626f715a4167626c74534f6646373361394a4e5a423752734b6357364667684b464756504c50426a485568666a4868616e61346e5575724a6f6c502b4e777a666c6136364b767a5538594858432b7272344b4167687563346b584c6859726b6c48396e55367468726a4770676768424641334a7a64487553737778656278645a6653393752777965714f4947646b2b6965566159417735664b562b4b745575616c574675757558532f504f3735764931554e59776a31517877766f56554d77443847477435596e447370696e4672752b6c39797969724f615839746858794a554e43367a526d7577384d6b415557306f37575361734241716f7a3057797a66704f486b332f2b666a6f69456e544f4e354a74455545412b59344a4f3834594238494143477653475672574b355a4a44514f376f6e32526b4a694753474b654e5234416343656b7a5945467a6f4c4666743379364956596a6e64764f556d574838516a4b4958774c66644b62765749415a42795838625a485a445a4b356d6c444152666e3435725036582b50527950524a50386f4d36707a35674173505a484a704d34484a4b55474f413469575331394e762b7a47346b506851674751766748686d7354546a6d433544446a7a77756466676c72706e58526763773454554c6b774e57486b726a367a394238696845335849322b61534144474777476d536f77695756744a634c697737627367656f766c554f6553465047556771516537647574534161766e426d756a2b654d367a4c72723477574f5a5351733036646242432b6543456230435635524b5177747a51526a6e646e4749623246574444507151304f44355a72457a3250587a69394154472b4d7276324c31624f4637713374484d6d6741417343426338613278563145734a655053647254305253436447484d3138456c766b46736b7543334f5365737a62696136737578586b664a39514d4d513467656b596150317369765a44664c546a68544143363070686861475755453078722b66316378467942684a3547503973306159734c5471614e694342554650766c6f4b715759534a66334c6a7a47557549566d515637553658707a44454455717837334b486e4863757648455642345758766f72585666585a6c6b314f4130396d7369512f5437385874486d4a705642755574474f68744554442b67436233356c4441726a6c70535638674e5a495955363873706561325a3255714e6b49586d766d2b356e5942556378473936733065464d61754a6c386f773949787371556c5a346d676e4634666677396d36674b79794551546c75672f57534776486d6b67326456643935436b68586c6e624f477363765354372f436b665832466858336e6f57464a706667526a59323371456177534b4e54546f777070394c594c4e546b743354332f384a666b4e307a75676f786774757a6d36614a365138503061706a726c396b58627953644d506978705a327072327134417032346b79747568683150445a697379566a6a53343170652b516b4d45553751795439306159366556372f784d474d6d4a776c2f5a663932514e6136356d503655726747633676762f31726a5a4f54305569757646794d327339705635625671744c47577a6e6f592f66354737714542447a325366487732354b646f2b38614c5971567748527032754f6a48516d78556163567a59727349444f496f4d6d66774a6a35704f4e64394e6c76625a37704b68704a56644b76466c70505a5274616c4d4433556c6433694936326f71686971584d662f35597553363454322f55324e6443304f4765444e4b46432f73375158786a594d4d413761366b433272534c502f554c33424979595634454b6d4a69594b75684e6f3362732f52416c52776f576d4554715065397a4b7461495963506332716e7a66416f4c656c6c3778366c6b39326978694977414e6d345652466d376e4d6e64727562452f755532557a37743833756d664f5937387145382f59695252695152346570596a6f47554c4a674446375449512b6537315632546e4b686a54646a527652427141684f3355726c57416a437250317444355134304f37396d76584333374d3271536931656353477a7877567765655672344f6e78697236456f6c4a6f734a77677550446b6f76636930787631616a6746354b735058507168466f726d306165714637495a705566526f514b6f4a374f756f544470616c524b756c746f5237376e6b786a4946396878596e64495a6e4455794d41784c3139364c6b69724b466362524c70323553382f3669636f4a7330357835316971533461456468354c7758352f5a537076584439467957386a4e454d6564666e7a687272444931327077556c7a673134625463362b5552706147752f795544663741493438645876664a3256476e52484636575630395866536f6b4d706535487271726931412b5258744a50452f5246416d66674f5a4e49705867417561562f4735334d5161704c3133325349695a4d6761694c655a7a756c554f4b66426136344938636b416f677a6e764f716372423854345a6c5664712b4952524166784a58656d675243756f76745454324648536d717150796c7975634747547066464370326142556d726d68344f76436d49706b4f6778766a65556e76646a334c6b694b4847337a2b394e546a3649732f324556637870334a482b6b49615534317550447661555a7332464a4c6c487a456c42644c486835776947326277594430506c463371307044484e61487a6a594e727a5165506c362b756c787661356b304d78557a624f61792f546a4c2b6d5a7459676d656b52546b4b4a49796a684a634e504e7865562f663471585a4875516c5652566454304d486d48667069617346646c5454486d71455532784342525376574d33335155372f43712f6d4936673570425472453550544f4c7939614b2b71317a475359706a6f5130326e35365033714e4f4b566544506c6875694e713848513559492f2b4f46695752566d79774c334b464d615531572f4b4e546a7537784d4865316f7862483032322f3575775a61507639315579374e6e7247312f7a7467545a6b4d5734706e58336c5733654264585452756b6a596f486c352b5156726249304c7162364b4d7362344a6f6e3762654e4868304a6578416a794e356b767746525a336a745a6e633277666c69464f484a4f75514f64554e74756332783635367a716e72536a58485731336248395032426a6e4f6d6e70477a753875746b54314441626e4a664d6b5633356a546472344a656e555334574a6a634d625567312b57562f50746d4c527a50657866556d73396c38306f643457344f744b56354a5a796935534c5059444b33343470614d2f6b483941356d6e32764d6452737a436e6248675630376f6b4b4c4455496f63712b305975583975343564346d49436b36763868417756684d306551354141414141424a52553545726b4a6767673d3d222f3e0a093c2f646566733e0a093c7374796c653e0a0909747370616e207b2077686974652d73706163653a707265207d200a09092e7430207b20666f6e742d73697a653a20323470783b66696c6c3a20233161316131613b666f6e742d7765696768743a203430303b666f6e742d66616d696c793a20224d696c6c696b526567756c6172222c20224d696c6c696b22207d200a09092e7431207b20666f6e742d73697a653a20323470783b66696c6c3a20233265636337313b666f6e742d7765696768743a203430303b666f6e742d66616d696c793a20224d696c6c696b526567756c6172222c20224d696c6c696b22207d200a093c2f7374796c653e0a093c7573652069643d2269636f6e73382d6c6f6769737469632d3130302220687265663d2223696d67312220783d223234312220793d2238222f3e0a093c746578742069643d22556e697465642050617263656c22207374796c653d227472616e73666f726d3a206d617472697828312e3433372c302c302c312e3137342c392e3032362c32372e31332922203e0a09093c747370616e20783d22302220793d22302220636c6173733d227430223e553c2f747370616e3e3c747370616e2020793d22302220636c6173733d227430223e6e697465643c2f747370616e3e3c747370616e2020793d22302220636c6173733d227430223e203c2f747370616e3e3c747370616e2020793d22302220636c6173733d227430223e503c2f747370616e3e3c747370616e2020793d22302220636c6173733d227430223e613c2f747370616e3e3c747370616e2020793d22302220636c6173733d227430223e7263656c3c2f747370616e3e3c747370616e2020793d22302220636c6173733d227430223e0a3c2f747370616e3e0a093c2f746578743e0a093c746578742069643d224578707265737322207374796c653d227472616e73666f726d3a206d617472697828322e3335332c302c302c312e3630392c392e3430342c35372e3935372922203e0a09093c747370616e20783d22302220793d22302220636c6173733d227431223e457870726573733c2f747370616e3e3c747370616e2020793d22302220636c6173733d227431223e0a3c2f747370616e3e0a093c2f746578743e0a3c2f7376673e, 'image/svg+xml');

INSERT INTO `tbl_clients` (`id`, `name`, `address`, `email`, `phone`, `company`, `country`, `state`, `postal_code`, `created_at`) VALUES
(1, 'Michelle Perez ', '2890 Redwood pkwy unit 51 Vallejo Ca. 94591', 'Mitchqperez@gmail.com', '4156199862', '', 'USA', 'California', '', '2023-10-26');

INSERT INTO `type_shipments` (`id`, `name`, `packaging`, `dimensions`, `estado`) VALUES
(8, 'Money', 'Envelope', '11,4 x 16,23', 1),
(9, 'Herbals', 'Plastic', '35x50, 40x50', 1),
(10, 'Medical', 'Box', ' 50 x 50x 23', 1),
(12, 'Technology', 'Box', ' 50 x 50x 23', 1),
(13, 'White line', 'Pallets', '1200 x 1000 ', 1);

INSERT INTO `courier` (`cid`, `cons_no`, `ship_name`, `phone`, `s_add`, `cc`, `rev_name`, `r_phone`, `r_add`, `cc_r`, `email`, `type`, `weight`, `variable`, `shipping_subtotal`, `invice_no`, `qty`, `book_mode`, `declarate`, `freight`, `mode`, `pick_date`, `schedule`, `pick_time`, `status`, `comments`, `book_date`, `status_delivered`, `officename`, `user`, `pimage`, `senderimg`, `reciverimage`, `percent`) VALUES
(140, 'LX-2024-001', 'Americans Shipping & Courier Logistics Miami Hub', '+13055551234', '1202 Logistics Way, Miami, FL 33142', 'USA', 'John Smith', '+13055555678', '4500 Oak Street, Chicago, IL 60601', 'USA', 'client@example.com', 'Express Parcel', 2.5, 'standard', '45.00', 'INV-2024-001', 1, 'Online', 'None', 'Air', 'Air', '2024-01-15', '2024-01-15', '09:30:00', 'in_transit', 'Package picked up from Miami Hub', '2024-01-15', '0', 'Americans Shipping & Courier Logistics - USA West Coast Hub', 'admin', NULL, NULL, NULL, '80'),
(139, 'LX-2024-002', 'Americans Shipping & Courier Logistics LA Warehouse', '+13235559876', '8900 Industrial Blvd, Los Angeles, CA 90001', 'USA', 'Maria Garcia', '+13235555432', '2200 Pine Avenue, Seattle, WA 98101', 'USA', 'maria@example.com', 'Freight', 150.0, 'heavy', '320.00', 'INV-2024-002', 2, 'Online', 'Commercial', 'Ocean', 'Sea', '2024-01-10', '2024-01-12', '08:00:00', 'delivered', 'Shipment delivered to Seattle warehouse', '2024-01-10', '1', 'Americans Shipping & Courier Logistics - USA West Coast Hub', 'admin', NULL, NULL, NULL, '100'),
(138, 'LX-2024-003', 'Americans Shipping & Courier Logistics New York Distribution Center', '+12125551234', '100 Commerce Drive, New York, NY 10001', 'USA', 'Robert Johnson', '+12125555678', '7500 Maple Road, Dallas, TX 75201', 'USA', 'robert@example.com', 'Documents', 0.5, 'light', '28.00', 'INV-2024-003', 1, 'Online', 'None', 'Road', 'Road', '2024-01-18', '2024-01-18', '14:15:00', 'out_for_delivery', 'Out for final delivery in Dallas', '2024-01-18', '0', 'Americans Shipping & Courier Logistics - USA West Coast Hub', 'admin', NULL, NULL, NULL, '90');

INSERT INTO `courier_track` (`id`, `cid`, `cons_no`, `pick_time`, `status`, `comments`, `bk_time`, `user`, `day`, `details`) VALUES
(1, 140, 'LX-2024-001', '2024-01-15 09:30:00', 'Booked', 'Package booked at Miami Hub', '2024-01-15 09:30:00', 'admin', '2024-01-15', 'Shipper submitted shipment details and payment'),
(2, 140, 'LX-2024-001', '2024-01-15 14:00:00', 'picked_up', 'Package picked up by courier', '2024-01-15 14:00:00', 'admin', '2024-01-15', 'Courier collected package from shipper address'),
(3, 140, 'LX-2024-001', '2024-01-16 08:00:00', 'in_transit', 'Package in transit to destination', '2024-01-16 08:00:00', 'admin', '2024-01-16', 'Package departed Miami sorting facility'),
(4, 140, 'LX-2024-001', '2024-01-17 06:30:00', 'in_transit', 'Package arrived at destination hub', '2024-01-17 06:30:00', 'admin', '2024-01-17', 'Package arrived at Chicago distribution center'),
(5, 139, 'LX-2024-002', '2024-01-10 08:00:00', 'Booked', 'Freight booked at LA warehouse', '2024-01-10 08:00:00', 'admin', '2024-01-10', 'Commercial freight shipment prepared for ocean export'),
(6, 139, 'LX-2024-002', '2024-01-12 16:00:00', 'delivered', 'Shipment delivered to recipient', '2024-01-12 16:00:00', 'admin', '2024-01-12', 'Freight cleared customs and delivered to consignee'),
(7, 138, 'LX-2024-003', '2024-01-18 14:15:00', 'Booked', 'Document shipment booked', '2024-01-18 14:15:00', 'admin', '2024-01-18', 'Important documents collected for express delivery'),
(8, 138, 'LX-2024-003', '2024-01-18 16:00:00', 'out_for_delivery', 'Out for delivery', '2024-01-18 16:00:00', 'admin', '2024-01-18', 'Courier en route to recipient address in Dallas');

INSERT INTO `courier_online` (`cid`, `deliveryboy`, `receivedby`, `drs`, `cons_no`, `ship_name`, `s_phone`, `s_add`, `fromcity`, `rev_name`, `r_phone`, `r_add`, `tocity`, `type`, `note`, `weight`, `book_mode`, `freight`, `Qnty`, `variable`, `shipping_subtotal`, `mode`, `date`, `deliverydate`, `time`, `status`, `payment`, `paymode`, `comments`, `office`, `user`) VALUES
(1, 'Mike Johnson', 'Pending', '', 'LX-2024-001', 'Americans Shipping & Courier Logistics Miami Hub', '+13055551234', '1202 Logistics Way, Miami, FL 33142', 'Miami', 'John Smith', '+13055555678', '4500 Oak Street, Chicago, IL 60601', 'Chicago', 'Express Parcel', 'Handle with care', '2.5', 'Online', 45.00, '1', 'standard', '45.00', 'Air', '2024-01-15', '2024-01-18', '09:30:00', 'in_transit', 'Paid', 'Credit Card', 'In transit - expected delivery Jan 18', 'Americans Shipping & Courier Logistics - USA West Coast Hub', 'admin'),
(2, 'Sarah Lee', 'John Smith', 'DRS-001', 'LX-2024-002', 'Americans Shipping & Courier Logistics LA Warehouse', '+13235559876', '8900 Industrial Blvd, Los Angeles, CA 90001', 'Los Angeles', 'Maria Garcia', '+13235555432', '2200 Pine Avenue, Seattle, WA 98101', 'Seattle', 'Freight', 'Fragile equipment', '150.0', 'Online', 320.00, '2', 'heavy', '320.00', 'Sea', '2024-01-10', '2024-01-12', '08:00:00', 'delivered', 'Paid', 'Bank Transfer', 'Delivered successfully', 'Americans Shipping & Courier Logistics - USA West Coast Hub', 'admin'),
(3, 'David Brown', 'Pending', '', 'LX-2024-003', 'Americans Shipping & Courier Logistics New York Distribution Center', '+12125551234', '100 Commerce Drive, New York, NY 10001', 'New York', 'Robert Johnson', '+12125555678', '7500 Maple Road, Dallas, TX 75201', 'Dallas', 'Documents', 'Urgent documents', '0.5', 'Online', 28.00, '1', 'light', '28.00', 'Road', '2024-01-18', '2024-01-18', '14:15:00', 'out_for_delivery', 'Paid', 'Credit Card', 'Courier out for delivery', 'Americans Shipping & Courier Logistics - USA West Coast Hub', 'admin');

-- ==========================================
-- ALTER TABLES & OTHER STATEMENTS
-- ==========================================

ALTER TABLE `courier_track` ADD CONSTRAINT `fk_courier_track_courier` FOREIGN KEY (`cid`) REFERENCES `courier` (`cid`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `shipments` ADD CONSTRAINT `fk_shipments_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `tracking_logs` ADD CONSTRAINT `fk_tracking_logs_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `delivery_attempts` ADD CONSTRAINT `fk_delivery_attempts_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `support_tickets` ADD CONSTRAINT `fk_support_tickets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `support_messages` ADD CONSTRAINT `fk_support_messages_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `packages` ADD CONSTRAINT `fk_packages_shipment` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `activity_logs`
  ADD KEY `idx_user` (`user_id`,`created_at`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created` (`created_at`);

ALTER TABLE `api_integrations`
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_provider` (`provider`,`integration_type`),
  ADD KEY `idx_active` (`is_active`,`last_sync_at`),
  ADD KEY `idx_errors` (`consecutive_failures`,`last_error`(50));

ALTER TABLE `api_integration_logs`
  ADD KEY `idx_integration_time` (`integration_id`,`started_at`),
  ADD KEY `idx_endpoint` (`endpoint_hit`(255),`response_code`),
  ADD KEY `idx_duration` (`duration_ms`),
  ADD KEY `idx_errors` (`error_message`(100),`started_at`);

ALTER TABLE `api_keys`
  ADD UNIQUE KEY `key_hash` (`key_hash`),
  ADD KEY `idx_key_hash` (`key_hash`),
  ADD KEY `idx_user` (`user_id`);

ALTER TABLE `api_usage_logs`
  ADD KEY `api_key_id` (`api_key_id`),
  ADD KEY `idx_endpoint` (`endpoint`,`created_at`),
  ADD KEY `idx_user` (`user_id`,`created_at`),
  ADD KEY `idx_ip` (`ip_address`,`created_at`),
  ADD KEY `idx_created` (`created_at`);

ALTER TABLE `attachments`
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_uuid` (`uuid`),
  ADD KEY `idx_uploaded` (`uploaded_by`);

ALTER TABLE `audit_logs_v2`
  ADD KEY `idx_user_action` (`user_id`,`action`,`created_at`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`,`created_at`),
  ADD KEY `idx_session` (`session_id`,`created_at`),
  ADD KEY `idx_ip` (`ip_address`,`created_at`),
  ADD KEY `idx_compliance` (`compliance_category`,`created_at`),
  ADD KEY `idx_module` (`module`,`created_at`),
  ADD KEY `idx_created` (`created_at`);

ALTER TABLE `bulk_operations`
  ADD UNIQUE KEY `operation_uuid` (`operation_uuid`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`),
  ADD KEY `idx_status` (`status`,`started_at`),
  ADD KEY `idx_operation` (`operation_type`,`created_at`);

ALTER TABLE `bulk_uploads`
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `idx_user` (`user_id`,`created_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_uuid` (`uuid`);

ALTER TABLE `communication_logs`
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `idx_recipient` (`recipient_address`,`created_at`),
  ADD KEY `idx_status` (`status`,`created_at`),
  ADD KEY `idx_type` (`type`,`created_at`),
  ADD KEY `idx_template` (`template_key`);

ALTER TABLE `communication_logs_enhanced`
  ADD KEY `recipient_id` (`recipient_id`),
  ADD KEY `idx_batch` (`batch_id`),
  ADD KEY `idx_recipient` (`recipient_address`,`created_at`),
  ADD KEY `idx_status_type` (`type`,`status`,`created_at`),
  ADD KEY `idx_template` (`template_key`),
  ADD KEY `idx_provider` (`provider`,`provider_message_id`),
  ADD KEY `idx_scheduled` (`scheduled_for`,`status`),
  ADD KEY `idx_retry` (`retry_count`,`max_retries`,`status`),
  ADD KEY `idx_created` (`created_at`);

ALTER TABLE `contact_messages`
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_category` (`category`);

ALTER TABLE `customs_checkpoints`
  ADD KEY `idx_shipment` (`shipment_id`,`arrival_time`),
  ADD KEY `idx_code` (`checkpoint_code`),
  ADD KEY `idx_status` (`current_status`),
  ADD KEY `idx_officer` (`officer_id`),
  ADD KEY `idx_arrival` (`arrival_time`);

ALTER TABLE `customs_declarations`
  ADD UNIQUE KEY `shipment_id` (`shipment_id`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_country` (`country_of_origin`),
  ADD KEY `idx_cleared` (`cleared`);

ALTER TABLE `customs_documents`
  ADD KEY `idx_customs_record` (`customs_record_id`,`document_type`),
  ADD KEY `idx_verified` (`verified_by`,`verified_at`),
  ADD KEY `idx_submitted` (`is_submitted`,`submitted_at`),
  ADD KEY `idx_expiry` (`expiry_date`);

ALTER TABLE `customs_duty_calculations`
  ADD KEY `calculated_by` (`calculated_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_customs` (`customs_record_id`),
  ADD KEY `idx_hs_code` (`hs_code`);

ALTER TABLE `customs_inspections`
  ADD KEY `customs_record_id` (`customs_record_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `idx_shipment` (`shipment_id`,`inspection_date`),
  ADD KEY `idx_inspector` (`inspector_id`),
  ADD KEY `idx_result` (`result`),
  ADD KEY `idx_risk` (`risk_level`);

ALTER TABLE `customs_notification_templates`
  ADD UNIQUE KEY `template_key` (`template_key`),
  ADD KEY `idx_event` (`event_type`,`is_active`),
  ADD KEY `idx_key` (`template_key`);

ALTER TABLE `customs_officer_logs`
  ADD KEY `checkpoint_id` (`checkpoint_id`),
  ADD KEY `seizure_id` (`seizure_id`),
  ADD KEY `inspection_id` (`inspection_id`),
  ADD KEY `idx_officer` (`officer_id`,`created_at`),
  ADD KEY `idx_action` (`action_type`,`created_at`),
  ADD KEY `idx_shipment` (`shipment_id`);

ALTER TABLE `customs_records`
  ADD UNIQUE KEY `customs_reference` (`customs_reference`),
  ADD KEY `cleared_by` (`cleared_by`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_reference` (`customs_reference`),
  ADD KEY `idx_status` (`status`);

ALTER TABLE `customs_seizures`
  ADD UNIQUE KEY `seizure_reference` (`seizure_reference`),
  ADD KEY `seizing_officer_id` (`seizing_officer_id`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_reference` (`seizure_reference`),
  ADD KEY `idx_custody` (`custody_status`,`seizure_date`),
  ADD KEY `idx_threat` (`threat_level`),
  ADD KEY `idx_type` (`restricted_item_type`);

ALTER TABLE `customs_tariffs`
  ADD KEY `idx_hs_code` (`hs_code`),
  ADD KEY `idx_country` (`country_code`,`is_active`),
  ADD KEY `idx_effective` (`effective_from`,`effective_to`);

ALTER TABLE `daily_stats`
  ADD UNIQUE KEY `stat_date` (`stat_date`),
  ADD KEY `idx_date` (`stat_date`),
  ADD KEY `idx_revenue` (`total_revenue`);

ALTER TABLE `delivery_attempts` ADD KEY `idx_shipment_attempt` (`shipment_id`,`attempt_number`), ADD KEY `idx_result` (`result`,`attempted_at`), ADD KEY `idx_rescheduled` (`rescheduled_to`), ADD KEY `idx_attempted_by` (`attempted_by`);

ALTER TABLE `delivery_zones`
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_country` (`country_code`),
  ADD KEY `idx_active` (`active`);

ALTER TABLE `document_queue`
  ADD UNIQUE KEY `queue_uuid` (`queue_uuid`),
  ADD KEY `template_id` (`template_id`),
  ADD KEY `idx_queue_status` (`status`,`priority`,`scheduled_for`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_document_type` (`document_type`,`created_at`),
  ADD KEY `idx_attempts` (`attempts`,`max_attempts`,`status`),
  ADD KEY `idx_generated` (`generated_by`);

ALTER TABLE `flagging_rules`
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_active_type` (`is_active`,`rule_type`),
  ADD KEY `idx_severity` (`severity`),
  ADD KEY `idx_triggered` (`last_triggered_at`,`triggers_count`);

ALTER TABLE `invoices`
  ADD KEY `idx_invoice_number` (`invoice_number`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_customer_status_date` (`customer_id`,`status`,`created_at`),
  ADD KEY `idx_due_status` (`due_date`,`status`),
  ADD KEY `idx_amount_range` (`total_amount`);

ALTER TABLE `locations`
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_city` (`city`),
  ADD KEY `idx_country` (`country`),
  ADD KEY `idx_type` (`type`);

ALTER TABLE `notifications`
  ADD KEY `idx_user` (`user_id`,`is_read`,`created_at`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_priority` (`priority`),
  ADD KEY `idx_unread_priority` (`user_id`,`is_read`,`priority`,`created_at`);

ALTER TABLE `packages` ADD UNIQUE KEY `uk_shipment_package` (`shipment_id`,`package_number`), ADD KEY `idx_status` (`status`), ADD KEY `idx_weight` (`weight`), ADD KEY `idx_shipment` (`shipment_id`), ADD KEY `idx_shipment_status` (`shipment_id`,`status`);

ALTER TABLE `package_dimensions_history`
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_package` (`package_id`,`created_at`);

ALTER TABLE `payments`
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_reference` (`reference_number`),
  ADD KEY `idx_invoice` (`invoice_id`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_customer` (`customer_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_customer_status` (`customer_id`,`status`,`created_at`);

ALTER TABLE `picking_orders`
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_warehouse_status` (`warehouse_id`,`status`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_assigned` (`assigned_to`,`status`),
  ADD KEY `idx_picker` (`picker_id`),
  ADD KEY `idx_priority_due` (`priority`,`created_at`);

ALTER TABLE `price_history`
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_service` (`service_id`,`effective_from`),
  ADD KEY `idx_zones` (`origin_zone_id`,`destination_zone_id`),
  ADD KEY `idx_effective` (`effective_from`,`effective_to`);

ALTER TABLE `zones`
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_country` (`country_code`);

ALTER TABLE `receipts`
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD UNIQUE KEY `uk_payment_receipt` (`payment_id`),
  ADD KEY `invoice_id` (`invoice_id`),
  ADD KEY `generated_by` (`generated_by`),
  ADD KEY `idx_receipt_number` (`receipt_number`),
  ADD KEY `idx_customer` (`customer_id`,`created_at`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_status` (`status`);

ALTER TABLE `templates`
  ADD UNIQUE KEY `template_key` (`template_key`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_active` (`is_active`);

ALTER TABLE `report_schedules`
  ADD KEY `idx_schedule` (`next_run_at`,`is_active`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_created_by` (`created_by`);

ALTER TABLE `restricted_item_detections`
  ADD KEY `confirmed_by` (`confirmed_by`),
  ADD KEY `idx_shipment` (`shipment_id`,`detection_timestamp`),
  ADD KEY `idx_category` (`restricted_category`),
  ADD KEY `idx_severity` (`severity`);

ALTER TABLE `shipment_routes`
  ADD UNIQUE KEY `route_number` (`route_number`),
  ADD KEY `destination_warehouse_id` (`destination_warehouse_id`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_route_number` (`route_number`),
  ADD KEY `idx_driver` (`driver_id`,`status`),
  ADD KEY `idx_vehicle` (`vehicle_id`),
  ADD KEY `idx_warehouses` (`origin_warehouse_id`,`destination_warehouse_id`),
  ADD KEY `idx_dates` (`planned_departure`,`planned_arrival`),
  ADD KEY `idx_status` (`status`);

ALTER TABLE `shipment_status_definitions`
  ADD UNIQUE KEY `status_code` (`status_code`),
  ADD KEY `idx_code` (`status_code`),
  ADD KEY `idx_category` (`category`,`is_active`),
  ADD KEY `idx_sort` (`sort_order`);

ALTER TABLE `shipment_status_history`
  ADD KEY `idx_shipment` (`shipment_id`,`created_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_shipment_event` (`shipment_id`,`event_type`,`created_at`),
  ADD KEY `idx_occurred_by` (`occurred_by`,`created_at`);

ALTER TABLE `shipment_status_history_v2` ADD KEY `idx_shipment_time` (`shipment_id`,`occurred_at`), ADD KEY `idx_status_code` (`status_code`,`occurred_at`), ADD KEY `idx_occurred_by` (`occurred_by`,`occurred_at`);

ALTER TABLE `staff_logs`
  ADD KEY `idx_staff` (`staff_id`,`created_at`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_action` (`action`);

ALTER TABLE `status_analytics_daily`
  ADD UNIQUE KEY `uk_date_status` (`stat_date`,`status_code`),
  ADD KEY `idx_date` (`stat_date`),
  ADD KEY `idx_status` (`status_code`),
  ADD KEY `idx_breaches` (`sla_breach_count`);

ALTER TABLE `status_assignment_rules`
  ADD KEY `assign_to_specific_user` (`assign_to_specific_user`),
  ADD KEY `idx_trigger_active` (`trigger_status`,`is_active`,`priority`),
  ADD KEY `idx_role` (`assign_to_role`);

ALTER TABLE `status_automation_log`
  ADD KEY `idx_rule` (`rule_id`,`executed_at`),
  ADD KEY `idx_shipment` (`shipment_id`,`executed_at`),
  ADD KEY `idx_result` (`action_result`,`executed_at`);

ALTER TABLE `status_automation_rules`
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_trigger` (`trigger_status`,`is_active`),
  ADD KEY `idx_priority` (`priority`,`is_active`),
  ADD KEY `idx_executed` (`last_executed_at`);

ALTER TABLE `status_change_audit`
  ADD KEY `idx_shipment_time` (`shipment_id`,`changed_at`),
  ADD KEY `idx_user` (`changed_by`,`changed_at`),
  ADD KEY `idx_status_transition` (`from_status`,`to_status`,`changed_at`),
  ADD KEY `idx_session` (`session_id`),
  ADD KEY `idx_compliance` (`changed_at`,`from_status`,`to_status`);

ALTER TABLE `status_change_requests`
  ADD UNIQUE KEY `request_uuid` (`request_uuid`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `denied_by` (`denied_by`),
  ADD KEY `idx_shipment_status` (`shipment_id`,`status`),
  ADD KEY `idx_requested` (`requested_by`,`requested_at`),
  ADD KEY `idx_approval` (`required_approval_role`,`status`,`expires_at`),
  ADD KEY `idx_expires` (`expires_at`,`status`);

ALTER TABLE `status_dashboard_configs`
  ADD UNIQUE KEY `uk_user_widget` (`user_id`,`widget_type`,`widget_name`),
  ADD KEY `idx_user` (`user_id`,`is_visible`);

ALTER TABLE `status_notification_logs`
  ADD KEY `status_history_id` (`status_history_id`),
  ADD KEY `idx_shipment_status` (`shipment_id`,`status_history_id`),
  ADD KEY `idx_recipient` (`recipient_address`,`created_at`),
  ADD KEY `idx_status_time` (`status`,`sent_at`),
  ADD KEY `idx_template` (`template_key`);

ALTER TABLE `status_notification_templates`
  ADD KEY `idx_status_type` (`status_code`,`notification_type`,`is_active`),
  ADD KEY `idx_template_key` (`template_key`);

ALTER TABLE `status_projections`
  ADD UNIQUE KEY `shipment_id` (`shipment_id`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_projection` (`projected_next_status`,`estimated_transition_at`),
  ADD KEY `idx_confidence` (`confidence_score`);

ALTER TABLE `status_slas`
  ADD KEY `idx_status_active` (`status_code`,`is_active`);

ALTER TABLE `status_transition_rules`
  ADD UNIQUE KEY `uk_transition` (`from_status`,`to_status`),
  ADD KEY `reverse_transition_id` (`reverse_transition_id`),
  ADD KEY `idx_from_to` (`from_status`,`to_status`),
  ADD KEY `idx_from` (`from_status`),
  ADD KEY `idx_to` (`to_status`);

ALTER TABLE `support_categories`
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_parent` (`parent_id`),
  ADD KEY `idx_active_sort` (`is_active`,`sort_order`);

ALTER TABLE `support_messages` ADD KEY `idx_ticket_created` (`ticket_id`,`created_at`), ADD KEY `idx_user` (`user_id`), ADD KEY `idx_internal` (`is_internal`);

ALTER TABLE `support_tickets`
  ADD KEY `idx_ticket_number` (`ticket_number`),
  ADD KEY `idx_user` (`user_id`,`created_at`),
  ADD KEY `idx_status_priority` (`status`,`priority`),
  ADD KEY `idx_assigned` (`assigned_to`,`status`),
  ADD KEY `idx_created` (`created_at`);

ALTER TABLE `system_alerts`
  ADD KEY `resolved_by` (`resolved_by`),
  ADD KEY `acknowledged_by` (`acknowledged_by`),
  ADD KEY `idx_type_severity` (`alert_type`,`severity`),
  ADD KEY `idx_source` (`source`,`created_at`),
  ADD KEY `idx_metric` (`metric_name`,`metric_value`),
  ADD KEY `idx_action_required` (`action_required`,`resolved_at`),
  ADD KEY `idx_triggered` (`triggered_by`,`created_at`),
  ADD KEY `idx_created` (`created_at`);

ALTER TABLE `system_config`
  ADD UNIQUE KEY `config_key` (`config_key`),
  ADD KEY `idx_key_category` (`config_key`,`category`),
  ADD KEY `idx_category` (`category`,`is_public`);

ALTER TABLE `system_notifications`
  ADD KEY `idx_target_roles` (`target_roles`(10)),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_expires` (`expires_at`);

ALTER TABLE `tracking_logs`
  ADD KEY `idx_tracking_number` (`tracking_number`),
  ADD KEY `idx_occurred_at` (`occurred_at`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_shipment_status_time` (`shipment_id`,`status`,`occurred_at`),
  ADD KEY `idx_public_tracking` (`tracking_number`,`is_public`,`occurred_at`);

ALTER TABLE `trade_verifications`
  ADD KEY `verified_by` (`verified_by`),
  ADD KEY `idx_shipment` (`shipment_id`),
  ADD KEY `idx_type_status` (`verification_type`,`status`);

ALTER TABLE `vehicles`
  ADD UNIQUE KEY `registration_number` (`registration_number`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `idx_registration` (`registration_number`),
  ADD KEY `idx_assigned` (`assigned_to`),
  ADD KEY `idx_status` (`status`);

ALTER TABLE `warehouse_zones`
  ADD UNIQUE KEY `uk_warehouse_zone` (`warehouse_id`,`zone_code`),
  ADD KEY `idx_warehouse_type` (`warehouse_id`,`zone_type`,`is_active`);

ALTER TABLE `webhook_events`
  ADD KEY `idx_event_type` (`event_type`,`created_at`),
  ADD KEY `idx_status` (`status`,`created_at`),
  ADD KEY `idx_target` (`target_url`(255));

ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `api_integrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `api_integration_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `api_usage_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `audit_logs_v2`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bulk_operations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `bulk_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `communication_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `communication_logs_enhanced`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `customs_checkpoints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `customs_declarations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `customs_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `customs_duty_calculations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `customs_inspections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `customs_notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `customs_officer_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `customs_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `customs_seizures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `customs_tariffs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `daily_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `delivery_attempts`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `delivery_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `document_queue`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `flagging_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `package_dimensions_history`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `picking_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `price_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `receipt_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `report_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `restricted_item_detections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

ALTER TABLE `setting_changes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `settlements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `settlement_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `shipment_discounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `shipment_exceptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `shipment_flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `shipment_holds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `shipment_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `shipment_status_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `shipment_status_history_v2`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `staff_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_analytics_daily`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_assignment_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_automation_log`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_automation_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_change_audit`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_change_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_dashboard_configs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_notification_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_notification_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_projections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_rate_impacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_slas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `status_transition_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `support_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

ALTER TABLE `support_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `system_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `system_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

ALTER TABLE `tracking_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `trade_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `warehouse_zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `webhook_events`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

ALTER TABLE `zones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `api_integrations`
  ADD CONSTRAINT `api_integrations_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `api_integration_logs`
  ADD CONSTRAINT `api_integration_logs_ibfk_1` FOREIGN KEY (`integration_id`) REFERENCES `api_integrations` (`id`) ON DELETE CASCADE;

ALTER TABLE `api_keys`
  ADD CONSTRAINT `api_keys_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `api_usage_logs`
  ADD CONSTRAINT `api_usage_logs_ibfk_1` FOREIGN KEY (`api_key_id`) REFERENCES `api_keys` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `api_usage_logs_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `attachments`
  ADD CONSTRAINT `attachments_ibfk_1` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `audit_logs_v2`
  ADD CONSTRAINT `audit_logs_v2_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `bulk_operations`
  ADD CONSTRAINT `bulk_operations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `bulk_uploads`
  ADD CONSTRAINT `bulk_uploads_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `communication_logs`
  ADD CONSTRAINT `communication_logs_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `communication_logs_enhanced`
  ADD CONSTRAINT `communication_logs_enhanced_ibfk_1` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `customs_checkpoints`
  ADD CONSTRAINT `customs_checkpoints_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customs_checkpoints_ibfk_2` FOREIGN KEY (`officer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `customs_declarations`
  ADD CONSTRAINT `customs_declarations_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE;

ALTER TABLE `customs_documents`
  ADD CONSTRAINT `customs_documents_ibfk_1` FOREIGN KEY (`customs_record_id`) REFERENCES `customs_records` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customs_documents_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `customs_duty_calculations`
  ADD CONSTRAINT `customs_duty_calculations_ibfk_1` FOREIGN KEY (`customs_record_id`) REFERENCES `customs_records` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customs_duty_calculations_ibfk_2` FOREIGN KEY (`calculated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customs_duty_calculations_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `customs_inspections`
  ADD CONSTRAINT `customs_inspections_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customs_inspections_ibfk_2` FOREIGN KEY (`customs_record_id`) REFERENCES `customs_records` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customs_inspections_ibfk_3` FOREIGN KEY (`inspector_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customs_inspections_ibfk_4` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `customs_officer_logs`
  ADD CONSTRAINT `customs_officer_logs_ibfk_1` FOREIGN KEY (`officer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customs_officer_logs_ibfk_2` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customs_officer_logs_ibfk_3` FOREIGN KEY (`checkpoint_id`) REFERENCES `customs_checkpoints` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customs_officer_logs_ibfk_4` FOREIGN KEY (`seizure_id`) REFERENCES `customs_seizures` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `customs_officer_logs_ibfk_5` FOREIGN KEY (`inspection_id`) REFERENCES `customs_inspections` (`id`) ON DELETE SET NULL;

ALTER TABLE `customs_records`
  ADD CONSTRAINT `customs_records_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customs_records_ibfk_2` FOREIGN KEY (`cleared_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `customs_seizures`
  ADD CONSTRAINT `customs_seizures_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `customs_seizures_ibfk_2` FOREIGN KEY (`seizing_officer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `delivery_attempts`
  ADD CONSTRAINT `delivery_attempts_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_attempts_ibfk_2` FOREIGN KEY (`attempted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `document_queue`
  ADD CONSTRAINT `document_queue_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `receipt_templates` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `document_queue_ibfk_2` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `flagging_rules`
  ADD CONSTRAINT `flagging_rules_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`);

ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `packages`
  ADD CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE;

ALTER TABLE `package_dimensions_history`
  ADD CONSTRAINT `package_dimensions_history_ibfk_1` FOREIGN KEY (`package_id`) REFERENCES `packages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `package_dimensions_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`),
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `payments_ibfk_4` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`);

ALTER TABLE `picking_orders`
  ADD CONSTRAINT `picking_orders_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `picking_orders_ibfk_2` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `picking_orders_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `picking_orders_ibfk_4` FOREIGN KEY (`picker_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `price_history`
  ADD CONSTRAINT `price_history_ibfk_1` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  ADD CONSTRAINT `price_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`),
  ADD CONSTRAINT `receipts_ibfk_2` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `receipts_ibfk_3` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`),
  ADD CONSTRAINT `receipts_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `receipts_ibfk_5` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `receipt_templates`
  ADD CONSTRAINT `receipt_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `report_schedules`
  ADD CONSTRAINT `report_schedules_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `restricted_item_detections`
  ADD CONSTRAINT `restricted_item_detections_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `restricted_item_detections_ibfk_2` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `setting_changes`
  ADD CONSTRAINT `setting_changes_ibfk_1` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

ALTER TABLE `settlements`
  ADD CONSTRAINT `settlements_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`);

ALTER TABLE `settlement_payments`
  ADD CONSTRAINT `settlement_payments_ibfk_1` FOREIGN KEY (`settlement_id`) REFERENCES `settlements` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `settlement_payments_ibfk_2` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `settlement_payments_ibfk_3` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE SET NULL;

ALTER TABLE `shipment_discounts`
  ADD CONSTRAINT `shipment_discounts_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipment_discounts_ibfk_2` FOREIGN KEY (`promotion_id`) REFERENCES `promotions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_discounts_ibfk_3` FOREIGN KEY (`applied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `shipment_exceptions`
  ADD CONSTRAINT `shipment_exceptions_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipment_exceptions_ibfk_2` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_exceptions_ibfk_3` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_exceptions_ibfk_4` FOREIGN KEY (`escalated_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `shipment_flags`
  ADD CONSTRAINT `shipment_flags_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipment_flags_ibfk_2` FOREIGN KEY (`flagged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_flags_ibfk_3` FOREIGN KEY (`acknowledged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_flags_ibfk_4` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `shipment_holds`
  ADD CONSTRAINT `shipment_holds_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipment_holds_ibfk_2` FOREIGN KEY (`held_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_holds_ibfk_3` FOREIGN KEY (`released_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `shipment_routes`
  ADD CONSTRAINT `shipment_routes_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipment_routes_ibfk_2` FOREIGN KEY (`origin_warehouse_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_routes_ibfk_3` FOREIGN KEY (`destination_warehouse_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_routes_ibfk_4` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `shipment_routes_ibfk_5` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

ALTER TABLE `shipment_status_history`
  ADD CONSTRAINT `shipment_status_history_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE;

ALTER TABLE `shipment_status_history_v2`
  ADD CONSTRAINT `shipment_status_history_v2_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `shipment_status_history_v2_ibfk_2` FOREIGN KEY (`occurred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `staff_logs`
  ADD CONSTRAINT `staff_logs_ibfk_1` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`);

ALTER TABLE `status_analytics_daily`
  ADD CONSTRAINT `status_analytics_daily_ibfk_1` FOREIGN KEY (`status_code`) REFERENCES `shipment_status_definitions` (`status_code`) ON DELETE CASCADE;

ALTER TABLE `status_assignment_rules`
  ADD CONSTRAINT `status_assignment_rules_ibfk_1` FOREIGN KEY (`trigger_status`) REFERENCES `shipment_status_definitions` (`status_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `status_assignment_rules_ibfk_2` FOREIGN KEY (`assign_to_specific_user`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `status_automation_log`
  ADD CONSTRAINT `status_automation_log_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE;

ALTER TABLE `status_automation_rules`
  ADD CONSTRAINT `status_automation_rules_ibfk_1` FOREIGN KEY (`trigger_status`) REFERENCES `shipment_status_definitions` (`status_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `status_automation_rules_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `status_change_audit`
  ADD CONSTRAINT `status_change_audit_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `status_change_audit_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `status_change_requests`
  ADD CONSTRAINT `status_change_requests_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `status_change_requests_ibfk_2` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `status_change_requests_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `status_change_requests_ibfk_4` FOREIGN KEY (`denied_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `status_dashboard_configs`
  ADD CONSTRAINT `status_dashboard_configs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

ALTER TABLE `status_notification_logs`
  ADD CONSTRAINT `status_notification_logs_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `status_notification_logs_ibfk_2` FOREIGN KEY (`status_history_id`) REFERENCES `shipment_status_history_v2` (`id`) ON DELETE CASCADE;

ALTER TABLE `status_notification_templates`
  ADD CONSTRAINT `status_notification_templates_ibfk_1` FOREIGN KEY (`status_code`) REFERENCES `shipment_status_definitions` (`status_code`) ON DELETE CASCADE;

ALTER TABLE `status_projections`
  ADD CONSTRAINT `status_projections_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE;

ALTER TABLE `status_rate_impacts`
  ADD CONSTRAINT `status_rate_impacts_ibfk_1` FOREIGN KEY (`status_code`) REFERENCES `shipment_status_definitions` (`status_code`) ON DELETE CASCADE;

ALTER TABLE `status_slas`
  ADD CONSTRAINT `status_slas_ibfk_1` FOREIGN KEY (`status_code`) REFERENCES `shipment_status_definitions` (`status_code`) ON DELETE CASCADE;

ALTER TABLE `status_transition_rules`
  ADD CONSTRAINT `status_transition_rules_ibfk_1` FOREIGN KEY (`reverse_transition_id`) REFERENCES `status_transition_rules` (`id`) ON DELETE SET NULL;

ALTER TABLE `support_categories`
  ADD CONSTRAINT `support_categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `support_categories` (`id`) ON DELETE SET NULL;

ALTER TABLE `support_messages`
  ADD CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_messages_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `system_alerts`
  ADD CONSTRAINT `system_alerts_ibfk_1` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `system_alerts_ibfk_2` FOREIGN KEY (`acknowledged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `trade_verifications`
  ADD CONSTRAINT `trade_verifications_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `trade_verifications_ibfk_2` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vehicles_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`) ON DELETE SET NULL;

ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

ALTER TABLE `warehouse_zones`
  ADD CONSTRAINT `warehouse_zones_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;



ALTER TABLE `calculator`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;

ALTER TABLE `company`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;

ALTER TABLE `courier`
  MODIFY `cid` int(6) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=141;

ALTER TABLE `courier_online`
  MODIFY `cid` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `courier_paid`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `courier_track`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;

ALTER TABLE `shipments`
ADD COLUMN IF NOT EXISTS `receipt_number` varchar(50) DEFAULT NULL AFTER `tracking_number`,
ADD COLUMN IF NOT EXISTS `barcode_path` varchar(255) DEFAULT NULL AFTER `receipt_number`,
ADD COLUMN IF NOT EXISTS `qr_code_path` varchar(255) DEFAULT NULL AFTER `barcode_path`,
ADD COLUMN IF NOT EXISTS `pdf_receipt_path` varchar(255) DEFAULT NULL AFTER `qr_code_path`,
ADD UNIQUE KEY IF NOT EXISTS `receipt_number` (`receipt_number`);



-- ==========================================
-- AUTO INCREMENT
-- ==========================================

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;




-- ============================================
-- Migration: 0001_tracking_history.sql
-- ============================================
-- Migration: 0001_tracking_history
-- Purpose: Create tracking_history table (was previously created at runtime)
-- Created: 2026-07-14
-- Note: This migration should run before migration 0003

CREATE TABLE IF NOT EXISTS `tracking_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `shipment_id` int(11) NOT NULL,
    `tracking_number` varchar(100) NOT NULL,
    `status` varchar(50) NOT NULL,
    `location` varchar(255) DEFAULT NULL,
    `description` text DEFAULT NULL,
    `customs_procedure` varchar(255) DEFAULT NULL,
    `event_notes` text DEFAULT NULL,
    `latitude` decimal(10,8) DEFAULT NULL,
    `longitude` decimal(11,8) DEFAULT NULL,
    `event_timestamp` datetime NOT NULL,
    `updated_by` varchar(100) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `tracking_number` (`tracking_number`),
    KEY `shipment_id` (`shipment_id`),
    KEY `event_timestamp` (`event_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Migration: 0002_courier_management.sql
-- ============================================
-- ============================================================
-- Migration 0002: Courier Management System
-- ------------------------------------------------------------
-- This migration is SAFE TO RE-RUN.
--   1. Expands the shipments.status enum (idempotent MODIFY).
--   2. Creates the drivers / vehicles tables (IF NOT EXISTS).
--
-- New columns on `shipments` (sender/receiver/parcel/transit fields)
-- are added via `database/dbs.sql` and applied non-destructively by
-- the existing admin/sync_schema.php tool (it only ever ADDs missing
-- columns, never drops). After applying this file, run:
--     visit admin/sync_schema.php   (or: php admin/sync_schema.php)
-- to create any new shipments columns that do not yet exist.
-- ============================================================

-- 1) Expand the shipments.status enum with the courier lifecycle.
--    (Old values are preserved so existing rows keep displaying.)
ALTER TABLE `shipments`
  MODIFY COLUMN `status` enum(
    'created','pending_pickup','picked_up','received_origin','sorted',
    'in_transit','at_hub','out_for_delivery','delivered','delivery_failed',
    'customer_unavailable','on_hold','returned','cancelled','lost','damaged',
    'pending','processing','at_warehouse','customs_inspection','customs_clearance',
    'customs_delayed','customs_seized','held','security_check','shipment_stopped'
  ) NOT NULL DEFAULT 'created';

-- 2) Drivers (couriers)
CREATE TABLE IF NOT EXISTS `drivers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `phone` varchar(90) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `status` enum('active','inactive','on_leave') DEFAULT 'active',
  `license_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Vehicles schema is already defined above. No duplicate needed.

-- 4) Branches / hubs are sourced from the existing `locations` table.
--    No new table required.


-- ============================================
-- Migration: 0003_unify_history_and_status.sql
-- ============================================
-- ============================================================
-- Migration 0003: Unify Status & Tracking History
-- ------------------------------------------------------------
-- SAFE TO RE-RUN.
--   1. Aligns the shipments.status ENUM with the canonical courier
--      lifecycle (idempotent MODIFY — preserves existing rows).
--   2. Back-fills the public tracking_history store from the admin
--      shipment_status_history_v2 table where a row is missing, so both
--      the admin detail page and the customer tracking page show the
--      same timeline.
--   3. Adds performance indexes used by the new filters (driver / branch /
--      vehicle / current_city).
--
-- How to apply (one time):
--   mysql -u shipuser -p shipping_db < database/migrations/0003_unify_history_and_status.sql
-- or import via phpMyAdmin.
-- ============================================================

-- 1) Align the status enum with the canonical set.
ALTER TABLE `shipments`
  MODIFY COLUMN `status` enum(
    'created','pending_pickup','picked_up','received_origin','sorted',
    'in_transit','at_hub','out_for_delivery','delivered','delivery_failed',
    'customer_unavailable','on_hold','returned','cancelled','lost','damaged',
    'pending','processing','at_warehouse','customs_inspection','customs_clearance',
    'customs_delayed','customs_seized','held','security_check','shipment_stopped'
  ) NOT NULL DEFAULT 'created';

-- 2) Back-fill tracking_history from shipment_status_history_v2 (missing rows only).
INSERT INTO `tracking_history`
    (shipment_id, tracking_number, status, location, description, event_timestamp, updated_by, created_at)
SELECT v.shipment_id, s.tracking_number, v.status_code, v.location, v.remarks, v.occurred_at, v.occurred_by, v.occurred_at
FROM `shipment_status_history_v2` v
JOIN `shipments` s ON s.id = v.shipment_id
LEFT JOIN `tracking_history` t
       ON t.shipment_id = v.shipment_id
      AND t.status = v.status_code
      AND t.event_timestamp = v.occurred_at
WHERE t.id IS NULL;

-- 3) Performance indexes for the Manage Shipments filters (added only if missing).
DROP PROCEDURE IF EXISTS `add_idx_if_missing`;
DELIMITER $$
CREATE PROCEDURE `add_idx_if_missing`(IN tbl VARCHAR(64), IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD INDEX `', idx, '` ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$
DELIMITER ;

CALL add_idx_if_missing('shipments', 'idx_shipments_driver',   '(`driver_id`)');
CALL add_idx_if_missing('shipments', 'idx_shipments_branch',  '(`branch_id`)');
CALL add_idx_if_missing('shipments', 'idx_shipments_vehicle', '(`vehicle_id`)');
CALL add_idx_if_missing('shipments', 'idx_shipments_curcity', '(`current_city`)');

DROP PROCEDURE IF EXISTS `add_idx_if_missing`;


-- ============================================
-- Migration: 0004_courier_tracking_enhancements.sql
-- ============================================
-- ============================================================
-- Migration 0004: Courier-style Tracking Enhancements
-- ============================================================
-- Adds:
--   - customs_procedure to shipment_status_history_v2
--   - transit_location + customs_procedure to shipments (current state)
-- Safe to re-run via IF NOT EXISTS / ADD COLUMN IF NOT EXISTS.

-- 1) Add customs_procedure to tracking history
SET @dbname = DATABASE();
SET @tbl = 'shipment_status_history_v2';
SET @col = 'customs_procedure';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) DEFAULT NULL AFTER `remarks`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col = 'event_notes';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` text DEFAULT NULL AFTER `customs_procedure`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Add current transit/procedure state to shipments
SET @tbl = 'shipments';
SET @cols := JSON_OBJECT(
  'transit_location', 'IFNULL((SELECT location FROM shipment_status_history_v2 WHERE shipment_id=shipments.id ORDER BY occurred_at DESC LIMIT 1), current_city)',
  'customs_procedure', 'IFNULL((SELECT customs_procedure FROM shipment_status_history_v2 WHERE shipment_id=shipments.id ORDER BY occurred_at DESC LIMIT 1), NULL)'
);

SET @col = 'transit_location';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) DEFAULT NULL AFTER `current_branch`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col = 'customs_procedure';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) DEFAULT NULL AFTER `transit_location`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Indexes for timeline filters
SET @idx = 'idx_history_shipment_time';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'shipment_status_history_v2' AND INDEX_NAME = @idx) > 0,
    'SELECT 1',
    'ALTER TABLE `shipment_status_history_v2` ADD INDEX `idx_history_shipment_time` (`shipment_id`, `occurred_at` DESC)'
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


-- ============================================
-- Migration: 0005_add_missing_primary_keys.sql
-- ============================================
-- ============================================================
-- Migration 0005: Add Missing Primary Keys and AUTO_INCREMENT
-- ------------------------------------------------------------
-- SAFE TO RE-RUN: Uses conditional checks before each ALTER.
--
-- This migration adds explicit PRIMARY KEY (`id`) AUTO_INCREMENT
-- to all tables where `id` exists but lacks a primary key, and
-- ensures AUTO_INCREMENT columns have explicit keys.
-- ============================================================

DELIMITER $$

-- Helper: add PK + AUTO_INCREMENT to a table if the column exists
-- and the table lacks a PRIMARY KEY.
DROP PROCEDURE IF EXISTS `add_pk_if_missing`;
CREATE PROCEDURE `add_pk_if_missing`(
    IN tbl VARCHAR(64),
    IN col VARCHAR(64),
    IN coltype VARCHAR(64)
)
BEGIN
    DECLARE has_pk INT DEFAULT 0;
    DECLARE has_col INT DEFAULT 0;

    SELECT COUNT(*) INTO has_pk
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tbl
      AND INDEX_NAME = 'PRIMARY';

    SELECT COUNT(*) INTO has_col
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tbl
      AND COLUMN_NAME = col;

    IF has_pk = 0 AND has_col = 1 THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ',
                          'MODIFY COLUMN `', col, '` ', coltype, ' NOT NULL AUTO_INCREMENT, ',
                          'ADD PRIMARY KEY (`', col, '`)');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

-- Helper: add PK + AUTO_INCREMENT using a non-`id` column.
DROP PROCEDURE IF EXISTS `add_pk_alt_if_missing`;
CREATE PROCEDURE `add_pk_alt_if_missing`(
    IN tbl VARCHAR(64),
    IN col VARCHAR(64),
    IN coltype VARCHAR(64)
)
BEGIN
    DECLARE has_pk INT DEFAULT 0;
    DECLARE has_col INT DEFAULT 0;

    SELECT COUNT(*) INTO has_pk
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tbl
      AND INDEX_NAME = 'PRIMARY';

    SELECT COUNT(*) INTO has_col
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = tbl
      AND COLUMN_NAME = col;

    IF has_pk = 0 AND has_col = 1 THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ',
                          'MODIFY COLUMN `', col, '` ', coltype, ' NOT NULL AUTO_INCREMENT, ',
                          'ADD PRIMARY KEY (`', col, '`)');
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- Tables with `id` column missing PK/AUTO_INCREMENT
CALL add_pk_if_missing('activity_logs',           'id', 'bigint(20)');
CALL add_pk_if_missing('api_integration_logs',    'id', 'bigint(20)');
CALL add_pk_if_missing('api_integrations',        'id', 'int(11)');
CALL add_pk_if_missing('api_keys',                'id', 'int(11)');
CALL add_pk_if_missing('api_usage_logs',          'id', 'bigint(20)');
CALL add_pk_if_missing('attachments',             'id', 'int(11)');
CALL add_pk_if_missing('audit_logs_v2',           'id', 'bigint(20)');
CALL add_pk_if_missing('bulk_operations',         'id', 'int(11)');
CALL add_pk_if_missing('bulk_uploads',            'id', 'int(11)');
CALL add_pk_if_missing('communication_logs',      'id', 'bigint(20)');
CALL add_pk_if_missing('communication_logs_enhanced', 'id', 'bigint(20)');
CALL add_pk_if_missing('contact_messages',        'id', 'int(11)');
CALL add_pk_if_missing('customs_checkpoints',     'id', 'int(11)');
CALL add_pk_if_missing('customs_declarations',    'id', 'int(11)');
CALL add_pk_if_missing('customs_documents',       'id', 'int(11)');
CALL add_pk_if_missing('customs_duty_calculations', 'id', 'int(11)');
CALL add_pk_if_missing('customs_inspections',     'id', 'int(11)');
CALL add_pk_if_missing('customs_notification_templates', 'id', 'int(11)');
CALL add_pk_if_missing('customs_officer_logs',    'id', 'int(11)');
CALL add_pk_if_missing('customs_records',         'id', 'int(11)');
CALL add_pk_if_missing('customs_seizures',        'id', 'int(11)');
CALL add_pk_if_missing('customs_tariffs',         'id', 'int(11)');
CALL add_pk_if_missing('daily_stats',             'id', 'int(11)');
CALL add_pk_if_missing('delivery_zones',          'id', 'int(11)');
CALL add_pk_if_missing('document_queue',          'id', 'bigint(20)');
CALL add_pk_if_missing('flagging_rules',          'id', 'int(11)');
CALL add_pk_if_missing('locations',               'id', 'int(11)');
CALL add_pk_if_missing('notifications',           'id', 'int(11)');
CALL add_pk_if_missing('package_dimensions_history', 'id', 'bigint(20)');
CALL add_pk_if_missing('picking_orders',          'id', 'int(11)');
CALL add_pk_if_missing('price_history',           'id', 'int(11)');
CALL add_pk_if_missing('promotions',              'id', 'int(11)');
CALL add_pk_if_missing('receipt_templates',       'id', 'int(11)');
CALL add_pk_if_missing('receipts',                'id', 'int(11)');
CALL add_pk_if_missing('report_schedules',        'id', 'int(11)');
CALL add_pk_if_missing('restricted_item_detections', 'id', 'int(11)');
CALL add_pk_if_missing('services',                'id', 'int(11)');
CALL add_pk_if_missing('sessions',                'id', 'varchar(128)');
CALL add_pk_if_missing('setting_changes',         'id', 'int(11)');
CALL add_pk_if_missing('settlement_payments',     'id', 'int(11)');
CALL add_pk_if_missing('settlements',             'id', 'int(11)');
CALL add_pk_if_missing('shipment_discounts',      'id', 'int(11)');
CALL add_pk_if_missing('shipment_exceptions',     'id', 'int(11)');
CALL add_pk_if_missing('shipment_flags',          'id', 'int(11)');
CALL add_pk_if_missing('shipment_holds',          'id', 'int(11)');
CALL add_pk_if_missing('shipment_routes',         'id', 'int(11)');
CALL add_pk_if_missing('shipment_status_history', 'id', 'int(11)');
CALL add_pk_if_missing('shipment_status_history_v2', 'id', 'int(11)');
CALL add_pk_if_missing('staff_logs',              'id', 'int(11)');
CALL add_pk_if_missing('status_analytics_daily',  'id', 'int(11)');
CALL add_pk_if_missing('status_assignment_rules', 'id', 'int(11)');
CALL add_pk_if_missing('status_automation_log',   'id', 'int(11)');
CALL add_pk_if_missing('status_automation_rules', 'id', 'int(11)');
CALL add_pk_if_missing('status_change_audit',     'id', 'int(11)');
CALL add_pk_if_missing('status_change_requests',  'id', 'int(11)');
CALL add_pk_if_missing('status_dashboard_configs','id', 'int(11)');
CALL add_pk_if_missing('status_notification_logs','id', 'int(11)');
CALL add_pk_if_missing('status_notification_templates', 'id', 'int(11)');
CALL add_pk_if_missing('status_projections',      'id', 'int(11)');
CALL add_pk_if_missing('status_rate_impacts',     'id', 'int(11)');
CALL add_pk_if_missing('status_slas',             'id', 'int(11)');
CALL add_pk_if_missing('status_transition_rules', 'id', 'int(11)');
CALL add_pk_if_missing('support_categories',      'id', 'int(11)');
CALL add_pk_if_missing('system_alerts',           'id', 'int(11)');
CALL add_pk_if_missing('system_config',           'id', 'int(11)');
CALL add_pk_if_missing('system_notifications',    'id', 'int(11)');
CALL add_pk_if_missing('templates',               'id', 'int(11)');
CALL add_pk_if_missing('trade_verifications',     'id', 'int(11)');
CALL add_pk_if_missing('type_shipments',         'id', 'int(11)');
CALL add_pk_if_missing('vehicles',               'id', 'int(11)');
CALL add_pk_if_missing('warehouse_zones',        'id', 'int(11)');
CALL add_pk_if_missing('warehouses',             'id', 'int(11)');
CALL add_pk_if_missing('webhook_events',         'id', 'int(11)');
CALL add_pk_if_missing('zones',                  'id', 'int(11)');

-- Tables with alternate PK column
CALL add_pk_alt_if_missing('courier_paid',        'id', 'int(10)');
CALL add_pk_alt_if_missing('courier_track',       'id', 'int(10)');
CALL add_pk_alt_if_missing('online_booking',      'id', 'int(100)');
CALL add_pk_alt_if_missing('scheduledpickup',     'cid', 'int(11)');
CALL add_pk_alt_if_missing('support_tickets',     'id', 'int(11)');
CALL add_pk_alt_if_missing('support_messages',    'id', 'int(11)');
CALL add_pk_alt_if_missing('shipment_status_history_v2', 'id', 'int(11)');

-- Cleanup
DROP PROCEDURE IF EXISTS `add_pk_if_missing`;
DROP PROCEDURE IF EXISTS `add_pk_alt_if_missing`;

DELIMITER ;

-- ============================================================
-- Performance indexes for high-volume lookups
-- ============================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS `add_idx_if_missing`;
CREATE PROCEDURE `add_idx_if_missing`(
    IN tbl VARCHAR(64),
    IN idx VARCHAR(64),
    IN def VARCHAR(255)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD INDEX `', idx, '` ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

CALL add_idx_if_missing('activity_logs',              'idx_activity_user',    '(`user_id`, `created_at`)');
CALL add_idx_if_missing('api_usage_logs',             'idx_api_usage_endpoint','(`endpoint`, `created_at`)');
CALL add_idx_if_missing('audit_logs_v2',              'idx_audit_entity_created','(`entity_type`, `entity_id`, `created_at`)');
CALL add_idx_if_missing('customs_records',            'idx_customs_shipment', '(`shipment_id`, `status`)');
CALL add_idx_if_missing('locations',                  'idx_locations_city_country', '(`city`, `country`)');
CALL add_idx_if_missing('notifications',              'idx_notifications_user', '(`user_id`, `is_read`, `created_at`)');
CALL add_idx_if_missing('shipment_status_history',    'idx_history_shipment_occurred','(`shipment_id`, `occurred_at`)');
CALL add_idx_if_missing('shipment_status_history_v2','idx_history_v2_shipment','(`shipment_id`, `occurred_at`)');
CALL add_idx_if_missing('tracking_history',          'idx_tracking_shipment_event','(`shipment_id`, `event_timestamp`)');

DROP PROCEDURE IF EXISTS `add_idx_if_missing`;


-- ============================================
-- Migration: 0006_integration_layer.sql
-- ============================================
-- ============================================================
-- Migration 0006: Unified Integration Layer
-- ------------------------------------------------------------
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. integration_status_map  (provider status => canonical status)
--   2. payment_intents         (gateway payment lifecycle bridge)
--   3. tracking_history.source + integration_id (carrier-sourced events)
--   4. shipments carrier linkage + external id + rate quote
--   5. api_integrations.inbound_secret_encrypted (inbound webhook HMAC)
--   6. performance indexes
--
-- Apply:
--   mysql -u shipuser -p shipping_db < database/migrations/0006_integration_layer.sql
-- or run database/migrations/apply_0006.php
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `add_col_if_missing_0006`$$
CREATE PROCEDURE `add_col_if_missing_0006`(IN tbl VARCHAR(64), IN col VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `add_idx_if_missing_0006`$$
CREATE PROCEDURE `add_idx_if_missing_0006`(IN tbl VARCHAR(64), IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD INDEX `', idx, '` ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) Provider status mapping
CREATE TABLE IF NOT EXISTS `integration_status_map` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `provider` varchar(100) NOT NULL,
    `provider_status` varchar(100) NOT NULL,
    `canonical_status` varchar(50) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_provider_status` (`provider`, `provider_status`),
    KEY `idx_provider` (`provider`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) Payment intents bridge
CREATE TABLE IF NOT EXISTS `payment_intents` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `gateway` varchar(100) NOT NULL,
    `gateway_payment_id` varchar(255) NOT NULL,
    `invoice_id` int(11) DEFAULT NULL,
    `shipment_id` int(11) DEFAULT NULL,
    `customer_id` int(11) DEFAULT NULL,
    `status` varchar(50) NOT NULL DEFAULT 'pending',
    `amount` decimal(12,2) DEFAULT NULL,
    `currency` varchar(10) DEFAULT 'USD',
    `client_secret` varchar(255) DEFAULT NULL,
    `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_gateway_payment` (`gateway`, `gateway_payment_id`),
    KEY `idx_invoice` (`invoice_id`),
    KEY `idx_shipment` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) tracking_history carrier source linkage
CALL add_col_if_missing_0006('tracking_history', 'source', "varchar(30) DEFAULT 'web'");
CALL add_col_if_missing_0006('tracking_history', 'integration_id', 'int(11) DEFAULT NULL');

-- 4) shipments carrier linkage
CALL add_col_if_missing_0006('shipments', 'carrier_integration_id', 'int(11) DEFAULT NULL');
CALL add_col_if_missing_0006('shipments', 'external_shipment_id', 'varchar(255) DEFAULT NULL');
CALL add_col_if_missing_0006('shipments', 'rate_quote_json', 'text DEFAULT NULL');

-- 5) inbound webhook secret on api_integrations
CALL add_col_if_missing_0006('api_integrations', 'inbound_secret_encrypted', 'text DEFAULT NULL');

-- 6) indexes
CALL add_idx_if_missing_0006('tracking_history', 'idx_th_source_intg', '(`source`, `integration_id`)');
CALL add_idx_if_missing_0006('shipments', 'idx_shp_carrier', '(`carrier_integration_id`)');

DROP PROCEDURE IF EXISTS `add_col_if_missing_0006`;
DROP PROCEDURE IF EXISTS `add_idx_if_missing_0006`;


-- ============================================
-- Migration: 0007_carrier_tracking_numbers.sql
-- ============================================
-- ============================================================
-- Migration 0007: Carrier Tracking Number Integration
-- ------------------------------------------------------------
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. carrier_tracking_number / carrier_name / carrier_integration_id
--      / last_carrier_sync_at to shipments
--   2. carrier_tracking_events table (raw carrier payload audit)
--   3. Unique key on (carrier_tracking_number, carrier_integration_id)
--
-- Apply:
--   mysql -u shipuser -p shipping_db < database/migrations/0007_carrier_tracking_numbers.sql
-- or run via apply_0007.php
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `add_col_if_missing_0007`$$
CREATE PROCEDURE `add_col_if_missing_0007`(IN tbl VARCHAR(64), IN col VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `add_idx_if_missing_0007`$$
CREATE PROCEDURE `add_idx_if_missing_0007`(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipments' AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `shipments` ADD INDEX `', idx, '` ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) Add carrier linkage columns to shipments
CALL add_col_if_missing_0007('shipments', 'carrier_tracking_number', 'varchar(120) DEFAULT NULL AFTER `tracking_number`');
CALL add_col_if_missing_0007('shipments', 'carrier_name', 'varchar(100) DEFAULT NULL AFTER `carrier_tracking_number`');
CALL add_col_if_missing_0007('shipments', 'carrier_integration_id', 'int(11) DEFAULT NULL AFTER `carrier_name`');
CALL add_col_if_missing_0007('shipments', 'last_carrier_sync_at', 'timestamp NULL DEFAULT NULL AFTER `carrier_integration_id`');

-- 2) Indexes
CALL add_idx_if_missing_0007('uq_carrier_tracking', 'UNIQUE KEY `uq_carrier_tracking` (`carrier_tracking_number`, `carrier_integration_id`)');
CALL add_idx_if_missing_0007('idx_carrier_sync', 'KEY `idx_carrier_sync` (`carrier_integration_id`, `last_carrier_sync_at`)');
CALL add_idx_if_missing_0007('idx_carrier_tn', 'KEY `idx_carrier_tn` (`carrier_tracking_number`)');

-- 3) Raw carrier event store
CREATE TABLE IF NOT EXISTS `carrier_tracking_events` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `shipment_id` int(11) NOT NULL,
    `integration_id` int(11) NOT NULL,
    `carrier_status` varchar(100) NOT NULL,
    `canonical_status` varchar(50) NOT NULL,
    `raw_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`raw_payload`)),
    `location` varchar(255) DEFAULT NULL,
    `event_timestamp` datetime DEFAULT NULL,
    `processed` tinyint(1) DEFAULT 0,
    `processed_at` timestamp NULL DEFAULT NULL,
    `error_message` text DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `shipment_id` (`shipment_id`),
    KEY `integration_id` (`integration_id`),
    KEY `processed` (`processed`),
    KEY `event_timestamp` (`event_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cleanup
DROP PROCEDURE IF EXISTS `add_col_if_missing_0007`;
DROP PROCEDURE IF EXISTS `add_idx_if_missing_0007`;

DELIMITER ;


-- ============================================
-- Migration: 0008_carrier_tracking_enhancements.sql
-- ============================================
-- ============================================================
-- Migration 0008: Carrier Tracking Enhancements + Deduplication
-- ============================================================
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. carrier_status + carrier_status_updated_at on shipments
--   2. dedup_hash unique key on carrier_tracking_events
--   3. (Optional) carrier_status_mappings master mapping table
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS add_col_if_missing_0008$$
CREATE PROCEDURE add_col_if_missing_0008(IN tbl VARCHAR(64), IN col VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS add_idx_if_missing_0008$$
CREATE PROCEDURE add_idx_if_missing_0008(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'carrier_tracking_events' AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `carrier_tracking_events` ADD ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) Carrier status cache on shipments (denormalized for fast filtering)
CALL add_col_if_missing_0008('shipments', 'carrier_status', 'varchar(100) DEFAULT NULL AFTER `carrier_integration_id`');
CALL add_col_if_missing_0008('shipments', 'carrier_status_updated_at', 'timestamp NULL DEFAULT NULL AFTER `carrier_status`');
CALL add_col_if_missing_0008('shipments', 'last_carrier_error', 'text DEFAULT NULL AFTER `last_carrier_sync_at`');

-- 2) Indexes on shipments for carrier queries
DELIMITER $$
DROP PROCEDURE IF EXISTS add_idx_shipments_0008$$
CREATE PROCEDURE add_idx_shipments_0008()
BEGIN
    DECLARE idx_exists INT DEFAULT 0;
    SELECT COUNT(*) INTO idx_exists FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipments' AND INDEX_NAME = 'idx_carrier_status';
    IF idx_exists = 0 THEN
        ALTER TABLE shipments ADD INDEX idx_carrier_status (carrier_status);
    END IF;
END$$
DELIMITER ;
CALL add_idx_shipments_0008();

-- 3) Dedup hash on carrier_tracking_events
CALL add_idx_if_missing_0008('uq_carrier_event_dedup', 'UNIQUE KEY `uq_carrier_event_dedup` (`dedup_hash`)');
CALL add_idx_if_missing_0008('idx_carrier_event_shipment_ts', 'KEY `idx_carrier_event_shipment_ts` (`shipment_id`, `event_timestamp`)');

-- 4) Carrier status mapping table (optional master config)
CREATE TABLE IF NOT EXISTS `carrier_status_mappings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `integration_id` INT(11) NOT NULL,
    `provider_status` VARCHAR(100) NOT NULL,
    `canonical_status` VARCHAR(50) NOT NULL,
    `notes` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_provider_status` (`integration_id`, `provider_status`),
    KEY `idx_integration` (`integration_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5) Seed some default mappings for common carrier statuses
INSERT IGNORE INTO `carrier_status_mappings` (`integration_id`, `provider_status`, `canonical_status`, `notes`) VALUES
(0, 'pre_transit', 'pending_pickup', 'Default mapping when no integration-specific override exists'),
(0, 'in_transit', 'in_transit', 'Default mapping'),
(0, 'out_for_delivery', 'out_for_delivery', 'Default mapping'),
(0, 'delivered', 'delivered', 'Default mapping'),
(0, 'return_to_sender', 'returned', 'Default mapping'),
(0, 'failure', 'delivery_failed', 'Default mapping'),
(0, 'cancelled', 'cancelled', 'Default mapping');

-- Cleanup
DROP PROCEDURE IF EXISTS add_col_if_missing_0008;
DROP PROCEDURE IF EXISTS add_idx_if_missing_0008;

DELIMITER ;


-- ============================================
-- Migration: 0009_performance_indexes.sql
-- ============================================
-- ============================================================
-- Migration 0009: Performance Indexes + Security Hardening
-- ============================================================
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. Composite indexes for critical query patterns
--   2. api_integrations secondary indexes
--   3. jobs table migration (from inline PHP to formal DDL)
--   4. Fix api_integrations base schema PK/AI
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS add_idx_if_missing_0009$$
CREATE PROCEDURE add_idx_if_missing_0009(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND INDEX_NAME = idx
    ) THEN
        SET @sql = def;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) tracking_history: composite index for public tracking lookup
CALL add_idx_if_missing_0009('idx_tracking_history_shipment_ts', 'ALTER TABLE `tracking_history` ADD INDEX `idx_shipment_event` (`shipment_id`, `event_timestamp`, `id`)');

-- 2) tracking_logs: composite index for fallback path
CALL add_idx_if_missing_0009('idx_tracking_logs_shipment_ts', 'ALTER TABLE `tracking_logs` ADD INDEX `idx_shipment_occurred` (`shipment_id`, `occurred_at`, `id`)');

-- 3) carrier_tracking_events: composite index for public merge query
CALL add_idx_if_missing_0009('idx_carrier_events_shipment_proc_ts', 'ALTER TABLE `carrier_tracking_events` ADD INDEX `idx_shipment_processed_event` (`shipment_id`, `processed`, `event_timestamp`, `id`)');

-- 4) api_integrations: secondary indexes for queries
CALL add_idx_if_missing_0009('idx_api_integrations_active_type', 'ALTER TABLE `api_integrations` ADD INDEX `idx_active_type` (`is_active`, `integration_type`)');
CALL add_idx_if_missing_0009('idx_api_integrations_provider', 'ALTER TABLE `api_integrations` ADD INDEX `idx_provider_type` (`provider`, `integration_type`)');

-- 5) payments: composite for dashboard revenue queries
CALL add_idx_if_missing_0009('idx_payments_status_date', 'ALTER TABLE `payments` ADD INDEX `idx_status_paid_date` (`status`, `paid_at`, `created_at`)');
CALL add_idx_if_missing_0009('idx_payments_customer_status', 'ALTER TABLE `payments` ADD INDEX `idx_customer_status` (`customer_id`, `status`)');

-- 6) shipments: composite for status + date queries
CALL add_idx_if_missing_0009('idx_shipments_status_created', 'ALTER TABLE `shipments` ADD INDEX `idx_status_created` (`status`, `created_at` DESC)');
CALL add_idx_if_missing_0009('idx_shipments_carrier_int', 'ALTER TABLE `shipments` ADD INDEX `idx_carrier_integration` (`carrier_integration_id`, `last_carrier_sync_at`)');

-- 7) notifications: composite for user inbox queries
CALL add_idx_if_missing_0009('idx_notifications_user_unread', 'ALTER TABLE `notifications` ADD INDEX `idx_user_unread` (`user_id`, `is_read`, `created_at`)');

-- 8) tracking_history: DB-level dedup (prevent duplicate timeline entries)
CALL add_idx_if_missing_0009('uq_tracking_history_event', 'ALTER TABLE `tracking_history` ADD UNIQUE INDEX `uq_shipment_event` (`shipment_id`, `tracking_number`, `event_timestamp`, `status`)');

-- 9) Ensure jobs table has migration coverage (same DDL as queue.php runtime creation)
CREATE TABLE IF NOT EXISTS `jobs` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `queue` varchar(50) NOT NULL,
    `payload` longtext NOT NULL,
    `attempts` int(11) DEFAULT 0,
    `status` enum('pending','processing','done','failed') DEFAULT 'pending',
    `available_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    `processed_at` datetime NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_queue_status` (`queue`, `status`, `available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10) Ensure api_integrations has PK + AUTO_INCREMENT
CALL add_idx_if_missing_0009('pk_api_integrations', 'ALTER TABLE `api_integrations` ADD PRIMARY KEY (`id`)');
CALL add_idx_if_missing_0009('ai_api_integrations', 'ALTER TABLE `api_integrations` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT');

-- Cleanup
DROP PROCEDURE IF EXISTS add_idx_if_missing_0009$$

DELIMITER ;


-- ============================================
-- Migration: 0010_shipment_listing_optimizations.sql
-- ============================================
-- ============================================================
-- Migration 0010: Additional Shipment Listing Optimizations
-- ============================================================
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. Missing single-column indexes on shipments for common filters
--   2. Composite indexes for frequent filter+sort combinations
--   3. Covering indexes for hot-path tracking lookups
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS add_idx_if_missing_0010$$
CREATE PROCEDURE add_idx_if_missing_0010(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND INDEX_NAME = idx
    ) THEN
        SET @sql = def;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) shipments: missing single-column indexes for common admin filters
CALL add_idx_if_missing_0010('idx_shipments_driver', 'ALTER TABLE `shipments` ADD INDEX `idx_driver` (`driver_id`)');
CALL add_idx_if_missing_0010('idx_shipments_branch', 'ALTER TABLE `shipments` ADD INDEX `idx_branch` (`branch_id`)');
CALL add_idx_if_missing_0010('idx_shipments_service', 'ALTER TABLE `shipments` ADD INDEX `idx_service` (`service_type`)');
CALL add_idx_if_missing_0010('idx_shipments_payment', 'ALTER TABLE `shipments` ADD INDEX `idx_payment` (`payment_status`)');
CALL add_idx_if_missing_0010('idx_shipments_destination', 'ALTER TABLE `shipments` ADD INDEX `idx_destination` (`destination_country`, `destination_city`)');

-- 2) shipments: composite indexes for common filter+sort patterns
CALL add_idx_if_missing_0010('idx_customer_status_created', 'ALTER TABLE `shipments` ADD INDEX `idx_customer_status_created` (`customer_id`, `status`, `created_at` DESC)');
CALL add_idx_if_missing_0010('idx_branch_status_created', 'ALTER TABLE `shipments` ADD INDEX `idx_branch_status_created` (`branch_id`, `status`, `created_at` DESC)');
CALL add_idx_if_missing_0010('idx_driver_status_created', 'ALTER TABLE `shipments` ADD INDEX `idx_driver_status_created` (`driver_id`, `status`, `created_at` DESC)');
CALL add_idx_if_missing_0010('idx_status_destination', 'ALTER TABLE `shipments` ADD INDEX `idx_status_destination` (`status`, `destination_country`, `destination_city`)');

-- 3) tracking_history: covering index for public tracking page
CALL add_idx_if_missing_0010('idx_tracking_history_number', 'ALTER TABLE `tracking_history` ADD INDEX `idx_tracking_number_ts` (`tracking_number`, `event_timestamp`, `status`, `location`)');

-- 4) api_integrations: covering index for active integration lookups
CALL add_idx_if_missing_0010('idx_active_integration_cover', 'ALTER TABLE `api_integrations` ADD INDEX `idx_active_cover` (`is_active`, `integration_type`, `provider`, `id`)');

-- 5) tracking_logs: composite index for fallback path with is_public filter
CALL add_idx_if_missing_0010('idx_tracking_logs_shipment_public_ts', 'ALTER TABLE `tracking_logs` ADD INDEX `idx_shipment_public_ts` (`shipment_id`, `is_public`, `occurred_at`, `id`)');

-- Cleanup
DROP PROCEDURE IF EXISTS add_idx_if_missing_0010$$

DELIMITER ;


-- ============================================
-- Migration: 0011_deprixa_unification.sql
-- ============================================
-- ============================================================
-- Migration 0011: Deprixa Unification
-- ============================================================
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. deprixa_customer_id mapping column on users
--   2. deprixa_booking_id mapping column on shipments
--   3. Indexes for fast lookup by legacy deprixa IDs
--
-- Apply:
--   mysql -u shipuser -p shipping_db < database/migrations/0011_deprixa_unification.sql
-- or run via a future apply_0011.php
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS `add_col_if_missing_0011`$$
CREATE PROCEDURE `add_col_if_missing_0011`(IN tbl VARCHAR(64), IN col VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = tbl AND COLUMN_NAME = col
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `', tbl, '` ADD COLUMN `', col, '` ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `add_idx_if_missing_0011`$$
CREATE PROCEDURE `add_idx_if_missing_0011`(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = idx
    ) THEN
        SET @sql = CONCAT('ALTER TABLE `users` ADD ', def);
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) Map deprixa customer IDs to main users table
CALL add_col_if_missing_0011('users', 'deprixa_customer_id', 'INT(11) DEFAULT NULL AFTER `id`');

-- 2) Map deprixa booking IDs to main shipments table
CALL add_col_if_missing_0011('shipments', 'deprixa_booking_id', 'INT(11) DEFAULT NULL AFTER `reference_number`');

-- 3) Indexes
CALL add_idx_if_missing_0011('uq_deprixa_customer_id', 'UNIQUE KEY `uq_deprixa_customer_id` (`deprixa_customer_id`)');
CALL add_idx_if_missing_0011('idx_deprixa_customer_id', 'KEY `idx_deprixa_customer_id` (`deprixa_customer_id`)');

-- 4) Index on shipments for deprixa booking lookup
SET @idx_exists := 0;
SELECT COUNT(*) INTO @idx_exists FROM information_schema.STATISTICS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipments' AND INDEX_NAME = 'idx_deprixa_booking_id';
IF @idx_exists = 0 THEN
    ALTER TABLE shipments ADD INDEX idx_deprixa_booking_id (deprixa_booking_id);
END IF;

-- Cleanup
DROP PROCEDURE IF EXISTS `add_col_if_missing_0011`;
DROP PROCEDURE IF EXISTS `add_idx_if_missing_0011`;

DELIMITER ;


-- ============================================
-- Migration: 0012_customer_indexes.sql
-- ============================================
-- ============================================================
-- Migration 0012: Customer Module Indexes + Reports Support
-- ============================================================
-- SAFE TO RE-RUN (idempotent). Adds:
--   1. Composite indexes for customer-scoped shipment/payment queries
--   2. Covering indexes for reports queries
--   3. Support for the new admin reports module
--
-- Apply:
--   mysql -u shipuser -p shipping_db < database/migrations/0012_customer_indexes.sql
-- ============================================================

DELIMITER $$

DROP PROCEDURE IF EXISTS add_idx_if_missing_0012$$
CREATE PROCEDURE add_idx_if_missing_0012(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'shipments' AND INDEX_NAME = idx
    ) THEN
        SET @sql = def;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DROP PROCEDURE IF EXISTS add_idx_payments_0012$$
CREATE PROCEDURE add_idx_payments_0012(IN idx VARCHAR(64), IN def VARCHAR(255))
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND INDEX_NAME = idx
    ) THEN
        SET @sql = def;
        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;
    END IF;
END$$

DELIMITER ;

-- 1) Customer-scoped shipment indexes
CALL add_idx_if_missing_0012('idx_customer_created', 'ALTER TABLE `shipments` ADD INDEX `idx_customer_created` (`customer_id`, `created_at` DESC)');
CALL add_idx_if_missing_0012('idx_customer_status_created', 'ALTER TABLE `shipments` ADD INDEX `idx_customer_status_created` (`customer_id`, `status`, `created_at` DESC)');
CALL add_idx_if_missing_0012('idx_customer_service_created', 'ALTER TABLE `shipments` ADD INDEX `idx_customer_service_created` (`customer_id`, `service_type`, `created_at` DESC)');

-- 2) Customer-scoped payment indexes
CALL add_idx_payments_0012('idx_payments_customer_created', 'ALTER TABLE `payments` ADD INDEX `idx_customer_created` (`customer_id`, `created_at` DESC)');
CALL add_idx_payments_0012('idx_payments_customer_status', 'ALTER TABLE `payments` ADD INDEX `idx_customer_status` (`customer_id`, `status`, `created_at` DESC)');

-- 3) Reports: shipment status + date composite for delivery performance
CALL add_idx_if_missing_0012('idx_reports_status_date', 'ALTER TABLE `shipments` ADD INDEX `idx_status_created_amount` (`status`, `created_at` DESC, `total_amount`)');

-- 4) Reports: origin/destination composite for route analysis
CALL add_idx_if_missing_0012('idx_reports_route', 'ALTER TABLE `shipments` ADD INDEX `idx_origin_dest_date` (`origin_country`, `destination_country`, `created_at` DESC)');

-- Cleanup
DROP PROCEDURE IF EXISTS add_idx_if_missing_0012;
DROP PROCEDURE IF EXISTS add_idx_payments_0012;

DELIMITER ;


-- ============================================
-- Migration: 0013_add_transit_location_to_tracking_history.sql
-- ============================================
-- ============================================================
-- Migration 0013: Add transit_location to tracking_history
-- ============================================================
-- Ensures the tracking_history table has a transit_location column
-- to store the parcel's physical location independently from
-- the customs procedure.
--
-- Safe to re-run via IF NOT EXISTS / ADD COLUMN IF NOT EXISTS.

SET @dbname = DATABASE();
SET @tbl = 'tracking_history';
SET @col = 'transit_location';
SET @sql := (
  SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tbl AND COLUMN_NAME = @col) > 0,
    'SELECT 1',
    CONCAT('ALTER TABLE `', @tbl, '` ADD COLUMN `', @col, '` varchar(255) DEFAULT NULL AFTER `location`')
  )
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE stmt;


-- ============================================
-- Migration: 0013_password_reset_tokens.sql
-- ============================================
-- Migration: 0013_password_reset_tokens
-- Purpose: Add secure token-based password reset table
-- Created: 2026-07-14

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_token` (`token`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_email` (`email`),
  KEY `idx_expires` (`expires_at`),
  CONSTRAINT `fk_prt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clean up expired tokens (older than 1 day)
DELETE FROM `password_reset_tokens` WHERE `expires_at` < DATE_SUB(NOW(), INTERVAL 1 DAY) AND `used_at` IS NULL;

-- ============================================
-- Migration: 0014_fastship_carrier.sql
-- ============================================
-- Migration 0014: FastShip Logistics carrier integration
-- No schema changes required. The existing api_integrations,
-- carrier_tracking_events, and webhook_subscriptions tables already
-- support arbitrary providers. This migration documents the version.

-- Optional: index provider + integration_type for faster carrier lookups.
ALTER TABLE api_integrations
  ADD INDEX idx_provider_type (provider, integration_type);

-- ============================================
-- Dynamically-created module tables (imported upfront for production)
-- These were previously created by ensureModuleTables() / ensureCourierTables()
-- on first admin access. Added here so they exist immediately after import.
-- ============================================

CREATE TABLE IF NOT EXISTS `package_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `shipment_id` int(11) NOT NULL,
    `item_name` varchar(255) NOT NULL,
    `category` varchar(120) DEFAULT NULL,
    `quantity` int(11) DEFAULT 1,
    `weight` decimal(10,2) DEFAULT 0,
    `declared_value` decimal(12,2) DEFAULT 0,
    `serial_number` varchar(120) DEFAULT NULL,
    `is_fragile` tinyint(1) DEFAULT 0,
    `is_dangerous` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `shipment_id` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courier_assignments` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `shipment_id` int(11) NOT NULL,
    `driver_id` int(11) DEFAULT NULL,
    `vehicle_id` int(11) DEFAULT NULL,
    `branch_id` int(11) DEFAULT NULL,
    `route` varchar(255) DEFAULT NULL,
    `distribution_center` varchar(255) DEFAULT NULL,
    `warehouse` varchar(255) DEFAULT NULL,
    `pickup_date` date DEFAULT NULL,
    `assigned_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `shipment_id` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shipment_notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `shipment_id` int(11) NOT NULL,
    `channel` enum('email','sms','push') NOT NULL DEFAULT 'email',
    `template` varchar(50) DEFAULT NULL,
    `recipient` varchar(255) DEFAULT NULL,
    `subject` varchar(255) DEFAULT NULL,
    `body` text DEFAULT NULL,
    `status` enum('sent','failed','queued') DEFAULT 'sent',
    `sent_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `shipment_id` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `delivery_confirmations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `shipment_id` int(11) NOT NULL,
    `receiver_name` varchar(255) DEFAULT NULL,
    `signature_path` varchar(255) DEFAULT NULL,
    `gps_lat` decimal(10,7) DEFAULT NULL,
    `gps_lng` decimal(11,8) DEFAULT NULL,
    `delivery_date` date DEFAULT NULL,
    `delivery_time` time DEFAULT NULL,
    `courier_notes` text DEFAULT NULL,
    `customer_feedback` text DEFAULT NULL,
    `photos` json DEFAULT NULL,
    `confirmed_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `shipment_id` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `refunds` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `shipment_id` int(11) NOT NULL,
    `amount` decimal(12,2) NOT NULL,
    `currency` varchar(10) DEFAULT 'USD',
    `reason` varchar(255) DEFAULT NULL,
    `refunded_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `shipment_id` (`shipment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `webhook_subscriptions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `event` varchar(100) NOT NULL,
    `url` varchar(500) NOT NULL,
    `secret` varchar(255) DEFAULT NULL,
    `is_active` tinyint(1) DEFAULT 1,
    `last_triggered_at` datetime DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `event` (`event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Deprixa / customer portal tables
CREATE TABLE IF NOT EXISTS `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` text DEFAULT NULL,
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `abono` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `cuenta` varchar(100) NOT NULL,
    `valor` decimal(12,2) NOT NULL DEFAULT 0.00,
    `descripcion` varchar(255) DEFAULT NULL,
    `fecha` date NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `cuenta` (`cuenta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `courier_officers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) DEFAULT NULL,
    `phone` varchar(90) DEFAULT NULL,
    `office` varchar(150) DEFAULT NULL,
    `role` varchar(50) DEFAULT 'officer',
    `estado` tinyint(1) DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


