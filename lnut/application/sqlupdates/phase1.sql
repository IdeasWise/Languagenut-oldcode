##
# sql changes made in phase 1
##
ALTER TABLE `language` ADD `flash_version` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `gamescore` ADD `support_language_uid` SMALLINT( 3 ) UNSIGNED NOT NULL AFTER `language_uid` ,
ADD INDEX ( `support_language_uid` );