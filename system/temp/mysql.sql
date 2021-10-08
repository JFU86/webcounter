SET NAMES 'UTF8';
/* SPLIT */
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
/* SPLIT */
CREATE TABLE IF NOT EXISTS `webcounter_referer` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `referer` varchar(255) NOT NULL,
  `anzahl` int(10) unsigned NOT NULL,
  `erstbesuch` datetime NOT NULL,
  `letztbesuch` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `referer` (`referer`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
/* SPLIT */
CREATE TABLE IF NOT EXISTS `webcounter_reload` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ipaddress` varchar(30) NOT NULL default '',
  `visit` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
/* SPLIT */
CREATE TABLE IF NOT EXISTS `webcounter_visitor` (
  `datum` date NOT NULL,
  `stunde` enum('0','1','2','3','4','5','6','7','8','9','10','11','12','13','14','15','16','17','18','19','20','21','22','23') NOT NULL,
  `anzahl` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`datum`,`stunde`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Besucherzähler nach Datum,Uhrzeit';