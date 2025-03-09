ALTER TABLE "#__assets" ALTER COLUMN "name" TYPE varchar(255) NOT NULL;
ALTER TABLE "#__assets" ALTER COLUMN "title" TYPE varchar(255) NOT NULL;
ALTER TABLE "#__categories" ALTER COLUMN "extension" TYPE varchar(100) DEFAULT '' NOT NULL;
ALTER TABLE "#__workflows" ALTER COLUMN "extension" TYPE varchar(255) NOT NULL;
ALTER TABLE "#__workflow_associations" ALTER COLUMN TYPE"extension" varchar(255) NOT NULL;