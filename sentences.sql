DROP TABLE IF EXISTS `Usuario`;
CREATE TABLE `Usuario`
(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `clave` varchar(350) NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `fecha_registro` datetime NOT NULL,
  `fecha_ultimo_login` datetime DEFAULT NULL,
  `estado` varchar(1) NOT NULL,
  `cantidad_operaciones` int(11) DEFAULT '0',
  PRIMARY KEY(`id`)
)DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS `Login`;
CREATE TABLE `Login`
(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idUser` int(11) NOT NULL,
  `fecha` datetime NOT NULL,
  `metodo` VARCHAR(250) DEFAULT NULL ,
  `ruta` VARCHAR(250) DEFAULT NULL ,
  PRIMARY KEY(`id`)
)DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS `Menu`;
CREATE TABLE `Menu`
(
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `precio` int(11) NOT NULL,
  `sector` varchar(50) NOT NULL,
  PRIMARY KEY(`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

INSERT INTO `Menu`
VALUES
  (2, 'Pizza Muzzarella', 178, 'cocinero'),
  (12, 'Milanesa a la Napolitana', 90, 'cocinero'),
  (22, 'Cerveza', 60, 'cervecero'),
  (42, 'Empanadas', 30, 'cocinero'),
  (52, 'Vino', 100, 'bartender'),
  (62, 'Jugo de Naranja', 60, 'bartender'),
  (72, 'Canelones', 120, 'cocinero');

DROP TABLE IF EXISTS `Pedido`;
CREATE TABLE `Pedido` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(5) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `horaInicial` time NOT NULL,
  `horaEntregaEstimada` time DEFAULT NULL,
  `horaEntregaReal` time DEFAULT NULL,
  `idMesa` varchar(5) NOT NULL,
  `idMenu` int(11) NOT NULL,
  `idUser` int(11) DEFAULT NULL,
  `idMozo` int(11) NOT NULL,
  `nombreCliente` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS `Mesa`;
CREATE TABLE `Mesa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigoMesa` varchar(5) NOT NULL,
  `estado` varchar(50) NOT NULL,
  `foto` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS `Encuesta`;
CREATE TABLE `Encuesta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigoMesa` varchar(5) NOT NULL,
  `idMozo` int(11) NOT NULL,
  `puntajeMesa` int(11) NOT NULL,
  `puntajeMozo` int(11) NOT NULL,
  `puntajeCocinero` int(11) NOT NULL,
  `puntajeRestaurante` int(11) NOT NULL,
  `comentario` varchar(66) NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

DROP TABLE IF EXISTS `Factura`;
CREATE TABLE `Factura` (
  `idFactura` int(11) NOT NULL AUTO_INCREMENT,
  `importe` int(11) NOT NULL,
  `codigoMesa` varchar(5) NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`idFactura`)
) DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

