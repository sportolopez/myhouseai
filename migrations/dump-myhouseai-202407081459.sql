-- MySQL dump 10.13  Distrib 8.0.26, for Win64 (x86_64)
--
-- Host: localhost    Database: myhouseai
-- ------------------------------------------------------
-- Server version	8.3.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `doctrine_migration_versions`
--

LOCK TABLES `doctrine_migration_versions` WRITE;
/*!40000 ALTER TABLE `doctrine_migration_versions` DISABLE KEYS */;
INSERT INTO `doctrine_migration_versions` VALUES ('DoctrineMigrations\\Version20240630233946','2024-06-30 23:40:24',63),('DoctrineMigrations\\Version20240705180812','2024-07-05 18:08:41',514),('DoctrineMigrations\\Version20240705181524','2024-07-05 18:15:39',241),('DoctrineMigrations\\Version20240705182716','2024-07-05 18:27:29',164),('DoctrineMigrations\\Version20240705183749','2024-07-05 18:37:54',73),('DoctrineMigrations\\Version20240707182215','2024-07-08 01:22:21',211),('DoctrineMigrations\\Version20240707182415','2024-07-08 01:22:22',8),('DoctrineMigrations\\Version20240708012012','2024-07-08 01:22:22',12),('DoctrineMigrations\\Version20240708012050','2024-07-08 17:08:15',30),('DoctrineMigrations\\Version20240708165314','2024-07-08 17:08:16',96),('DoctrineMigrations\\Version20240708170746','2024-07-08 17:17:31',387),('DoctrineMigrations\\Version20240708172427','2024-07-08 17:24:32',78),('DoctrineMigrations\\Version20240708172542','2024-07-08 17:25:45',159);
/*!40000 ALTER TABLE `doctrine_migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `imagen`
--

DROP TABLE IF EXISTS `imagen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `imagen` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `usuario_id` int NOT NULL,
  `fecha` datetime NOT NULL,
  `estilo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo_habitacion` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `img_origen` longblob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8319D2B3DB38439E` (`usuario_id`),
  CONSTRAINT `FK_8319D2B3DB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `imagen`
--

LOCK TABLES `imagen` WRITE;
/*!40000 ALTER TABLE `imagen` DISABLE KEYS */;
INSERT INTO `imagen` VALUES ('07466369-80ed-4f73-a1ad-85d75676339c',1,'2024-07-08 02:16:32','Moderno','Moderno',''),('0ebdecd9-34b9-4b46-805d-d322bd592e70',1,'2024-07-08 01:28:11','Moderno','Moderno',''),('61512d95-fd54-4289-9bd1-860d64bf5a74',1,'2024-07-08 02:14:32','Moderno','Moderno',''),('64c877d9-5e1b-4105-a029-0ced02b925bc',1,'2024-07-08 02:14:38','Moderno','Moderno',''),('92352bfe-9003-457c-853c-a044f2a17988',1,'2024-07-08 02:02:31','Moderno','Moderno',''),('a8ec6efc-693c-485f-8431-53179ff883d9',1,'2024-07-08 02:11:53','Moderno','Moderno',''),('a9303ec9-ce82-4e90-b31f-a5d0278c13db',1,'2024-07-08 02:13:53','Moderno','Moderno',''),('abec3575-613d-462b-b837-3d70bffbecfe',1,'2024-07-08 02:12:46','Moderno','Moderno',''),('b93865be-e3e8-4a46-b09f-fc613e27666d',1,'2024-07-08 02:14:25','Moderno','Moderno',''),('c3927dbc-5143-4a7b-848b-138b3a510b76',1,'2024-07-08 01:28:19','Moderno','Moderno',''),('c625045e-e6bf-4eb3-a717-67fd362dbe93',1,'2024-07-08 01:28:18','Moderno','Moderno',''),('cd656f5d-9a07-4644-8a80-926453b88be7',1,'2024-07-08 02:03:58','Moderno','Moderno',''),('d1202675-36dc-441f-9fbd-68d3ab201913',1,'2024-07-08 02:01:19','Moderno','Moderno',''),('d9dd8f30-f450-4dd3-9c42-1af5b190d455',1,'2024-07-08 02:20:56','Moderno','Moderno',''),('ea7ba433-1e42-43e7-8983-d40d68fa3762',1,'2024-07-08 02:21:28','Moderno','Moderno',''),('ec75452e-fab8-4762-bf2b-98b3cbfc6c24',1,'2024-07-08 02:13:10','Moderno','Moderno',''),('f62164b9-30b2-4a0d-8d97-5d2cffe23813',1,'2024-07-08 01:30:43','Moderno','Moderno',''),('f87559cc-eec4-4e6e-abe2-75e9b51b96d3',1,'2024-07-08 02:04:50','Moderno','Moderno','');
/*!40000 ALTER TABLE `imagen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cantidad_imagenes_disponibles` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Tincho','prueba@gmail.com',5);
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_compras`
--

DROP TABLE IF EXISTS `usuario_compras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario_compras` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `fecha` datetime NOT NULL,
  `cantidad` int NOT NULL,
  `monto` double NOT NULL,
  `moneda` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `medio_pago` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_4FB44BF0DB38439E` (`usuario_id`),
  CONSTRAINT `FK_4FB44BF0DB38439E` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_compras`
--

LOCK TABLES `usuario_compras` WRITE;
/*!40000 ALTER TABLE `usuario_compras` DISABLE KEYS */;
/*!40000 ALTER TABLE `usuario_compras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variacion`
--

DROP TABLE IF EXISTS `variacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `variacion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `imagen_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `fecha` datetime NOT NULL,
  `img` longblob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_AF555CCC763C8AA7` (`imagen_id`),
  CONSTRAINT `FK_AF555CCC763C8AA7` FOREIGN KEY (`imagen_id`) REFERENCES `imagen` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variacion`
--

LOCK TABLES `variacion` WRITE;
/*!40000 ALTER TABLE `variacion` DISABLE KEYS */;
/*!40000 ALTER TABLE `variacion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'myhouseai'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-07-08 14:59:15
