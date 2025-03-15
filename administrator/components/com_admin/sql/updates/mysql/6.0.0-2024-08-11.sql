-- Update link to featured

UPDATE `#__menu`
SET `link` = 'index.php?option=com_content&view=articles&filter[featured]=1'
WHERE `link` = 'index.php?option=com_content&view=featured'
AND `client_id` = 1;

INSERT INTO `#__extensions` (`package_id`, `name`, `type`, `element`, `folder`, `client_id`, `enabled`, `access`, `protected`, `locked`, `manifest_cache`, `params`, `custom_data`, `ordering`, `state`) VALUES
(0, 'plg_fields_number', 'plugin', 'number', 'fields', 0, 1, 1, 0, 1, '', '{"min":"1.0","max":"100.0","step":"0.1"}', '', 17, 0);
