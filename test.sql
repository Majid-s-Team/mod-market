ALTER TABLE `mod-market`.`cards`   
	CHANGE `card_holder` `card_holder` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `card_number` `card_number` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `expiry_month` `expiry_month` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `expiry_year` `expiry_year` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
	CHANGE `cvv` `cvv` VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
