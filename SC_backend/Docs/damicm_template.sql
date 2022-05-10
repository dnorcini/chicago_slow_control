-- MySQL dump 10.17  Distrib 10.3.17-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: damicm_template
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
-- Table structure for table `daq_control`
--

DROP TABLE IF EXISTS `daq_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daq_control` (
  `entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `lug_entry_id` int(11) NOT NULL,
  `utc` int(11) NOT NULL,
  `acquire_now` tinyint(1) NOT NULL,
  PRIMARY KEY (`entry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daq_control`
--

LOCK TABLES `daq_control` WRITE;
/*!40000 ALTER TABLE `daq_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `daq_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daq_control_tmp`
--

DROP TABLE IF EXISTS `daq_control_tmp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daq_control_tmp` (
  `utc` int(11) NOT NULL,
  `user_name` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `tag` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `acquire_now` tinyint(1) NOT NULL,
  `xml_settings_file` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `end_status` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  KEY `utc` (`utc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daq_control_tmp`
--

LOCK TABLES `daq_control_tmp` WRITE;
/*!40000 ALTER TABLE `daq_control_tmp` DISABLE KEYS */;
/*!40000 ALTER TABLE `daq_control_tmp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daq_presets`
--

DROP TABLE IF EXISTS `daq_presets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `daq_presets` (
  `entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `preset` mediumblob NOT NULL,
  PRIMARY KEY (`entry_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daq_presets`
--

LOCK TABLES `daq_presets` WRITE;
/*!40000 ALTER TABLE `daq_presets` DISABLE KEYS */;
/*!40000 ALTER TABLE `daq_presets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dqm_channel_data`
--

DROP TABLE IF EXISTS `dqm_channel_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dqm_channel_data` (
  `utc` int(11) NOT NULL,
  `runs` text COLLATE latin1_general_cs NOT NULL,
  `prefixes` text COLLATE latin1_general_cs NOT NULL,
  `dat_files` text COLLATE latin1_general_cs NOT NULL,
  `avg_event_rate` float NOT NULL,
  `avg_livetime` float NOT NULL,
  `pulse_rates` text COLLATE latin1_general_cs NOT NULL,
  `avg_pulse_lengths` text COLLATE latin1_general_cs NOT NULL,
  `avg_baselines_mv` text COLLATE latin1_general_cs NOT NULL,
  KEY `time` (`utc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dqm_channel_data`
--

LOCK TABLES `dqm_channel_data` WRITE;
/*!40000 ALTER TABLE `dqm_channel_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `dqm_channel_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dqm_control`
--

DROP TABLE IF EXISTS `dqm_control`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dqm_control` (
  `utc_time` int(11) NOT NULL,
  KEY `utc_time` (`utc_time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dqm_control`
--

LOCK TABLES `dqm_control` WRITE;
/*!40000 ALTER TABLE `dqm_control` DISABLE KEYS */;
/*!40000 ALTER TABLE `dqm_control` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dqm_event_data`
--

DROP TABLE IF EXISTS `dqm_event_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dqm_event_data` (
  `utc_time` int(11) NOT NULL,
  `run` int(11) NOT NULL,
  `prefix` text COLLATE latin1_general_cs NOT NULL,
  `event_rate` float NOT NULL,
  `livetime` float NOT NULL,
  `deadtime` float NOT NULL,
  KEY `utc_time` (`utc_time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dqm_event_data`
--

LOCK TABLES `dqm_event_data` WRITE;
/*!40000 ALTER TABLE `dqm_event_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `dqm_event_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dqm_event_display`
--

DROP TABLE IF EXISTS `dqm_event_display`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dqm_event_display` (
  `utc` int(11) NOT NULL,
  `picture` longblob NOT NULL,
  KEY `utc_time` (`utc`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dqm_event_display`
--

LOCK TABLES `dqm_event_display` WRITE;
/*!40000 ALTER TABLE `dqm_event_display` DISABLE KEYS */;
/*!40000 ALTER TABLE `dqm_event_display` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `globals`
--

LOCK TABLES `globals` WRITE;
/*!40000 ALTER TABLE `globals` DISABLE KEYS */;
INSERT INTO `globals` VALUES ('Master_alarm',0,0,0,0,0,0,0,0,'','','','paul09'),('Title',0,0,0,0,0,0,0,0,'Test Chamber 2',NULL,NULL,NULL),('have_TS',0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('have_RGA',0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('have_Cams',1,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('have_LB',1,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('web_bg_colour',0,0,0,0,0,0,0,0,'white',NULL,NULL,NULL),('web_text_colour',0,0,0,0,0,0,0,0,'black',NULL,NULL,NULL),('have_HV_crate',0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `globals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lug_categories`
--

DROP TABLE IF EXISTS `lug_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lug_categories` (
  `l_c_indx` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subcategory` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'General',
  PRIMARY KEY (`l_c_indx`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lug_categories`
--

LOCK TABLES `lug_categories` WRITE;
/*!40000 ALTER TABLE `lug_categories` DISABLE KEYS */;
INSERT INTO `lug_categories` VALUES (16,'DAQ','General'),(15,'Disk','General'),(3,'System','General'),(4,'System','TempControl'),(17,'DAQ','Calibration'),(8,'Calibrations','General'),(10,'Calibrations','Co60'),(18,'DAQ','Physics'),(12,'Calibrations','Co57'),(13,'Calibrations','Ba133');
/*!40000 ALTER TABLE `lug_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lug_entries`
--

DROP TABLE IF EXISTS `lug_entries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lug_entries` (
  `entry_id` int(11) NOT NULL AUTO_INCREMENT,
  `important_flag` tinyint(1) DEFAULT 0,
  `action_user` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `action_time` int(11) NOT NULL,
  `edit_user` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `edit_time` int(11) DEFAULT NULL,
  `run_number` int(11) NOT NULL,
  `category` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subcategory` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `entry_description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `entry_image` mediumblob DEFAULT NULL,
  `entry_image_thumb` blob DEFAULT NULL,
  `entry_file` mediumblob DEFAULT NULL,
  `filename` char(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `source` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `strikeme` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`entry_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lug_entries`
--

LOCK TABLES `lug_entries` WRITE;
/*!40000 ALTER TABLE `lug_entries` DISABLE KEYS */;
INSERT INTO `lug_entries` VALUES (13,0,'guest',1461523701,'james',1461523729,2,'Disk','General','This is also a thing that works.',NULL,NULL,NULL,NULL,NULL,0),(14,0,'dnorcini',1580833120,'dnorcini',1580833128,1,'Disk','General','Testing function.',NULL,NULL,NULL,NULL,NULL,0);
/*!40000 ALTER TABLE `lug_entries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msg_log`
--

DROP TABLE IF EXISTS `msg_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msg_log` (
  `msg_id` int(11) NOT NULL AUTO_INCREMENT,
  `time` int(11) NOT NULL,
  `ip_address` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `subsys` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `msgs` text COLLATE latin1_general_cs DEFAULT NULL,
  `type` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `is_error` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msg_log`
--

LOCK TABLES `msg_log` WRITE;
/*!40000 ALTER TABLE `msg_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `msg_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `msg_log_types`
--

DROP TABLE IF EXISTS `msg_log_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `msg_log_types` (
  `types` varchar(16) COLLATE latin1_general_cs NOT NULL,
  UNIQUE KEY `name` (`types`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msg_log_types`
--

LOCK TABLES `msg_log_types` WRITE;
/*!40000 ALTER TABLE `msg_log_types` DISABLE KEYS */;
INSERT INTO `msg_log_types` VALUES ('Alarm'),('Alert'),('Config.'),('Error'),('Setpoint'),('Shifts');
/*!40000 ALTER TABLE `msg_log_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `runs`
--

DROP TABLE IF EXISTS `runs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `runs` (
  `num` int(11) NOT NULL AUTO_INCREMENT,
  `start_t` int(11) NOT NULL,
  `end_t` int(11) NOT NULL DEFAULT 0,
  `file_path` varchar(128) COLLATE latin1_general_cs DEFAULT NULL,
  `file_root` varchar(128) COLLATE latin1_general_cs DEFAULT NULL,
  `note` char(128) COLLATE latin1_general_cs DEFAULT NULL,
  UNIQUE KEY `num_2` (`num`),
  KEY `num` (`num`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `runs`
--

LOCK TABLES `runs` WRITE;
/*!40000 ALTER TABLE `runs` DISABLE KEYS */;
INSERT INTO `runs` VALUES (1,1342019232,1457624232,'','','Setup');
/*!40000 ALTER TABLE `runs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_insts`
--

DROP TABLE IF EXISTS `sc_insts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_insts` (
  `name` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `description` varchar(64) COLLATE latin1_general_cs DEFAULT NULL,
  `subsys` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `run` tinyint(1) NOT NULL DEFAULT 0,
  `restart` tinyint(1) NOT NULL DEFAULT 0,
  `WD_ctrl` tinyint(1) NOT NULL DEFAULT 1,
  `path` varchar(256) COLLATE latin1_general_cs NOT NULL DEFAULT 'rel_path',
  `dev_type` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `dev_address` varchar(24) COLLATE latin1_general_cs DEFAULT NULL,
  `start_time` int(11) NOT NULL DEFAULT -1,
  `last_update_time` int(11) NOT NULL DEFAULT -1,
  `PID` int(11) NOT NULL DEFAULT -1,
  `user1` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `user2` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `parm1` double NOT NULL DEFAULT 0,
  `parm2` double NOT NULL DEFAULT 0,
  `notes` text COLLATE latin1_general_cs DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_insts`
--

LOCK TABLES `sc_insts` WRITE;
/*!40000 ALTER TABLE `sc_insts` DISABLE KEYS */;
INSERT INTO `sc_insts` VALUES ('alarm_trip_sys','Alarm trip monitor program','Alarm',1,0,1,'alarm_trip_system/alarm_trip_sys','daemon','',1628620203,1652193865,62983,'','',0,0,''),('alarm_alert_sys','Alarm alert monitor program','Alarm',1,0,1,'alarm_alert_system/alarm_alert_sys','daemon','',1628620203,1652193872,63012,'Alarm_Light','Alarm_Siren',0,0,''),('Watchdog','Overseeing watchdog program','Watchdog',1,0,0,'/home/damic/SC_backend/slow_control_code',NULL,NULL,1628620143,1652193863,62870,NULL,NULL,0,0,'This should always be running. If not, you must start it manually using the path above (i.e. /home/damic/SC_backend/slow_control_code/SC_Watchdog/SC_Watchdog). This requires that any old Watchdog processes were killed AND the web interface is not stuck on an old-issued PID (i.e. need to reset instrument if non-graceful shutdown). The Watchdog will start all other instruments/daemons automatically if their `run` flag is set.'),('APC3000','Monitor power consumption of UPS','Test Chamber 2',1,0,1,'APC3000/APC3000','modbus','192.168.1.191',1628620203,1652193855,62996,'502',NULL,0,0,NULL),('disk_free','Monitor free disk space','Sys',1,0,1,'disk_free/disk_free','daemon',NULL,1628620203,1652193854,62991,NULL,NULL,0,0,''),('LS336','Monitor chamber temperature and control heater','Test Chamber 2',1,0,1,'LS336_eth/LS336','ethernet','192.168.1.190',1629237507,1652193863,363517,'7777',NULL,0,0,NULL),('HiCube80','Monitor and run vacuum pump','Test Chamber 2',1,0,1,'HiCube80/HiCube80','ethernet','192.168.1.109',1650468559,1652193859,2429202,'5350',NULL,0,0,NULL),('AgilentE3646','Monitor and control power supply','Test Chamber 2',0,0,1,'AgilentE3646/AgilentE3646','ethernet','',-1,-1,-1,'',NULL,0,0,NULL),('Raritan_PX3','Monitor and control PDU','Test Chamber 2',1,0,1,'Raritan_PX3/Raritan_PX3','modbus','192.168.1.108',1628620203,1652193858,63002,'5502',NULL,0,0,NULL),('KeysightE36312A','Monitor and control power supply (multi-channel)','Test Chamber 2',1,0,1,'KeysightE36312A/KeysightE36312A','ethernet','192.168.1.107',1628620203,1652193849,63004,'5025',NULL,0,0,NULL),('CryoTelGT_AVC','Monitor and controll CryoTel GT with AVC','Test Chamber 2',1,0,1,'CryoTelGT_AVC/CryoTelGT_AVC','ethernet','192.168.1.100',1631570852,1652193866,1562152,'100',NULL,0,0,NULL),('SRSDC205','Monitor Voltage on SRS Power Supply','Test Chamber 2',1,0,1,'SRSDC205/SRSDC205','ethernet','192.168.1.103',1628620203,1652193707,63010,'100',NULL,0,0,NULL),('CenterOne','Monitor CenterOne controller + pressure gauge','Test Chamber 2',1,0,1,'CenterThree/CenterThree','ethernet','192.168.1.105',1650483264,1652193858,2431735,'8000',NULL,0,0,NULL);
/*!40000 ALTER TABLE `sc_insts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Battery_Charge`
--

DROP TABLE IF EXISTS `sc_sens_Battery_Charge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Battery_Charge` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Battery_Charge`
--

LOCK TABLES `sc_sens_Battery_Charge` WRITE;
/*!40000 ALTER TABLE `sc_sens_Battery_Charge` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Battery_Charge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Chamber_pressure`
--

DROP TABLE IF EXISTS `sc_sens_Chamber_pressure`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Chamber_pressure` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Chamber_pressure`
--

LOCK TABLES `sc_sens_Chamber_pressure` WRITE;
/*!40000 ALTER TABLE `sc_sens_Chamber_pressure` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Chamber_pressure` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Current_1`
--

DROP TABLE IF EXISTS `sc_sens_Current_1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Current_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Current_1`
--

LOCK TABLES `sc_sens_Current_1` WRITE;
/*!40000 ALTER TABLE `sc_sens_Current_1` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Current_1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Current_2`
--

DROP TABLE IF EXISTS `sc_sens_Current_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Current_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Current_2`
--

LOCK TABLES `sc_sens_Current_2` WRITE;
/*!40000 ALTER TABLE `sc_sens_Current_2` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Current_2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Current_3`
--

DROP TABLE IF EXISTS `sc_sens_Current_3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Current_3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Current_3`
--

LOCK TABLES `sc_sens_Current_3` WRITE;
/*!40000 ALTER TABLE `sc_sens_Current_3` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Current_3` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_DAQ_ctrl`
--

DROP TABLE IF EXISTS `sc_sens_DAQ_ctrl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_DAQ_ctrl` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_DAQ_ctrl`
--

LOCK TABLES `sc_sens_DAQ_ctrl` WRITE;
/*!40000 ALTER TABLE `sc_sens_DAQ_ctrl` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_DAQ_ctrl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Heater_B`
--

DROP TABLE IF EXISTS `sc_sens_Heater_B`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Heater_B` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Heater_B`
--

LOCK TABLES `sc_sens_Heater_B` WRITE;
/*!40000 ALTER TABLE `sc_sens_Heater_B` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Heater_B` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Heater_B_OffOn`
--

DROP TABLE IF EXISTS `sc_sens_Heater_B_OffOn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Heater_B_OffOn` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Heater_B_OffOn`
--

LOCK TABLES `sc_sens_Heater_B_OffOn` WRITE;
/*!40000 ALTER TABLE `sc_sens_Heater_B_OffOn` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Heater_B_OffOn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Inlet_Current`
--

DROP TABLE IF EXISTS `sc_sens_Inlet_Current`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Inlet_Current` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Inlet_Current`
--

LOCK TABLES `sc_sens_Inlet_Current` WRITE;
/*!40000 ALTER TABLE `sc_sens_Inlet_Current` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Inlet_Current` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Inlet_Power`
--

DROP TABLE IF EXISTS `sc_sens_Inlet_Power`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Inlet_Power` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Inlet_Power`
--

LOCK TABLES `sc_sens_Inlet_Power` WRITE;
/*!40000 ALTER TABLE `sc_sens_Inlet_Power` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Inlet_Power` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_PDU_7_OnOff`
--

DROP TABLE IF EXISTS `sc_sens_PDU_7_OnOff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_PDU_7_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_PDU_7_OnOff`
--

LOCK TABLES `sc_sens_PDU_7_OnOff` WRITE;
/*!40000 ALTER TABLE `sc_sens_PDU_7_OnOff` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_PDU_7_OnOff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_PDU_8_OnOff`
--

DROP TABLE IF EXISTS `sc_sens_PDU_8_OnOff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_PDU_8_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_PDU_8_OnOff`
--

LOCK TABLES `sc_sens_PDU_8_OnOff` WRITE;
/*!40000 ALTER TABLE `sc_sens_PDU_8_OnOff` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_PDU_8_OnOff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Power_Output`
--

DROP TABLE IF EXISTS `sc_sens_Power_Output`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Power_Output` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Power_Output`
--

LOCK TABLES `sc_sens_Power_Output` WRITE;
/*!40000 ALTER TABLE `sc_sens_Power_Output` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Power_Output` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Power_Output_AVC`
--

DROP TABLE IF EXISTS `sc_sens_Power_Output_AVC`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Power_Output_AVC` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Power_Output_AVC`
--

LOCK TABLES `sc_sens_Power_Output_AVC` WRITE;
/*!40000 ALTER TABLE `sc_sens_Power_Output_AVC` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Power_Output_AVC` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Pump_OffOn`
--

DROP TABLE IF EXISTS `sc_sens_Pump_OffOn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Pump_OffOn` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Pump_OffOn`
--

LOCK TABLES `sc_sens_Pump_OffOn` WRITE;
/*!40000 ALTER TABLE `sc_sens_Pump_OffOn` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Pump_OffOn` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Ramprate_B`
--

DROP TABLE IF EXISTS `sc_sens_Ramprate_B`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Ramprate_B` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Ramprate_B`
--

LOCK TABLES `sc_sens_Ramprate_B` WRITE;
/*!40000 ALTER TABLE `sc_sens_Ramprate_B` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Ramprate_B` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Reject_Temp`
--

DROP TABLE IF EXISTS `sc_sens_Reject_Temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Reject_Temp` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Reject_Temp`
--

LOCK TABLES `sc_sens_Reject_Temp` WRITE;
/*!40000 ALTER TABLE `sc_sens_Reject_Temp` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Reject_Temp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Rotation_Speed`
--

DROP TABLE IF EXISTS `sc_sens_Rotation_Speed`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Rotation_Speed` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Rotation_Speed`
--

LOCK TABLES `sc_sens_Rotation_Speed` WRITE;
/*!40000 ALTER TABLE `sc_sens_Rotation_Speed` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Rotation_Speed` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Runtime`
--

DROP TABLE IF EXISTS `sc_sens_Runtime`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Runtime` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Runtime`
--

LOCK TABLES `sc_sens_Runtime` WRITE;
/*!40000 ALTER TABLE `sc_sens_Runtime` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Runtime` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_SRS_VOLT`
--

DROP TABLE IF EXISTS `sc_sens_SRS_VOLT`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_SRS_VOLT` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_SRS_VOLT`
--

LOCK TABLES `sc_sens_SRS_VOLT` WRITE;
/*!40000 ALTER TABLE `sc_sens_SRS_VOLT` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_SRS_VOLT` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_SRS_VOLT_ONOFF`
--

DROP TABLE IF EXISTS `sc_sens_SRS_VOLT_ONOFF`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_SRS_VOLT_ONOFF` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_SRS_VOLT_ONOFF`
--

LOCK TABLES `sc_sens_SRS_VOLT_ONOFF` WRITE;
/*!40000 ALTER TABLE `sc_sens_SRS_VOLT_ONOFF` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_SRS_VOLT_ONOFF` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Set_Cooler`
--

DROP TABLE IF EXISTS `sc_sens_Set_Cooler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Set_Cooler` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Set_Cooler`
--

LOCK TABLES `sc_sens_Set_Cooler` WRITE;
/*!40000 ALTER TABLE `sc_sens_Set_Cooler` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Set_Cooler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Set_Heater_B`
--

DROP TABLE IF EXISTS `sc_sens_Set_Heater_B`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Set_Heater_B` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Set_Heater_B`
--

LOCK TABLES `sc_sens_Set_Heater_B` WRITE;
/*!40000 ALTER TABLE `sc_sens_Set_Heater_B` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Set_Heater_B` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Set_Power_Output`
--

DROP TABLE IF EXISTS `sc_sens_Set_Power_Output`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Set_Power_Output` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Set_Power_Output`
--

LOCK TABLES `sc_sens_Set_Power_Output` WRITE;
/*!40000 ALTER TABLE `sc_sens_Set_Power_Output` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Set_Power_Output` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Set_Ramprate_B`
--

DROP TABLE IF EXISTS `sc_sens_Set_Ramprate_B`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Set_Ramprate_B` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Set_Ramprate_B`
--

LOCK TABLES `sc_sens_Set_Ramprate_B` WRITE;
/*!40000 ALTER TABLE `sc_sens_Set_Ramprate_B` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Set_Ramprate_B` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Set_Target_Temp`
--

DROP TABLE IF EXISTS `sc_sens_Set_Target_Temp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Set_Target_Temp` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Set_Target_Temp`
--

LOCK TABLES `sc_sens_Set_Target_Temp` WRITE;
/*!40000 ALTER TABLE `sc_sens_Set_Target_Temp` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Set_Target_Temp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Set_Voltage_1`
--

DROP TABLE IF EXISTS `sc_sens_Set_Voltage_1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Set_Voltage_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Set_Voltage_1`
--

LOCK TABLES `sc_sens_Set_Voltage_1` WRITE;
/*!40000 ALTER TABLE `sc_sens_Set_Voltage_1` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Set_Voltage_1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Set_Voltage_2`
--

DROP TABLE IF EXISTS `sc_sens_Set_Voltage_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Set_Voltage_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Set_Voltage_2`
--

LOCK TABLES `sc_sens_Set_Voltage_2` WRITE;
/*!40000 ALTER TABLE `sc_sens_Set_Voltage_2` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Set_Voltage_2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Set_Voltage_3`
--

DROP TABLE IF EXISTS `sc_sens_Set_Voltage_3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Set_Voltage_3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Set_Voltage_3`
--

LOCK TABLES `sc_sens_Set_Voltage_3` WRITE;
/*!40000 ALTER TABLE `sc_sens_Set_Voltage_3` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Set_Voltage_3` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Setpoint_B`
--

DROP TABLE IF EXISTS `sc_sens_Setpoint_B`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Setpoint_B` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Setpoint_B`
--

LOCK TABLES `sc_sens_Setpoint_B` WRITE;
/*!40000 ALTER TABLE `sc_sens_Setpoint_B` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Setpoint_B` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Temp_B`
--

DROP TABLE IF EXISTS `sc_sens_Temp_B`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Temp_B` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Temp_B`
--

LOCK TABLES `sc_sens_Temp_B` WRITE;
/*!40000 ALTER TABLE `sc_sens_Temp_B` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Temp_B` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Temp_C`
--

DROP TABLE IF EXISTS `sc_sens_Temp_C`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Temp_C` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Temp_C`
--

LOCK TABLES `sc_sens_Temp_C` WRITE;
/*!40000 ALTER TABLE `sc_sens_Temp_C` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Temp_C` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Temp_ColdHead`
--

DROP TABLE IF EXISTS `sc_sens_Temp_ColdHead`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Temp_ColdHead` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Temp_ColdHead`
--

LOCK TABLES `sc_sens_Temp_ColdHead` WRITE;
/*!40000 ALTER TABLE `sc_sens_Temp_ColdHead` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Temp_ColdHead` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Temp_Target`
--

DROP TABLE IF EXISTS `sc_sens_Temp_Target`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Temp_Target` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Temp_Target`
--

LOCK TABLES `sc_sens_Temp_Target` WRITE;
/*!40000 ALTER TABLE `sc_sens_Temp_Target` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Temp_Target` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Turbo_OnOff`
--

DROP TABLE IF EXISTS `sc_sens_Turbo_OnOff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Turbo_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Turbo_OnOff`
--

LOCK TABLES `sc_sens_Turbo_OnOff` WRITE;
/*!40000 ALTER TABLE `sc_sens_Turbo_OnOff` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Turbo_OnOff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Voltage_1`
--

DROP TABLE IF EXISTS `sc_sens_Voltage_1`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Voltage_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Voltage_1`
--

LOCK TABLES `sc_sens_Voltage_1` WRITE;
/*!40000 ALTER TABLE `sc_sens_Voltage_1` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Voltage_1` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Voltage_1_OnOff`
--

DROP TABLE IF EXISTS `sc_sens_Voltage_1_OnOff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Voltage_1_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Voltage_1_OnOff`
--

LOCK TABLES `sc_sens_Voltage_1_OnOff` WRITE;
/*!40000 ALTER TABLE `sc_sens_Voltage_1_OnOff` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Voltage_1_OnOff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Voltage_2`
--

DROP TABLE IF EXISTS `sc_sens_Voltage_2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Voltage_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Voltage_2`
--

LOCK TABLES `sc_sens_Voltage_2` WRITE;
/*!40000 ALTER TABLE `sc_sens_Voltage_2` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Voltage_2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Voltage_3`
--

DROP TABLE IF EXISTS `sc_sens_Voltage_3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Voltage_3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Voltage_3`
--

LOCK TABLES `sc_sens_Voltage_3` WRITE;
/*!40000 ALTER TABLE `sc_sens_Voltage_3` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Voltage_3` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Voltage_3_OnOff`
--

DROP TABLE IF EXISTS `sc_sens_Voltage_3_OnOff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Voltage_3_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Voltage_3_OnOff`
--

LOCK TABLES `sc_sens_Voltage_3_OnOff` WRITE;
/*!40000 ALTER TABLE `sc_sens_Voltage_3_OnOff` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Voltage_3_OnOff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_dsk_data`
--

DROP TABLE IF EXISTS `sc_sens_dsk_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_dsk_data` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_dsk_data`
--

LOCK TABLES `sc_sens_dsk_data` WRITE;
/*!40000 ALTER TABLE `sc_sens_dsk_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_dsk_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_dsk_root`
--

DROP TABLE IF EXISTS `sc_sens_dsk_root`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_dsk_root` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_dsk_root`
--

LOCK TABLES `sc_sens_dsk_root` WRITE;
/*!40000 ALTER TABLE `sc_sens_dsk_root` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_dsk_root` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_dsk_shm`
--

DROP TABLE IF EXISTS `sc_sens_dsk_shm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_dsk_shm` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_dsk_shm`
--

LOCK TABLES `sc_sens_dsk_shm` WRITE;
/*!40000 ALTER TABLE `sc_sens_dsk_shm` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_dsk_shm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sensor_types`
--

DROP TABLE IF EXISTS `sc_sensor_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sensor_types` (
  `num` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `name` (`name`),
  KEY `num` (`num`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sensor_types`
--

LOCK TABLES `sc_sensor_types` WRITE;
/*!40000 ALTER TABLE `sc_sensor_types` DISABLE KEYS */;
INSERT INTO `sc_sensor_types` VALUES (2,'Sys'),(3,'Temperature'),(12,'Pressure'),(9,'UPS'),(10,'PowerSupply'),(11,'PDU'),(13,'CryoCooler'),(14,'Vsub');
/*!40000 ALTER TABLE `sc_sensor_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sensors`
--

DROP TABLE IF EXISTS `sc_sensors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sensors` (
  `name` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `description` varchar(64) COLLATE latin1_general_cs DEFAULT NULL,
  `type` varchar(16) COLLATE latin1_general_cs NOT NULL DEFAULT 'unknown',
  `subtype` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `ctrl_priv` varchar(128) COLLATE latin1_general_cs NOT NULL DEFAULT 'full',
  `num` int(11) NOT NULL DEFAULT 0,
  `instrument` varchar(16) COLLATE latin1_general_cs NOT NULL DEFAULT 'inst_name',
  `units` varchar(16) COLLATE latin1_general_cs NOT NULL DEFAULT 'units',
  `discrete_vals` varchar(128) COLLATE latin1_general_cs DEFAULT NULL,
  `al_set_val_low` double NOT NULL DEFAULT 0,
  `al_set_val_high` double NOT NULL DEFAULT 0,
  `al_arm_val_low` tinyint(1) NOT NULL DEFAULT 0,
  `al_arm_val_high` tinyint(1) NOT NULL DEFAULT 0,
  `al_set_rate_low` double NOT NULL DEFAULT 0,
  `al_set_rate_high` double NOT NULL DEFAULT 0,
  `al_arm_rate_low` tinyint(1) NOT NULL DEFAULT 0,
  `al_arm_rate_high` tinyint(1) NOT NULL DEFAULT 0,
  `alarm_tripped` tinyint(1) NOT NULL DEFAULT 0,
  `grace` int(11) NOT NULL DEFAULT 0,
  `last_trip` int(11) NOT NULL DEFAULT -1,
  `settable` tinyint(1) NOT NULL DEFAULT 0,
  `show_rate` tinyint(1) NOT NULL DEFAULT 0,
  `hide_sensor` tinyint(1) NOT NULL DEFAULT 0,
  `update_period` int(11) NOT NULL DEFAULT 60,
  `num_format` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `user1` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `user2` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `user3` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `user4` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `parm1` double NOT NULL DEFAULT 0,
  `parm2` double NOT NULL DEFAULT 0,
  `parm3` double NOT NULL DEFAULT 0,
  `parm4` double NOT NULL DEFAULT 0,
  `notes` text COLLATE latin1_general_cs DEFAULT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sensors`
--

LOCK TABLES `sc_sensors` WRITE;
/*!40000 ALTER TABLE `sc_sensors` DISABLE KEYS */;
INSERT INTO `sc_sensors` VALUES ('dsk_data','Free space on /data','2','disk_free','full',0,'disk_free','%',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,1,60,NULL,'/data',NULL,NULL,NULL,0,0,0,0,NULL),('dsk_shm','Free space on /dev/shm','2','disk','full',0,'disk_free','%',NULL,50,99,1,0,0,0,0,0,1,0,1648830589,0,0,0,60,NULL,'/dev/shm',NULL,NULL,NULL,0,0,0,0,''),('dsk_root','Free space on /','2','disk','full',0,'disk_free','%',NULL,30,70,1,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,'/',NULL,NULL,NULL,0,0,0,0,''),('Set_Heater_B','Setpoint Heater B','3','Setsetpoint','full',1,'LS336','K',NULL,0,0,0,0,0,0,0,0,0,0,-1,1,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Set_Target_Temp','Set target temperature (control mode)','13','setttarget','full',0,'CryoTelGT_AVC','K',NULL,0,0,0,0,0,0,0,0,0,0,-1,1,0,1,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Heater_B','Power Heater B','3','Heater','full',1,'LS336','%',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Temp_ColdHead','Temperature of Cold Head','13','tc','full',0,'CryoTelGT_AVC','K',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Set_Ramprate_B','Ramprate Heater B','3','Setramprate','full',1,'LS336','K/min',NULL,0,0,0,0,0,0,0,0,0,0,-1,1,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Temp_B','Temperature Input B','3','Temp','full',1,'LS336','K',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Set_Cooler','Set the control mode of AVC','13','setcooler','full',0,'CryoTelGT_AVC','discrete','0:2;Off:Power',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('SRS_VOLT_ONOFF','Monitor voltage on/off of SRS for Vsub','14','onoff','full',0,'SRSDC205','discrete_vals','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Battery_Charge','Battery Charge of UPS','9','battcharge','full',0,'APC3000','%',NULL,50,0,1,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Inlet_Power','Monitor active power of PDU inlet','11','power','full',0,'Raritan_PX3','W',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Power_Output','Power load of UPS','9','apppower','full',0,'APC3000','%',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Rotation_Speed','Rotation Speed of Pump','12','actualspd','full',0,'HiCube80','rpm',NULL,0,91000,0,1,0,0,0,0,0,0,-1,0,0,0,30,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('PDU_7_OnOff','Set PDU Outlet 7 On/Off (CryoTel)','11','outlet','full',7,'Raritan_PX3','discrete','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Runtime','Apprx. runtime of UPS','9','runtime','full',0,'APC3000','hours',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Ramprate_B','Ramprate Heater B','3','Ramprate','full',1,'LS336','K/min',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Power_Output_AVC','Power output measured by AVC','13','p','full',0,'CryoTelGT_AVC','W',NULL,68,0,1,0,0,0,0,0,0,0,-1,0,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Setpoint_B','Setpoint Heater B','3','Setpoint','full',1,'LS336','K',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Temp_Target','Target temperature (control mode)','13','ttarget','full',0,'CryoTelGT_AVC','K',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,1,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Heater_B_OffOn','Set Heater B on LS336 on/off','3','Heateronoff','full',1,'LS336','discrete','0:1:3;Off:Low:High',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Turbo_OnOff','Set Turbo Motor to HiCube Flag','12','motorpump','full',0,'HiCube80','discrete','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Pump_OffOn','Set Pump to HiCube On/Off','12','pumpgstatn','full',0,'HiCube80','discrete','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('PDU_8_OnOff','Set PDU Outlet 8 On/Off (CenterOne)','11','outlet','full',8,'Raritan_PX3','discrete','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Inlet_Current','Monitor rms current of PDU inlet','11','current','full',0,'Raritan_PX3','A',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Chamber_pressure','Pressure of chamber','12','pressure','full',1,'CenterOne','mbar',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,30,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Set_Power_Output','Set power output controlled by AVC','13','setpwout','full',0,'CryoTelGT_AVC','W',NULL,0,0,0,0,0,0,0,0,0,0,-1,1,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Temp_C','Temperature Input C','3','Temp','full',3,'LS336','K',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Reject_Temp','Reject Temperature of Cryotel','13','reject','full',0,'CryoTelGT_AVC','C',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,5,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('SRS_VOLT','Monitor input voltage of SRS for Vsub','14','volt','full',0,'SRSDC205','V',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Current_1','Current on Amplifer+ board','10','current','full',1,'KeysightE36312A','A',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Current_2','Current on Amplifer- board','10','current','full',2,'KeysightE36312A','A',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Current_3','Current on Leach board','10','current','full',3,'KeysightE36312A','A',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Set_Voltage_1','Set voltage on Amplifier+ board','10','setvolt','full',1,'KeysightE36312A','V',NULL,0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Set_Voltage_2','Set voltage on Amplifier- board','10','setvolt','full',2,'KeysightE36312A','V',NULL,0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Set_Voltage_3','Set voltage on Leach board','10','setvolt','full',3,'KeysightE36312A','V',NULL,0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Voltage_1','Voltage on Amplifer+ board','10','volt','full',1,'KeysightE36312A','V',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Voltage_2','Voltage on Amplifer- board','10','volt','full',2,'KeysightE36312A','V',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Voltage_3','Voltage on Leach board','10','volt','full',3,'KeysightE36312A','V',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Voltage_1_OnOff','Set voltage on Amplifer board On/Off','10','setonoff','full',2,'KeysightE36312A','discrete','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Voltage_3_OnOff','Set voltage on Leach board On/Off','10','setonoff','full',3,'KeysightE36312A','discrete','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL);
/*!40000 ALTER TABLE `sc_sensors` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_privileges`
--

LOCK TABLES `user_privileges` WRITE;
/*!40000 ALTER TABLE `user_privileges` DISABLE KEYS */;
INSERT INTO `user_privileges` VALUES (1,'guest','all'),(2,'basic','all'),(3,'full','all'),(4,'admin','all'),(5,'config','all');
/*!40000 ALTER TABLE `user_privileges` ENABLE KEYS */;
UNLOCK TABLES;

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
-- Dumping data for table `user_shift_status`
--

LOCK TABLES `user_shift_status` WRITE;
/*!40000 ALTER TABLE `user_shift_status` DISABLE KEYS */;
INSERT INTO `user_shift_status` VALUES ('Off',0),('Shift Leader',1),('Shift Manager',1),('On Shift',0),('System Manager',1);
/*!40000 ALTER TABLE `user_shift_status` ENABLE KEYS */;
UNLOCK TABLES;

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
  `sms` varchar(32) COLLATE latin1_general_cs DEFAULT NULL,
  `phone` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `on_call` tinyint(1) NOT NULL DEFAULT 0,
  `shift_status` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `privileges` text COLLATE latin1_general_cs DEFAULT NULL,
  UNIQUE KEY `username` (`user_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('guest','084e0343a0486ff05530df6c705c8bb4','guest','none','','','',0,'Off','guest,basic'),('root','63a9f0ea7bb98050796b649e85481845','root','root','root','root','root',0,'Off','admin,basic,config,full,lug,siren'),('dnorcini','30d3016ed2b8e3e62f1cdb9b124a1347','Danielle Norcini','UChicago','dnorcini@kicp.uchicago.edu','6105059248@tmomail.net','610 505 9248',1,'System Manager','admin,basic,config,full');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-05-10 10:02:26
