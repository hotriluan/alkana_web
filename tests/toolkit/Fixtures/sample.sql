-- Alkana Toolkit — sample SQL fixture for import tests
SET FOREIGN_KEY_CHECKS=0;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) NOT NULL DEFAULT '',
  `option_value` longtext NOT NULL,
  `autoload` varchar(20) NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `wp_options` VALUES (1,'siteurl','http://localhost/alkana','yes');
INSERT INTO `wp_options` VALUES (2,'blogname','Alkana Test','yes');
INSERT INTO `wp_options` VALUES (3,'blogdescription','Paint your world','yes');
INSERT INTO `wp_options` VALUES (4,'_transient_test','a:2:{i:0;s:3:"foo";i:1;s:3:"bar";}','no');

SET FOREIGN_KEY_CHECKS=1;
