CREATE TABLE IF NOT EXISTS project (  id varchar(50) NOT NULL,  `name` tinytext NOT NULL,  `status` int(1) NOT NULL,  progress int(3) NOT NULL,  `owner` varchar(50) NOT NULL COMMENT 'usuario que lo ha creado',  node varchar(50) NOT NULL COMMENT 'nodo en el que se ha creado',  amount int(6) DEFAULT NULL COMMENT 'acumulado actualmente',  created date DEFAULT NULL,  published date DEFAULT NULL,  success date DEFAULT NULL,  closed date DEFAULT NULL,  contract_name varchar(255) DEFAULT NULL,  contract_surname varchar(255) DEFAULT NULL,  contract_nif varchar(10) DEFAULT NULL COMMENT 'Guardar sin espacios ni puntos ni guiones',  contract_email varchar(256) DEFAULT NULL,  phone varchar(9) DEFAULT NULL COMMENT 'guardar sin espacios ni puntos',  address tinytext,  zipcode varchar(10) DEFAULT NULL,  location varchar(255) DEFAULT NULL,  country varchar(50) DEFAULT NULL,  PRIMARY KEY (id),  KEY `owner` (`owner`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Proyectos de la plataforma';ALTER TABLE `project` ADD `image` VARCHAR( 256 ) NULL ,ADD `description` TEXT NULL ,ADD `motivation` TEXT NULL ,ADD `about` TEXT NULL ,ADD `goal` TEXT NULL ,ADD `related` TEXT NULL ,ADD `category` VARCHAR( 50 ) NULL ,ADD `media` VARCHAR( 256 ) NULL ,ADD `currently` INT( 1 ) NULL ,ADD `project_location` VARCHAR( 256 ) NULL ,ADD `resource` TEXT NULL ;ALTER TABLE `project` ADD `updated` DATE NULL AFTER `created` ;