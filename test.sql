ALTER TABLE `mod-market`.`cards`
	CHANGE `card_holder` `card_holder` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `card_number` `card_number` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `expiry_month` `expiry_month` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `expiry_year` `expiry_year` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `cvv` `cvv` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
INSERT INTO grades (name, is_active, created_at, updated_at) VALUES
('1st Grade', 1, NULL, NULL),
('2nd Grade', 1, NULL, NULL),
('3rd Grade', 1, NULL, NULL),
('4th Grade', 1, NULL, NULL),
('5th Grade', 1, NULL, NULL),
('6th Grade', 1, NULL, NULL),
('7th Grade', 1, NULL, NULL),
('8th Grade', 1, NULL, NULL),
('9th Grade', 1, NULL, NULL),
('10th Grade', 1, NULL, NULL),
('11th Grade', 1, NULL, NULL),
('12th Grade', 1, NULL, NULL);
INSERT INTO teeshirt_sizes (name, is_active, created_at, updated_at) VALUES
('Youth Small', 1, NULL, NULL),
('Youth Medium', 1, NULL, NULL),
('Youth Large', 1, NULL, NULL),
('Youth X-Large', 1, NULL, NULL),
('Small', 1, NULL, NULL),
('Medium', 1, NULL, NULL),
('Large', 1, NULL, NULL),
('X-Large', 1, NULL, NULL);
INSERT INTO gurukal (name, is_active, created_at, updated_at) VALUES
('Agastya', 1, NULL, NULL),
('Angirasa', 1, NULL, NULL),
('Anasuya', 1, NULL, NULL),
('Bhargava', 1, NULL, NULL),
('Dhruva', 1, NULL, NULL),
('Janaki', 1, NULL, NULL),
('Kashyapa', 1, NULL, NULL),
('Meera', 1, NULL, NULL),
('Nachiketa', 1, NULL, NULL),
('Sabari', 1, NULL, NULL),
('Sandipani', 1, NULL, NULL),
('Valmiki', 1, NULL, NULL),
('Vyaasa', 1, NULL, NULL);
INSERT INTO activities (name, is_active, created_at, updated_at) VALUES
('Arts and Crafts', 1, NULL, NULL),
('Book Club', 1, NULL, NULL),
('Carpool', 1, NULL, NULL),
('Events', 1, NULL, NULL),
('Teaching', 1, NULL, NULL),
('Weekly General Help', 1, NULL, NULL);




ALTER TABLE `mod-market`.`messages`
  ADD COLUMN `message_type` ENUM (
    'text',
    'image',
    'video',
    'link',
    'emoji'
  ) NULL AFTER `message`;


ALTER TABLE `messages`
ADD COLUMN `vehicle_ad_id` BIGINT UNSIGNED NULL AFTER `receiver_id`,
ADD CONSTRAINT `messages_vehicle_ad_id_foreign`
FOREIGN KEY (`vehicle_ad_id`) REFERENCES `vehicle_ads`(`id`)
ON DELETE SET NULL ON UPDATE CASCADE;



ALTER TABLE `users`
  ADD COLUMN `platform_id` VARCHAR(255) NULL AFTER `email`,
  ADD COLUMN `platform_type` ENUM('facebook','google','apple') NOT NULL AFTER `platform_id`,
  ADD COLUMN `device_type` ENUM('android','ios','web') NOT NULL AFTER `platform_type`,
  ADD COLUMN `device_token` VARCHAR(255) NULL AFTER `device_type`;

