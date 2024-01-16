-- MariaDB dump 10.19  Distrib 10.5.21-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: mysql    Database: draw
-- ------------------------------------------------------
-- Server version	8.0.23

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `acme__user_address`
--

DROP TABLE IF EXISTS `acme__user_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acme__user_address` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `address_street` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address_postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address_city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address_country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `position` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `IDX_7FBCA30BA76ED395` (`user_id`),
  CONSTRAINT `FK_7FBCA30BA76ED395` FOREIGN KEY (`user_id`) REFERENCES `draw_acme__user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acme__user_address`
--

LOCK TABLES `acme__user_address` WRITE;
/*!40000 ALTER TABLE `acme__user_address` DISABLE KEYS */;
/*!40000 ALTER TABLE `acme__user_address` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `acme__user_tag`
--

DROP TABLE IF EXISTS `acme__user_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acme__user_tag` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `tag_id` bigint NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8C67AC97A76ED395` (`user_id`),
  KEY `IDX_8C67AC97BAD26311` (`tag_id`),
  CONSTRAINT `FK_8C67AC97A76ED395` FOREIGN KEY (`user_id`) REFERENCES `draw_acme__user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_8C67AC97BAD26311` FOREIGN KEY (`tag_id`) REFERENCES `draw_acme__tag` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `acme__user_tag`
--

LOCK TABLES `acme__user_tag` WRITE;
/*!40000 ALTER TABLE `acme__user_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `acme__user_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `command__execution`
--

DROP TABLE IF EXISTS `command__execution`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `command__execution` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `command` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'N/A',
  `command_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `input` json NOT NULL,
  `output` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `auto_acknowledge_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `state` (`state`),
  KEY `command` (`command`),
  KEY `command_name` (`command_name`),
  KEY `state_updated` (`state`,`updated_at`),
  KEY `auto_acknowledge_reason` (`auto_acknowledge_reason`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `command__execution`
--

LOCK TABLES `command__execution` WRITE;
/*!40000 ALTER TABLE `command__execution` DISABLE KEYS */;
INSERT INTO `command__execution` VALUES ('1eeb4af1-e646-649c-8b23-0242ac140005','N/A','draw:doctrine:mysql-dump','started','{\"file\": \"./data/sql/dump.sql\", \"--env\": \"test\", \"command\": \"draw:doctrine:mysql-dump\", \"--connection\": \"default\", \"--no-interaction\": true}','','2024-01-16 20:37:51','2024-01-16 20:37:51',NULL);
/*!40000 ALTER TABLE `command__execution` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draw__config`
--

DROP TABLE IF EXISTS `draw__config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draw__config` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` json NOT NULL,
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draw__config`
--

LOCK TABLES `draw__config` WRITE;
/*!40000 ALTER TABLE `draw__config` DISABLE KEYS */;
/*!40000 ALTER TABLE `draw__config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draw_acme__base_object`
--

DROP TABLE IF EXISTS `draw_acme__base_object`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draw_acme__base_object` (
  `id` int NOT NULL AUTO_INCREMENT,
  `discriminator_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attribute_1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_time_immutable` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draw_acme__base_object`
--

LOCK TABLES `draw_acme__base_object` WRITE;
/*!40000 ALTER TABLE `draw_acme__base_object` DISABLE KEYS */;
/*!40000 ALTER TABLE `draw_acme__base_object` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draw_acme__tag`
--

DROP TABLE IF EXISTS `draw_acme__tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draw_acme__tag` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draw_acme__tag`
--

LOCK TABLES `draw_acme__tag` WRITE;
/*!40000 ALTER TABLE `draw_acme__tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `draw_acme__tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draw_acme__user`
--

DROP TABLE IF EXISTS `draw_acme__user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draw_acme__user` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_password_updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `level` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `address_street` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address_postal_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address_city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `address_country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `date_of_birth` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `comment` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `totp_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `force_enabling_two_factor_authentication` tinyint(1) NOT NULL DEFAULT '0',
  `need_change_password` tinyint(1) NOT NULL DEFAULT '0',
  `manual_lock` tinyint(1) NOT NULL DEFAULT '0',
  `email_auth_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_auth_code_generated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `two_factor_authentication_enabled_providers` json DEFAULT NULL,
  `child_object1_id` int DEFAULT NULL,
  `child_object2_id` int DEFAULT NULL,
  `on_delete_restrict_id` int DEFAULT NULL,
  `on_delete_cascade_id` int DEFAULT NULL,
  `on_delete_set_null_id` int DEFAULT NULL,
  `on_delete_cascade_config_overridden_id` int DEFAULT NULL,
  `on_delete_cascade_attribute_overridden_id` int DEFAULT NULL,
  `preferred_locale` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_5E86F9A7E7927C74` (`email`),
  KEY `IDX_5E86F9A79E2C07EE` (`child_object1_id`),
  KEY `IDX_5E86F9A78C99A800` (`child_object2_id`),
  KEY `IDX_5E86F9A7E864B41F` (`on_delete_restrict_id`),
  KEY `IDX_5E86F9A77FFFEA0E` (`on_delete_cascade_id`),
  KEY `IDX_5E86F9A72A00A4ED` (`on_delete_set_null_id`),
  KEY `IDX_5E86F9A79E145C6D` (`on_delete_cascade_config_overridden_id`),
  KEY `IDX_5E86F9A79A3CF4B7` (`on_delete_cascade_attribute_overridden_id`),
  CONSTRAINT `FK_5E86F9A72A00A4ED` FOREIGN KEY (`on_delete_set_null_id`) REFERENCES `draw_acme__base_object` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_5E86F9A77FFFEA0E` FOREIGN KEY (`on_delete_cascade_id`) REFERENCES `draw_acme__base_object` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5E86F9A78C99A800` FOREIGN KEY (`child_object2_id`) REFERENCES `draw_acme__base_object` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_5E86F9A79A3CF4B7` FOREIGN KEY (`on_delete_cascade_attribute_overridden_id`) REFERENCES `draw_acme__base_object` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5E86F9A79E145C6D` FOREIGN KEY (`on_delete_cascade_config_overridden_id`) REFERENCES `draw_acme__base_object` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_5E86F9A79E2C07EE` FOREIGN KEY (`child_object1_id`) REFERENCES `draw_acme__base_object` (`id`) ON DELETE SET NULL,
  CONSTRAINT `FK_5E86F9A7E864B41F` FOREIGN KEY (`on_delete_restrict_id`) REFERENCES `draw_acme__base_object` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draw_acme__user`
--

LOCK TABLES `draw_acme__user` WRITE;
/*!40000 ALTER TABLE `draw_acme__user` DISABLE KEYS */;
/*!40000 ALTER TABLE `draw_acme__user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draw_entity_migrator__migration`
--

DROP TABLE IF EXISTS `draw_entity_migrator__migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draw_entity_migrator__migration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draw_entity_migrator__migration`
--

LOCK TABLES `draw_entity_migrator__migration` WRITE;
/*!40000 ALTER TABLE `draw_entity_migrator__migration` DISABLE KEYS */;
/*!40000 ALTER TABLE `draw_entity_migrator__migration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draw_messenger__message`
--

DROP TABLE IF EXISTS `draw_messenger__message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draw_messenger__message` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `message_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `available_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `delivered_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expires_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_7403A37312EDE674E3BD61CE` (`message_class`,`available_at`),
  KEY `IDX_7403A373FB7336F0E3BD61CE` (`queue_name`,`available_at`),
  KEY `IDX_7403A373E3BD61CE` (`available_at`),
  KEY `IDX_7403A37316BA31DB` (`delivered_at`),
  KEY `IDX_7403A373F9D83E2` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draw_messenger__message`
--

LOCK TABLES `draw_messenger__message` WRITE;
/*!40000 ALTER TABLE `draw_messenger__message` DISABLE KEYS */;
/*!40000 ALTER TABLE `draw_messenger__message` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draw_messenger__message_tag`
--

DROP TABLE IF EXISTS `draw_messenger__message_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draw_messenger__message_tag` (
  `message_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`message_id`,`name`),
  KEY `IDX_9013A4D45E237E06` (`name`),
  KEY `IDX_9013A4D4537A1329` (`message_id`),
  CONSTRAINT `FK_9013A4D4537A1329` FOREIGN KEY (`message_id`) REFERENCES `draw_messenger__message` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draw_messenger__message_tag`
--

LOCK TABLES `draw_messenger__message_tag` WRITE;
/*!40000 ALTER TABLE `draw_messenger__message_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `draw_messenger__message_tag` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draw_user__user_lock`
--

DROP TABLE IF EXISTS `draw_user__user_lock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `draw_user__user_lock` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `lock_on` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expires_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `unlock_until` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_A86CF708A76ED395` (`user_id`),
  CONSTRAINT `FK_A86CF708A76ED395` FOREIGN KEY (`user_id`) REFERENCES `draw_acme__user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draw_user__user_lock`
--

LOCK TABLES `draw_user__user_lock` WRITE;
/*!40000 ALTER TABLE `draw_user__user_lock` DISABLE KEYS */;
/*!40000 ALTER TABLE `draw_user__user_lock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migration_versions`
--

DROP TABLE IF EXISTS `migration_versions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration_versions` (
  `version` varchar(191) COLLATE utf8_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migration_versions`
--

LOCK TABLES `migration_versions` WRITE;
/*!40000 ALTER TABLE `migration_versions` DISABLE KEYS */;
INSERT INTO `migration_versions` VALUES ('DoctrineMigrations\\Version20191021185639','2024-01-16 20:37:50',12),('DoctrineMigrations\\Version20191023185421','2024-01-16 20:37:50',6),('DoctrineMigrations\\Version20200326225539','2024-01-16 20:37:50',7),('DoctrineMigrations\\Version20200413155805','2024-01-16 20:37:50',49),('DoctrineMigrations\\Version20200415201648','2024-01-16 20:37:50',7),('DoctrineMigrations\\Version20200416133625','2024-01-16 20:37:50',12),('DoctrineMigrations\\Version20200416200350','2024-01-16 20:37:50',7),('DoctrineMigrations\\Version20200419202333','2024-01-16 20:37:50',29),('DoctrineMigrations\\Version20200420140820','2024-01-16 20:37:50',6),('DoctrineMigrations\\Version20200501195331','2024-01-16 20:37:50',8),('DoctrineMigrations\\Version20200520234726','2024-01-16 20:37:50',9),('DoctrineMigrations\\Version20211004163831','2024-01-16 20:37:50',9),('DoctrineMigrations\\Version20211108195729','2024-01-16 20:37:50',14),('DoctrineMigrations\\Version20211117171549','2024-01-16 20:37:50',8),('DoctrineMigrations\\Version20211118134701','2024-01-16 20:37:50',8),('DoctrineMigrations\\Version20211119134126','2024-01-16 20:37:50',7),('DoctrineMigrations\\Version20220212170050','2024-01-16 20:37:50',9),('DoctrineMigrations\\Version20220213152716','2024-01-16 20:37:50',16),('DoctrineMigrations\\Version20220218192808','2024-01-16 20:37:50',38),('DoctrineMigrations\\Version20220221151253','2024-01-16 20:37:50',18),('DoctrineMigrations\\Version20220221155732','2024-01-16 20:37:50',8),('DoctrineMigrations\\Version20220315160551','2024-01-16 20:37:50',8),('DoctrineMigrations\\Version20220620003049','2024-01-16 20:37:50',26),('DoctrineMigrations\\Version20220713230252','2024-01-16 20:37:50',10),('DoctrineMigrations\\Version20220714182910','2024-01-16 20:37:50',9),('DoctrineMigrations\\Version20220715165231','2024-01-16 20:37:50',26),('DoctrineMigrations\\Version20220715165615','2024-01-16 20:37:50',7),('DoctrineMigrations\\Version20221127004000','2024-01-16 20:37:50',85),('DoctrineMigrations\\Version20230521140305','2024-01-16 20:37:50',62),('DoctrineMigrations\\Version20230607233749','2024-01-16 20:37:50',7),('DoctrineMigrations\\Version20230615111619','2024-01-16 20:37:50',254),('DoctrineMigrations\\Version20230731181203','2024-01-16 20:37:50',54),('DoctrineMigrations\\Version20231023001449','2024-01-16 20:37:50',63),('DoctrineMigrations\\Version20231101173759','2024-01-16 20:37:51',8),('DoctrineMigrations\\Version20231218175905','2024-01-16 20:37:51',14);
/*!40000 ALTER TABLE `migration_versions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_migration`
--

DROP TABLE IF EXISTS `user_migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_migration` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `entity_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `migration_id` int NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `transition_logs` json DEFAULT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `entity_migration` (`entity_id`,`migration_id`),
  KEY `IDX_C3FC382681257D5D` (`entity_id`),
  KEY `IDX_C3FC382679D9816F` (`migration_id`),
  CONSTRAINT `FK_C3FC382679D9816F` FOREIGN KEY (`migration_id`) REFERENCES `draw_entity_migrator__migration` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_C3FC382681257D5D` FOREIGN KEY (`entity_id`) REFERENCES `draw_acme__user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_migration`
--

LOCK TABLES `user_migration` WRITE;
/*!40000 ALTER TABLE `user_migration` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_migration` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_tag`
--

DROP TABLE IF EXISTS `user_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_tag` (
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:guid)',
  `tag_id` bigint NOT NULL,
  PRIMARY KEY (`user_id`,`tag_id`),
  KEY `IDX_E89FD608A76ED395` (`user_id`),
  KEY `IDX_E89FD608BAD26311` (`tag_id`),
  CONSTRAINT `FK_E89FD608A76ED395` FOREIGN KEY (`user_id`) REFERENCES `draw_acme__user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_E89FD608BAD26311` FOREIGN KEY (`tag_id`) REFERENCES `draw_acme__tag` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_tag`
--

LOCK TABLES `user_tag` WRITE;
/*!40000 ALTER TABLE `user_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_tag` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-01-16 20:37:51
