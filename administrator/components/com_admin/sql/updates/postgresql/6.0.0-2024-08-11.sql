-- Update link to featured

UPDATE "#__menu"
SET "link" = 'index.php?option=com_content&view=articles&filter[featured]=1'
WHERE "link" = 'index.php?option=com_content&view=featured'
AND "client_id" = 1;

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'plg_fields_note', 'plugin', 'note', 'fields', 0, 1, 1, 0, 1, '', '{"class":"alert alert-info","heading":"h4"}', '', 18, 0);
