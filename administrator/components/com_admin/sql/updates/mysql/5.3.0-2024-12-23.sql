ALTER TABLE `#__assets` MODIFY `name` varchar(255) NOT NULL;
ALTER TABLE `#__assets` MODIFY `title` varchar(255) NOT NULL;
ALTER TABLE `#__categories` MODIFY `extension` varchar(100) DEFAULT '' NOT NULL;
ALTER TABLE `#__workflows` MODIFY `extension` varchar(255) NOT NULL;
ALTER TABLE `#__workflow_associations` MODIFY `extension` varchar(255) NOT NULL;