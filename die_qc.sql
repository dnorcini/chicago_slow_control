-- MySQL dump 10.17  Distrib 10.3.17-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: die_qc
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
-- Table structure for table `DIE`
--

DROP TABLE IF EXISTS `DIE`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `DIE` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` text COLLATE latin1_general_cs DEFAULT NULL,
  `Status` text COLLATE latin1_general_cs DEFAULT NULL,
  `Wafer_ID` text COLLATE latin1_general_cs DEFAULT NULL,
  `Wafer_Position` text COLLATE latin1_general_cs DEFAULT NULL,
  `Activation` int(11) DEFAULT NULL,
  `Grade_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Grade_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Grade_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Grade_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Check_L1` tinyint(1) DEFAULT NULL,
  `Check_L2` tinyint(1) DEFAULT NULL,
  `Check_U1` tinyint(1) DEFAULT NULL,
  `Check_U2` tinyint(1) DEFAULT NULL,
  `Notes_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Notes_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Notes_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Notes_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Reviewer` text COLLATE latin1_general_cs DEFAULT NULL,
  `Notes` text COLLATE latin1_general_cs DEFAULT NULL,
  `Tester` text COLLATE latin1_general_cs DEFAULT NULL,
  `Test_Date` date DEFAULT NULL,
  `Test_Time` time DEFAULT NULL,
  `Chamber` text COLLATE latin1_general_cs DEFAULT NULL,
  `Temp` decimal(5,2) DEFAULT NULL,
  `Feedthru_Position` text COLLATE latin1_general_cs DEFAULT NULL,
  `ACM` text COLLATE latin1_general_cs DEFAULT NULL,
  `Script` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image_Dir` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Saturation_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Comments_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Reference_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Saturation_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Comments_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Reference_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Saturation_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Comments_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Reference_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Saturation_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Comments_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_Reference_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Trace_File` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Tracks_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Noise_L1` decimal(10,2) DEFAULT NULL,
  `Image1_Defects_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Comments_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Reference_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Tracks_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Noise_L2` decimal(10,2) DEFAULT NULL,
  `Image1_Defects_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Comments_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Reference_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Tracks_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Noise_U1` decimal(10,2) DEFAULT NULL,
  `Image1_Defects_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Comments_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Reference_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Tracks_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Noise_U2` decimal(10,2) DEFAULT NULL,
  `Image1_Defects_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Comments_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_Reference_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image1_File` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Pixel_Defects_L1` int(11) DEFAULT NULL,
  `Image2_Column_Defects_L1` int(11) DEFAULT NULL,
  `Image2_Region_Defect_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Noise_Overscan_L1` decimal(10,2) DEFAULT NULL,
  `Image2_CTI_Code_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_CTI_Visual_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Sharpness_Tracks_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Comments_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Reference_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Pixel_Defects_L2` int(11) DEFAULT NULL,
  `Image2_Column_Defects_L2` int(11) DEFAULT NULL,
  `Image2_Region_Defect_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Noise_Overscan_L2` decimal(10,2) DEFAULT NULL,
  `Image2_CTI_Code_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_CTI_Visual_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Sharpness_Tracks_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Comments_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Reference_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Pixel_Defects_U1` int(11) DEFAULT NULL,
  `Image2_Column_Defects_U1` int(11) DEFAULT NULL,
  `Image2_Region_Defect_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Noise_Overscan_U1` decimal(10,2) DEFAULT NULL,
  `Image2_CTI_Code_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_CTI_Visual_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Sharpness_Tracks_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Comments_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Reference_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Pixel_Defects_U2` int(11) DEFAULT NULL,
  `Image2_Column_Defects_U2` int(11) DEFAULT NULL,
  `Image2_Region_Defect_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Noise_Overscan_U2` decimal(10,2) DEFAULT NULL,
  `Image2_CTI_Code_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_CTI_Visual_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Sharpness_Tracks_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Comments_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_Reference_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image2_File` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Pixel_Defects_L1` int(11) DEFAULT NULL,
  `Image3_Column_Defects_L1` int(11) DEFAULT NULL,
  `Image3_Region_Defect_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Noise_Overscan_L1` decimal(10,2) DEFAULT NULL,
  `Image3_CTI_Code_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_CTI_Visual_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Sharpness_Tracks_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Comments_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Reference_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Pixel_Defects_L2` int(11) DEFAULT NULL,
  `Image3_Column_Defects_L2` int(11) DEFAULT NULL,
  `Image3_Region_Defect_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Noise_Overscan_L2` decimal(10,2) DEFAULT NULL,
  `Image3_CTI_Code_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_CTI_Visual_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Sharpness_Tracks_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Comments_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Reference_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Pixel_Defects_U1` int(11) DEFAULT NULL,
  `Image3_Column_Defects_U1` int(11) DEFAULT NULL,
  `Image3_Region_Defect_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Noise_Overscan_U1` decimal(10,2) DEFAULT NULL,
  `Image3_CTI_Code_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_CTI_Visual_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Sharpness_Tracks_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Comments_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Reference_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Pixel_Defects_U2` int(11) DEFAULT NULL,
  `Image3_Column_Defects_U2` int(11) DEFAULT NULL,
  `Image3_Region_Defect_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Noise_Overscan_U2` decimal(10,2) DEFAULT NULL,
  `Image3_CTI_Code_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_CTI_Visual_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Sharpness_Tracks_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Comments_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_Reference_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image3_File` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image4_Res_L1` decimal(10,2) DEFAULT NULL,
  `Image4_Gain_L1` decimal(10,2) DEFAULT NULL,
  `Image4_Dark_Current_L1` decimal(10,2) DEFAULT NULL,
  `Image4_Comments_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image4_Reference_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image4_Res_L2` decimal(10,2) DEFAULT NULL,
  `Image4_Gain_L2` decimal(10,2) DEFAULT NULL,
  `Image4_Dark_Current_L2` decimal(10,2) DEFAULT NULL,
  `Image4_Comments_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image4_Reference_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image4_Res_U1` decimal(10,2) DEFAULT NULL,
  `Image4_Gain_U1` decimal(10,2) DEFAULT NULL,
  `Image4_Dark_Current_U1` decimal(10,2) DEFAULT NULL,
  `Image4_Comments_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image4_Reference_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image4_Res_U2` decimal(10,2) DEFAULT NULL,
  `Image4_Gain_U2` decimal(10,2) DEFAULT NULL,
  `Image4_Dark_Current_U2` decimal(10,2) DEFAULT NULL,
  `Image4_Comments_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image4_Reference_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image4_File` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image5_Column_Defects_L1` int(11) DEFAULT NULL,
  `Image5_Noise_L1` decimal(10,2) DEFAULT NULL,
  `Image5_Comments_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image5_Reference_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image5_Column_Defects_L2` int(11) DEFAULT NULL,
  `Image5_Noise_L2` decimal(10,2) DEFAULT NULL,
  `Image5_Comments_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image5_Reference_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image5_Column_Defects_U1` int(11) DEFAULT NULL,
  `Image5_Noise_U1` decimal(10,2) DEFAULT NULL,
  `Image5_Comments_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image5_Reference_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image5_Column_Defects_U2` int(11) DEFAULT NULL,
  `Image5_Noise_U2` decimal(10,2) DEFAULT NULL,
  `Image5_Comments_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image5_Reference_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image5_File` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Column_Defects_L1` int(11) DEFAULT NULL,
  `Image6_Region_Defect_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Noise_Overscan_L1` decimal(10,2) DEFAULT NULL,
  `Image6_CTI_Code_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_CTI_Visual_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Sharpness_Tracks_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Comments_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Reference_L1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Column_Defects_L2` int(11) DEFAULT NULL,
  `Image6_Region_Defect_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Noise_Overscan_L2` decimal(10,2) DEFAULT NULL,
  `Image6_CTI_Code_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_CTI_Visual_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Sharpness_Tracks_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Comments_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Reference_L2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Column_Defects_U1` int(11) DEFAULT NULL,
  `Image6_Region_Defect_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Noise_Overscan_U1` decimal(10,2) DEFAULT NULL,
  `Image6_CTI_Code_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_CTI_Visual_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Sharpness_Tracks_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Comments_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Reference_U1` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Column_Defects_U2` int(11) DEFAULT NULL,
  `Image6_Region_Defect_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Noise_Overscan_U2` decimal(10,2) DEFAULT NULL,
  `Image6_CTI_Code_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_CTI_Visual_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Sharpness_Tracks_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Comments_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_Reference_U2` text COLLATE latin1_general_cs DEFAULT NULL,
  `Image6_File` text COLLATE latin1_general_cs DEFAULT NULL,
  `Last_Update` int(11) NOT NULL DEFAULT -1,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `num_2` (`ID`),
  KEY `num` (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `DIE`
--

LOCK TABLES `DIE` WRITE;
/*!40000 ALTER TABLE `DIE` DISABLE KEYS */;
INSERT INTO `DIE` VALUES (1,'09_13_2024_batch_1_A','Tested','240525-24-D5','D',NULL,'Operational','Operational','Operational','Science',1,1,1,1,'test','test','test','test',NULL,NULL,'','2024-09-11','03:03:00',' ',5.80,'2','106','','','No',NULL,'trace_ch0_NDCMPRE5_101_20240913_214334__0.csv','No',NULL,'trace_ch1_NDCMPRE5_101_20240913_214334__1.csv','No',NULL,'trace_ch2_NDCMPRE5_101_20240913_214335__2.csv','No',NULL,'trace_ch3_NDCMPRE5_101_20240913_214336__3.csv','/var/www/html/QC_production/uploads/edit_die/die_1/trace_file.png',' ',5.00,' ',NULL,'avg_sngl_101_20240913_215533_4.fz',' ',6.00,' ','Col defect : 41',NULL,' ',5.30,' ','Pix defect : col 299','avg_sngl_101_20240913_215533_4.fz',' ',5.20,' ',NULL,'avg_sngl_101_20240913_215533_4.fz','/var/www/html/QC_production/uploads/edit_die/die_2/image1_file.png',1,NULL,NULL,5.00,NULL,'No','Yes','Pixel defect: [1237,57]','avg_sngl_101_20240913_222143_5.fz  avg_sngl_101_20240913_230042_10.fz avg_sngl_101_20240913_222931_6.fz  avg_sngl_101_20240913_230829_11.fz avg_sngl_101_20240913_223719_7.fz  avg_sngl_101_20240913_231616_12.fz avg_sngl_101_20240913_224506_8.fz  avg_sngl_101_20240913_232403_13.fz avg_sngl_101_20240913_225254_9.fz  avg_sngl_101_20240913_233151_14.fz',4,1,NULL,5.10,' ','No','Yes','Col defect: 405, Pix defect: [1016,482],[1321,6],[2622,275],[2594,692]',NULL,NULL,173,NULL,5.30,' ','No','Yes','Transfer gate issue, many col defects',NULL,5,NULL,NULL,5.20,' ','No','Yes','Pixel defect: [1826,559],[1839,568],[2011,622],[2529,333],[2902,538]',NULL,'/var/www/html/QC_production/uploads/edit_die/die_1/image2_file.png',1,NULL,NULL,5.20,' ','No','Yes','Same as before','avg_sngl_101_20240913_233949_15.fz  avg_sngl_101_20240914_001846_20.fz avg_sngl_101_20240913_234737_16.fz  avg_sngl_101_20240914_002634_21.fz avg_sngl_101_20240913_235524_17.fz  avg_sngl_101_20240914_003421_22.fz avg_sngl_101_20240914_000311_18.fz  avg_sngl_101_20240914_004209_23.fz avg_sngl_101_20240914_001059_19.fz  avg_sngl_101_20240914_004956_24.fz',4,8,NULL,6.00,' ','No','Yes','New pix defect at [1461,497], [851,351], many col defects in e-transient area before col 300, old col defect gone',NULL,4,10,NULL,5.30,' ','No','Yes','Pix defect [817,10], [1611,38], [959,657], [508,348], many col defects in e-transient area before col 300, old col defect gone',NULL,5,10,NULL,5.30,' ','No','Yes','Same pixel defects as before, many col defects in e-transient area before col 300',NULL,'/var/www/html/QC_production/uploads/edit_die/die_1/image3_file.png',0.14,1.27,0.16,NULL,'avg_skips_SR_101_20240914_005844_25.fz',0.14,1.25,0.30,NULL,'avg_skips_SR_101_20240914_005844_25.fz',0.14,1.30,0.19,NULL,'avg_skips_SR_101_20240914_005844_25.fz',0.15,1.29,0.30,NULL,'avg_skips_SR_101_20240914_005844_25.fz','/var/www/html/QC_production/uploads/edit_die/die_1/image4_file.png',NULL,5.20,'Some noise patterns','avg_sngl_SR_101_20240914_010546_26.fz',NULL,5.20,'Some noise patterns','avg_sngl_SR_101_20240914_010546_26.fz',NULL,5.20,'Some noise patterns','avg_sngl_SR_101_20240914_010546_26.fz',NULL,5.30,'Some noise patterns','avg_sngl_SR_101_20240914_010546_26.fz','/var/www/html/QC_production/uploads/edit_die/die_1/image5_file.png',4,'test',5.00,' ','No','Yes','Col defect [2586, 3248, 3249, 3474], e transient region absorption defect','avg_sngl_101_20240914_010938_27.fz',16,NULL,5.10,' ','No','Yes',' Die testing results Die testing results 100% 10      	 Col defect [2334, 2335, 2336, 2337, 2338, 2339, 2340, 2341, 2586, 2614, 3135,        3248, 3249, 3622] ','avg_sngl_101_20240914_011955_28.fz',100,NULL,5.10,' ','No','Yes','TG issue, many col defects','avg_sngl_101_20240914_013012_29.fz',4,NULL,5.30,' ','No','Yes','Col defect [2521, 2894, 2895, 3529, 3557], e transient region absorption defect','avg_sngl_101_20240914_014030_30.fz','/var/www/html/QC_production/uploads/edit_die/die_1/image6_file.png',1727174097),(2,NULL,NULL,NULL,NULL,NULL,NULL,'Operational',NULL,NULL,0,1,1,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'19:09:00',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,' ',NULL,' ',NULL,NULL,' ',NULL,' ',NULL,NULL,' ',NULL,' ',NULL,NULL,' ',NULL,' ',NULL,NULL,'/var/www/html/QC_production/uploads/edit_die/die_2/image1_file.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1727666398);
/*!40000 ALTER TABLE `DIE` ENABLE KEYS */;
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
INSERT INTO `globals` VALUES ('Master_alarm',0,0,0,0,0,0,0,0,'','','',''),('Title',0,0,0,0,0,0,0,0,'DIE',NULL,NULL,NULL),('have_TS',0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('have_RGA',0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('have_Cams',1,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('have_LB',1,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL),('web_bg_colour',0,0,0,0,0,0,0,0,'white',NULL,NULL,NULL),('web_text_colour',0,0,0,0,0,0,0,0,'black',NULL,NULL,NULL),('have_HV_crate',0,0,0,0,0,0,0,0,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `globals` ENABLE KEYS */;
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
INSERT INTO `user_privileges` VALUES (1,'guest','all'),(2,'basic','all'),(3,'full','all'),(4,'admin','all');
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
  `privileges` text COLLATE latin1_general_cs DEFAULT NULL,
  `shift_status` text COLLATE latin1_general_cs DEFAULT NULL,
  UNIQUE KEY `username` (`user_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('guest','084e0343a0486ff05530df6c705c8bb4','guest','none','','basic,guest',''),('root','63a9f0ea7bb98050796b649e85481845','root','root','root','admin,basic,full',''),('dnorcini','30d3016ed2b8e3e62f1cdb9b124a1347','Danielle Norcini','UChicago','dnorcini@jhu.edu','admin,basic,full',''),('smida','4ea71f586688055838f4b33717ce6c6e','Radomir Smida','UChicago','smida@kicp.uchicago.edu','basic,full',NULL),('priviter','e99a18c428cb38d5f260853678922e03','Paolo Privitera','UChicago','priviter@astro.uchicago.edu','basic,full',NULL),('huehnm','e09a05fd3bf8dba97f0f0df9dd125975','Michael Huehn ','UW','huehnm@uw.edu','basic,full',NULL),('alvaro','02fa158c0e5138da37e0126d7768b57b','Alvaro Chavarria','UW','chavarri@uw.edu','basic,full',NULL),('mtraina','3ac2749ebcde331cfb8892551cb09b98','Michelangelo Traina','UW','mtraina@uw.edu','basic,full',NULL),('mconde','e99a18c428cb38d5f260853678922e03','Marcel Conde','UW','mconde@uw.edu','basic,full',NULL),('paul09','e99a18c428cb38d5f260853678922e03','Jonty Paul','UChicago','paul09@uchicago.edu','basic,full',NULL),('rachanayajur','55547040e69721288762ea9064e6f851','Rachana Yajur','UChicago','rachanayajur@uchicago.edu','basic,full',NULL),('bertou','21fe7e28513d7cf46c3e443296697353','Xavier Bertou','Bariloche ','bertou@gmail.com','basic,full',NULL),('henglin','6b64e315189134bc297c5583ff3bd619','Heng Lin','UW','henglin@uw.edu','basic,full',NULL);
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

-- Dump completed on 2024-09-30 16:17:42
