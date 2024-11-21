ALTER TABLE `#__contact_details` ADD COLUMN `image_alt` varchar(255) AFTER `image` /** CAN FAIL **/;
ALTER TABLE `#__contact_details` ADD COLUMN `image_alt_empty` tinyint unsigned NOT NULL DEFAULT 0 AFTER `image_alt` /** CAN FAIL **/;
