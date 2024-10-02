-- MySQL dump 10.17  Distrib 10.3.17-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: ccd_qc
-- ------------------------------------------------------
-- Server version	10.3.17-MariaDB

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
-- Table structure for table `CCD`
--

DROP TABLE IF EXISTS `CCD`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `CCD` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` text COLLATE latin1_general_cs DEFAULT NULL,
  `CCD_Type` text COLLATE latin1_general_cs DEFAULT NULL,
  `Size` text COLLATE latin1_general_cs DEFAULT NULL,
  `Status` text COLLATE latin1_general_cs DEFAULT NULL,
  `Packager` text COLLATE latin1_general_cs DEFAULT NULL,
  `Location` text COLLATE latin1_general_cs DEFAULT NULL,
  `Packaging_date` text COLLATE latin1_general_cs DEFAULT NULL,
  `Note` longtext COLLATE latin1_general_cs DEFAULT NULL,
  `Last_update` int(11) NOT NULL DEFAULT -1,
  `Noise_U1` decimal(10,2) DEFAULT 0.00,
  `Noise_L1` decimal(10,2) DEFAULT 0.00,
  `Noise_U2` decimal(10,2) DEFAULT 0.00,
  `Noise_L2` decimal(10,2) DEFAULT 0.00,
  `Resolution_U1` decimal(10,2) DEFAULT 0.00,
  `Resolution_L1` decimal(10,2) DEFAULT 0.00,
  `Resolution_U2` decimal(10,2) DEFAULT 0.00,
  `Resolution_L2` decimal(10,2) DEFAULT 0.00,
  `Gain_U1` decimal(10,2) DEFAULT 0.00,
  `Gain_L1` decimal(10,2) DEFAULT 0.00,
  `Gain_U2` decimal(10,2) DEFAULT 0.00,
  `Gain_L2` decimal(10,2) DEFAULT 0.00,
  `Dark_current_U1` decimal(10,2) DEFAULT 0.00,
  `Dark_current_L1` decimal(10,2) DEFAULT 0.00,
  `Dark_current_U2` decimal(10,2) DEFAULT 0.00,
  `Dark_current_L2` decimal(10,2) DEFAULT 0.00,
  `Eff_resistivity` decimal(10,2) DEFAULT 0.00,
  `Test_temp` decimal(10,2) DEFAULT 0.00,
  `Test_vref` decimal(10,2) DEFAULT 0.00,
  `Defects` text COLLATE latin1_general_cs DEFAULT NULL,
  `Wafer_ID` text COLLATE latin1_general_cs DEFAULT NULL,
  `Wafer_position` text COLLATE latin1_general_cs DEFAULT NULL,
  `Production_date` text COLLATE latin1_general_cs DEFAULT NULL,
  `Glue_humid` decimal(10,2) DEFAULT 0.00,
  `Glue_temp` decimal(10,2) DEFAULT 0.00,
  `Glue_radon` decimal(10,2) DEFAULT 0.00,
  `Wb_humid` decimal(10,2) DEFAULT 0.00,
  `Wb_temp` decimal(10,2) DEFAULT 0.00,
  `Wb_radon` decimal(10,2) DEFAULT 0.00,
  `Wb_power` decimal(10,2) DEFAULT 0.00,
  `Wb_time` decimal(10,2) DEFAULT 0.00,
  `Wb_date` text COLLATE latin1_general_cs DEFAULT NULL,
  `Cable_np` tinyint(1) NOT NULL DEFAULT 0,
  `JFET_U1` tinyint(1) NOT NULL DEFAULT 0,
  `JFET_L1` tinyint(1) NOT NULL DEFAULT 0,
  `JFET_U2` tinyint(1) NOT NULL DEFAULT 0,
  `JFET_L2` tinyint(1) NOT NULL DEFAULT 0,
  `AMP_U1` tinyint(1) NOT NULL DEFAULT 0,
  `AMP_L1` tinyint(1) NOT NULL DEFAULT 0,
  `AMP_U2` tinyint(1) NOT NULL DEFAULT 0,
  `AMP_L2` tinyint(1) NOT NULL DEFAULT 0,
  `Gluing_details` text COLLATE latin1_general_cs DEFAULT NULL,
  `Wirebonding_details` text COLLATE latin1_general_cs DEFAULT NULL,
  `Tester` text COLLATE latin1_general_cs DEFAULT NULL,
  `Test_date` text COLLATE latin1_general_cs DEFAULT NULL,
  `Test_details` text COLLATE latin1_general_cs DEFAULT NULL,
  UNIQUE KEY `num_2` (`ID`),
  KEY `num` (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=148 DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `globals`
--

DROP TABLE IF EXISTS `globals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `globals` (
  `name` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `int1` int(11) NOT NULL DEFAULT 0,
  `int2` int(11) NOT NULL DEFAULT 0,
  `int3` int(11) NOT NULL DEFAULT 0,
  `int4` int(11) NOT NULL DEFAULT 0,
  `double1` double NOT NULL DEFAULT 0,
  `double2` double NOT NULL DEFAULT 0,
  `double3` double NOT NULL DEFAULT 0,
  `double4` double NOT NULL DEFAULT 0,
  `string1` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `string2` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `string3` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `string4` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  UNIQUE KEY `names` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_privileges`
--

DROP TABLE IF EXISTS `user_privileges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_privileges` (
  `u_p_indx` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `allowed_host` varchar(16) COLLATE latin1_general_cs NOT NULL DEFAULT 'all',
  UNIQUE KEY `index` (`u_p_indx`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_shift_status`
--

DROP TABLE IF EXISTS `user_shift_status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_shift_status` (
  `status` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `can_manage` tinyint(1) NOT NULL DEFAULT 0,
  UNIQUE KEY `status` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_name` varchar(32) COLLATE latin1_general_cs NOT NULL,
  `password` char(32) COLLATE latin1_general_cs DEFAULT NULL,
  `full_name` varchar(32) COLLATE latin1_general_cs DEFAULT NULL,
  `affiliation` varchar(64) COLLATE latin1_general_cs DEFAULT NULL,
  `email` varchar(32) COLLATE latin1_general_cs DEFAULT NULL,
  `privileges` text COLLATE latin1_general_cs DEFAULT NULL,
  `shift_status` text COLLATE latin1_general_cs DEFAULT NULL,
  UNIQUE KEY `username` (`user_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-10-02 12:01:56
