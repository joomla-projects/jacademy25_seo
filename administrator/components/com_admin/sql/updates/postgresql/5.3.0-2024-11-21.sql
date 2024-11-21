ALTER TABLE "#__contact_details" ADD COLUMN "image_alt" varchar(255) /** CAN FAIL **/;
ALTER TABLE "#__contact_details" ADD COLUMN "image_alt_empty" smallint NOT NULL DEFAULT 0 /** CAN FAIL **/;
