##
# sql changes made in phase 2
##
ALTER TABLE `gamescore` ADD `support_language_uid` SMALLINT( 3 ) UNSIGNED NOT NULL AFTER `language_uid` ,
ADD INDEX ( `support_language_uid` );
ALTER TABLE `language` ADD `flash_version` VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;