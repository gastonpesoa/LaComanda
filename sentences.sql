DROP TABLE IF EXISTS `Usuario`;
CREATE TABLE `Usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `clave` varchar(50) NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `fecha_ultimo_login` datetime DEFAULT NULL,
  `estado` varchar(1) NOT NULL,
  `cantidad_operaciones` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS `Login`;
CREATE TABLE `Login` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `metodo` VARCHAR(250) DEFAULT NULL , 
  `ruta` VARCHAR(250) DEFAULT NULL , 
  PRIMARY KEY (`id`)
)DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

