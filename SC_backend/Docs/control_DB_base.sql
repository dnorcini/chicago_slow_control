-- MySQL dump 10.15  Distrib 10.0.25-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: ASC_control
-- ------------------------------------------------------
-- Server version	10.0.25-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
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
  `int1` int(11) NOT NULL DEFAULT '0',
  `int2` int(11) NOT NULL DEFAULT '0',
  `int3` int(11) NOT NULL DEFAULT '0',
  `int4` int(11) NOT NULL DEFAULT '0',
  `double1` double NOT NULL DEFAULT '0',
  `double2` double NOT NULL DEFAULT '0',
  `double3` double NOT NULL DEFAULT '0',
  `double4` double NOT NULL DEFAULT '0',
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
INSERT INTO `globals` VALUES ('Master_alarm',0,0,0,0,0,0,0,0,'','','',''),('Title',0,0,0,0,0,0,0,0,'Slow Control Sys',NULL,NULL,NULL),('have_TS',0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('have_RGA',0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('have_Cams',1,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('have_LB',1,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('web_bg_colour',0,0,0,0,0,0,0,0,'wheat',NULL,NULL,NULL),('web_text_colour',0,0,0,0,0,0,0,0,'navy',NULL,NULL,NULL),('have_HV_crate',0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL);
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
  `important_flag` tinyint(1) DEFAULT '0',
  `action_user` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `action_time` int(11) NOT NULL,
  `edit_user` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `edit_time` int(11) DEFAULT NULL,
  `run_number` int(11) NOT NULL,
  `category` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subcategory` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `entry_description` longtext COLLATE utf8_unicode_ci NOT NULL,
  `entry_image` mediumblob,
  `entry_image_thumb` blob,
  `entry_file` mediumblob,
  `filename` char(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `source` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `strikeme` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`entry_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lug_entries`
--

LOCK TABLES `lug_entries` WRITE;
/*!40000 ALTER TABLE `lug_entries` DISABLE KEYS */;
INSERT INTO `lug_entries` VALUES (12,0,'james',1461523662,'james',1461523690,1,'System','General','Test of the log book system.',NULL,NULL,NULL,NULL,NULL,0),(13,0,'guest',1461523701,'james',1461523729,2,'Disk','General','This is also a thing that works.',NULL,NULL,NULL,NULL,NULL,0);
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
  `msgs` text COLLATE latin1_general_cs,
  `type` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `is_error` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`msg_id`)
) ENGINE=MyISAM AUTO_INCREMENT=83 DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `msg_log`
--

LOCK TABLES `msg_log` WRITE;
/*!40000 ALTER TABLE `msg_log` DISABLE KEYS */;
INSERT INTO `msg_log` VALUES (1,1457553874,NULL,NULL,'Shift status of james changed to System Manager by root.','Shifts',0);
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
  `end_t` int(11) NOT NULL DEFAULT '0',
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
  `run` tinyint(1) NOT NULL DEFAULT '0',
  `restart` tinyint(1) NOT NULL DEFAULT '0',
  `WD_ctrl` tinyint(1) NOT NULL DEFAULT '1',
  `path` varchar(256) COLLATE latin1_general_cs NOT NULL DEFAULT 'rel_path',
  `dev_type` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `dev_address` varchar(24) COLLATE latin1_general_cs DEFAULT NULL,
  `start_time` int(11) NOT NULL DEFAULT '-1',
  `last_update_time` int(11) NOT NULL DEFAULT '-1',
  `PID` int(11) NOT NULL DEFAULT '-1',
  `user1` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `user2` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `parm1` double NOT NULL DEFAULT '0',
  `parm2` double NOT NULL DEFAULT '0',
  `notes` text COLLATE latin1_general_cs,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_insts`
--

LOCK TABLES `sc_insts` WRITE;
/*!40000 ALTER TABLE `sc_insts` DISABLE KEYS */;
INSERT INTO `sc_insts` VALUES ('alarm_trip_sys','Alarm trip monitor program','Alarm',0,0,1,'alarm_trip_system/alarm_trip_sys','daemon','',-1,-1,-1,'','',0,0,''),('alarm_alert_sys','Alarm alert monitor program','Alarm',0,0,1,'alarm_alert_system/alarm_alert_sys','daemon','',-1,-1,-1,'Alarm_Light','Alarm_Siren',0,0,''),('Watchdog','Overseeing watchdog program','Watchdog',1,0,0,'/home/james/code/astro-slow-control/SC_backend/slow_control_code/','watchdog','',1468432011,1474551505,27010,'','',0,0,'This should always be running.  If not, you must start it manually using the path above.  The Watchdog will start all other instruments/daemons automatically if their `run` flag is set.  '),('alarm_siren','Light and Siren on top of the HV rack','Alarm',0,0,1,'ADAM6000/ADAM6060','modbus','192.168.91.91',-1,-1,-1,NULL,NULL,0,0,''),('disk_free','Monitor free disk space','Sys',1,0,1,'disk_free/disk_free','daemon',NULL,1468432011,1474551501,27149,NULL,NULL,0,0,''),('Arduino_IO','Arduino general IO','IO',0,0,1,'Arduino_ADC/Arduino_ADC','ethernet','192.168.1.90',-1,-1,-1,'5000',NULL,0,0,NULL);
/*!40000 ALTER TABLE `sc_insts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Alarm_Light`
--

DROP TABLE IF EXISTS `sc_sens_Alarm_Light`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Alarm_Light` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Alarm_Light`
--

LOCK TABLES `sc_sens_Alarm_Light` WRITE;
/*!40000 ALTER TABLE `sc_sens_Alarm_Light` DISABLE KEYS */;
INSERT INTO `sc_sens_Alarm_Light` VALUES (1457555287,2.3,0),(1459350469,0,0);
/*!40000 ALTER TABLE `sc_sens_Alarm_Light` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Alarm_Siren`
--

DROP TABLE IF EXISTS `sc_sens_Alarm_Siren`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Alarm_Siren` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Alarm_Siren`
--

LOCK TABLES `sc_sens_Alarm_Siren` WRITE;
/*!40000 ALTER TABLE `sc_sens_Alarm_Siren` DISABLE KEYS */;
INSERT INTO `sc_sens_Alarm_Siren` VALUES (1459350470,0,0);
/*!40000 ALTER TABLE `sc_sens_Alarm_Siren` ENABLE KEYS */;
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
INSERT INTO `sc_sens_DAQ_ctrl` VALUES (1461529514,1,0),(1461529515,0,0);
/*!40000 ALTER TABLE `sc_sens_DAQ_ctrl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Run_ctrl`
--

DROP TABLE IF EXISTS `sc_sens_Run_ctrl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Run_ctrl` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Run_ctrl`
--

LOCK TABLES `sc_sens_Run_ctrl` WRITE;
/*!40000 ALTER TABLE `sc_sens_Run_ctrl` DISABLE KEYS */;
INSERT INTO `sc_sens_Run_ctrl` VALUES (1461529604,1,0),(1461529605,0,0);
/*!40000 ALTER TABLE `sc_sens_Run_ctrl` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Set_Temp_A`
--

DROP TABLE IF EXISTS `sc_sens_Set_Temp_A`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Set_Temp_A` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Set_Temp_A`
--

LOCK TABLES `sc_sens_Set_Temp_A` WRITE;
/*!40000 ALTER TABLE `sc_sens_Set_Temp_A` DISABLE KEYS */;
INSERT INTO `sc_sens_Set_Temp_A` VALUES (1457977153,23,0);
/*!40000 ALTER TABLE `sc_sens_Set_Temp_A` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_TC_onoff`
--

DROP TABLE IF EXISTS `sc_sens_TC_onoff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_TC_onoff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_TC_onoff`
--

LOCK TABLES `sc_sens_TC_onoff` WRITE;
/*!40000 ALTER TABLE `sc_sens_TC_onoff` DISABLE KEYS */;
INSERT INTO `sc_sens_TC_onoff` VALUES (1457977139,1,0),(1457977151,0,0);
/*!40000 ALTER TABLE `sc_sens_TC_onoff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sc_sens_Temp_A`
--

DROP TABLE IF EXISTS `sc_sens_Temp_A`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sc_sens_Temp_A` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL,
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sens_Temp_A`
--

LOCK TABLES `sc_sens_Temp_A` WRITE;
/*!40000 ALTER TABLE `sc_sens_Temp_A` DISABLE KEYS */;
/*!40000 ALTER TABLE `sc_sens_Temp_A` ENABLE KEYS */;
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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sensor_types`
--

LOCK TABLES `sc_sensor_types` WRITE;
/*!40000 ALTER TABLE `sc_sensor_types` DISABLE KEYS */;
INSERT INTO `sc_sensor_types` VALUES (1,'Alarm'),(2,'Sys'),(3,'Temperature'),(6,'DAQ'),(7,'TS');
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
  `num` int(11) NOT NULL DEFAULT '0',
  `instrument` varchar(16) COLLATE latin1_general_cs NOT NULL DEFAULT 'inst_name',
  `units` varchar(16) COLLATE latin1_general_cs NOT NULL DEFAULT 'units',
  `discrete_vals` varchar(128) COLLATE latin1_general_cs DEFAULT NULL,
  `al_set_val_low` double NOT NULL DEFAULT '0',
  `al_set_val_high` double NOT NULL DEFAULT '0',
  `al_arm_val_low` tinyint(1) NOT NULL DEFAULT '0',
  `al_arm_val_high` tinyint(1) NOT NULL DEFAULT '0',
  `al_set_rate_low` double NOT NULL DEFAULT '0',
  `al_set_rate_high` double NOT NULL DEFAULT '0',
  `al_arm_rate_low` tinyint(1) NOT NULL DEFAULT '0',
  `al_arm_rate_high` tinyint(1) NOT NULL DEFAULT '0',
  `alarm_tripped` tinyint(1) NOT NULL DEFAULT '0',
  `grace` int(11) NOT NULL DEFAULT '0',
  `last_trip` int(11) NOT NULL DEFAULT '-1',
  `settable` tinyint(1) NOT NULL DEFAULT '0',
  `show_rate` tinyint(1) NOT NULL DEFAULT '0',
  `hide_sensor` tinyint(1) NOT NULL DEFAULT '0',
  `update_period` int(11) NOT NULL DEFAULT '60',
  `num_format` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `user1` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `user2` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `user3` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `user4` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `parm1` double NOT NULL DEFAULT '0',
  `parm2` double NOT NULL DEFAULT '0',
  `parm3` double NOT NULL DEFAULT '0',
  `parm4` double NOT NULL DEFAULT '0',
  `notes` text COLLATE latin1_general_cs,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sc_sensors`
--

LOCK TABLES `sc_sensors` WRITE;
/*!40000 ALTER TABLE `sc_sensors` DISABLE KEYS */;
INSERT INTO `sc_sensors` VALUES ('Alarm_Light','Alarm light on the HV rack','1','Relay','siren',0,'alarm_siren','discrete','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,1,0,0,0,''),('Alarm_Siren','Alarm siren on the HV rack','1','Relay','siren',1,'alarm_siren','discrete','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,1,0,0,0,''),('dsk_shm','Free space on /dev/shm','2','disk','full',0,'disk_free','%',NULL,50,99,1,0,0,0,0,0,0,0,-1,0,0,0,30,NULL,'/dev/shm',NULL,NULL,NULL,0,0,0,0,''),('dsk_root','Free space on /','2','disk','full',0,'disk_free','%',NULL,30,70,1,0,0,0,0,0,0,0,-1,0,0,0,30,NULL,'/',NULL,NULL,NULL,0,0,0,0,''),('Set_Temp_A',NULL,'3',NULL,'full,TempControl',0,'inst_name','K',NULL,0,0,0,0,0,0,0,0,0,0,-1,1,0,1,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Temp_A',NULL,'3',NULL,'full',0,'inst_name','K',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,1,1,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Temp_B',NULL,'3',NULL,'full',1,'inst_name','K',NULL,0,0,0,0,0,0,0,0,0,0,-1,0,0,1,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('TC_onoff',NULL,'3',NULL,'full,TempControl',0,'inst_name','discrete','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL),('Run_ctrl','Run control handle','6','ctrl','full',0,'inst_name','discrete','0:1;Off:On',0,0,0,0,0,0,0,0,0,0,-1,1,0,0,60,NULL,NULL,NULL,NULL,NULL,0,0,0,0,NULL);
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
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_privileges`
--

LOCK TABLES `user_privileges` WRITE;
/*!40000 ALTER TABLE `user_privileges` DISABLE KEYS */;
INSERT INTO `user_privileges` VALUES (1,'guest','all'),(2,'basic','all'),(3,'full','all'),(4,'admin','all'),(5,'config','all'),(20,'TempControl','192.168.1.14'),(25,'DAQ',''),(27,'TS','all'),(17,'siren','all');
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
  `can_manage` tinyint(1) NOT NULL DEFAULT '0',
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
  `on_call` tinyint(1) NOT NULL DEFAULT '0',
  `shift_status` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `privileges` text COLLATE latin1_general_cs,
  UNIQUE KEY `username` (`user_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('guest','084e0343a0486ff05530df6c705c8bb4','guest','none','','','',0,'Off','basic,full,guest'),('root','63a9f0ea7bb98050796b649e85481845','root','root','root','root','root',0,'Off','admin,basic,config,full,lug,siren'),('james','bfd59291e825b5f2bbf1eb76569f8fe7','James Nikkel','Yale','james.nikkel@yale.edu',NULL,'203 430 3404',0,'Shift Leader','admin,basic,config,DAQ,full,siren,TempControl,TS');
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

-- Dump completed on 2016-09-22  9:45:02
