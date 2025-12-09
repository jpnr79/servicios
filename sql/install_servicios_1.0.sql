DROP TABLE IF EXISTS `glpi_plugin_servicios_servicios`;
CREATE TABLE `glpi_plugin_servicios_servicios` (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `entities_id` int(11) unsigned NOT NULL default '0',
  `is_recursive` tinyint(1) NOT NULL default '0',
  `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
  `plugin_servicios_serviciotypes_id` int(11) unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_servicios_serviciotypes (id)',
  `users_id_tech` int(11) unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
  `groups_id_tech` int(11) unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_groups (id)',
  `suppliers_id` int(11) unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
  `locations_id` int(11) unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_locations (id)',
  `date_mod` timestamp NULL DEFAULT NULL,
  `is_helpdesk_visible` BIGINT UNSIGNED NOT NULL default '1',
  `comment` text collate utf8mb4_unicode_ci,
  `is_deleted` tinyint(1) NOT NULL default '0',
  `states_id` int(11) unsigned NOT NULL default '0',
  `plugin_servicios_orientados_id` int(11) unsigned NOT NULL default '1',
  `groups_id` int(11) unsigned NOT NULL default '0',
  `users_id` int(11) unsigned NOT NULL default '0',
  `responsable_id` int(11) unsigned NOT NULL default '0',
  `titulo` varchar(255) collate utf8mb4_unicode_ci NOT NULL,
  `descripcion` text collate utf8mb4_unicode_ci,
  `garantia` text collate utf8mb4_unicode_ci,
  `cal_id` int(11) unsigned NOT NULL default '0',
  `plugin_servicios_criticidads_id` int(11) unsigned NOT NULL default '2',
  `acronimosi` varchar(45) collate utf8mb4_unicode_ci default NULL,
  `responsableseguridad_id` int(11) unsigned NOT NULL default '0',
  `nusuarios` varchar(45) collate utf8mb4_unicode_ci default NULL,
  `is_afectadoens` tinyint(1) NOT NULL default '0',
  `plugin_servicios_ensnivels_id` int(11) unsigned NOT NULL default '0',
  `ens_estado_implantacion` int(11) unsigned NOT NULL default '0',
  `autenticationinterno_id` int(11) unsigned NOT NULL default '0',
  `autenticationexterno_id` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`),
  KEY `entities_id` (`entities_id`),
  KEY `plugin_servicios_serviciotypes_id` (`plugin_servicios_serviciotypes_id`),
  KEY `users_id_tech` (`users_id_tech`),
  KEY `groups_id_tech` (`groups_id_tech`),
  KEY `suppliers_id` (`suppliers_id`),
  KEY `locations_id` (`locations_id`),
  KEY `date_mod` (`date_mod`),
  KEY `is_helpdesk_visible` (`is_helpdesk_visible`),
  KEY `is_deleted` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_servicios_serviciotypes`;
   CREATE TABLE `glpi_plugin_servicios_serviciotypes` (
   `id` BIGINT UNSIGNED NOT NULL auto_increment,
   `entities_id` int(11) unsigned NOT NULL default '0',
   `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
   `comment` text collate utf8mb4_unicode_ci,
   PRIMARY KEY  (`id`),
   KEY `name` (`name`),
   KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_servicios_servicios_items`;
CREATE TABLE `glpi_plugin_servicios_servicios_items` (
   `id` BIGINT UNSIGNED NOT NULL auto_increment,
   `plugin_servicios_servicios_id` int(11) unsigned NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_servicios_servicios (id)',
   `items_id` int(11) unsigned NOT NULL default '0' COMMENT 'RELATION to various tables, according to itemtype (id)',
   `itemtype` varchar(100) collate utf8mb4_unicode_ci NOT NULL COMMENT 'see .class.php file',
   PRIMARY KEY  (`id`),
   UNIQUE KEY `unicity` (`plugin_servicios_servicios_id`,`items_id`,`itemtype`),
  KEY `FK_device` (`items_id`,`itemtype`),
  KEY `item` (`itemtype`,`items_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `glpi_displaypreferences` (`id`,`itemtype`,`num`,`rank`,`users_id`) VALUES (NULL,'PluginServiciosServicio','2','2','0');
INSERT INTO `glpi_displaypreferences` (`id`,`itemtype`,`num`,`rank`,`users_id`) VALUES (NULL,'PluginServiciosServicio','3','4','0');
INSERT INTO `glpi_displaypreferences` (`id`,`itemtype`,`num`,`rank`,`users_id`) VALUES (NULL,'PluginServiciosServicio','6','5','0');
INSERT INTO `glpi_displaypreferences` (`id`,`itemtype`,`num`,`rank`,`users_id`) VALUES (NULL,'PluginServiciosServicio','7','6','0');
INSERT INTO `glpi_displaypreferences` (`id`,`itemtype`,`num`,`rank`,`users_id`) VALUES (NULL,'PluginServiciosServicio','8','7','0');

DROP TABLE IF EXISTS `glpi_plugin_servicios_criticidads`;
CREATE TABLE `glpi_plugin_servicios_criticidads` (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
  `comment` text collate utf8mb4_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_servicios_orientados`;
CREATE TABLE `glpi_plugin_servicios_orientados` (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
  `comment` text collate utf8mb4_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_servicios_ensnivels`;
CREATE TABLE `glpi_plugin_servicios_ensnivels` (
  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
  `comment` text collate utf8mb4_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_servicios_autentications`;
CREATE TABLE `glpi_plugin_servicios_autentications` (

  `id` BIGINT UNSIGNED NOT NULL auto_increment,
  `name` varchar(255) collate utf8mb4_unicode_ci default NULL,
  `comment` text collate utf8mb4_unicode_ci,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*
-- Query: SELECT * FROM glpi085.glpi_plugin_servicios_ensnivels
-- Date: 2015-07-17 13:02
*/
INSERT INTO `glpi_plugin_servicios_ensnivels` (`id`,`name`,`comment`) VALUES (1,'Alto','');
INSERT INTO `glpi_plugin_servicios_ensnivels` (`id`,`name`,`comment`) VALUES (2,'Medio','');
INSERT INTO `glpi_plugin_servicios_ensnivels` (`id`,`name`,`comment`) VALUES (3,'Bajo','');

/*
-- Query: SELECT * FROM glpi085.glpi_plugin_servicios_autentications
-- Date: 2015-07-17 13:04
*/
INSERT INTO `glpi_plugin_servicios_autentications` (`id`,`name`,`comment`) VALUES (1,'Contraseña','');














