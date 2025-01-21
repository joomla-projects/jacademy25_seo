CREATE TABLE IF NOT EXISTS "#__noncore_extensions_safemode" (
	"extension_id" BIGINT NOT NULL,
	"name" VARCHAR(100) NOT NULL,
	"type" VARCHAR(20) NOT NULL,
	"element" VARCHAR(100) NOT NULL,
	"time" TIMESTAMP NOT NULL,
    PRIMARY KEY ("id")
);

INSERT INTO "#__extensions" ("package_id", "name", "type", "element", "folder", "client_id", "enabled", "access", "protected", "locked", "manifest_cache", "params", "custom_data", "ordering", "state") VALUES
(0, 'plg_system_safemode', 'plugin', 'safemode', 'system', 0, 1, 1, 0, 0, '', '{}', '', 55, 0);