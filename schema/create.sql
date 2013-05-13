CREATE TABLE `visits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `campaign` varchar(255) DEFAULT NULL,
  `pixel_used` varchar(255) DEFAULT NULL,
  `browser_stack` varchar(255) DEFAULT NULL,
  `referer` varchar(255) DEFAULT NULL,
  `user_agent` text,
  `server_var` text,
  `created` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8;