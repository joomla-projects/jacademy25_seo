INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state")
SELECT 0, 'plg_quickicon_autoupdate', 'plugin', 'autoupdate', 'quickicon', 0, 1, 1, 0, 1, '', '', '', -1, 0
WHERE NOT EXISTS (SELECT * FROM "#__extensions" e WHERE e."type" = 'plugin' AND e."element" = 'autoupdate' AND e."folder" = 'quickicon' AND e."client_id" = 0);
