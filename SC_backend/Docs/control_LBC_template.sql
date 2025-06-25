-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 25, 2025 at 07:43 PM
-- Server version: 10.3.28-MariaDB
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `control`
--

-- --------------------------------------------------------

--
-- Table structure for table `daq_control`
--

CREATE TABLE `daq_control` (
  `entry_id` int(11) NOT NULL,
  `lug_entry_id` int(11) NOT NULL,
  `utc` int(11) NOT NULL,
  `acquire_now` tinyint(1) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daq_control_tmp`
--

CREATE TABLE `daq_control_tmp` (
  `utc` int(11) NOT NULL,
  `user_name` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `tag` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `acquire_now` tinyint(1) NOT NULL,
  `xml_settings_file` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL,
  `end_status` text CHARACTER SET latin1 COLLATE latin1_general_cs NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daq_presets`
--

CREATE TABLE `daq_presets` (
  `entry_id` int(11) NOT NULL,
  `preset` mediumblob NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dqm_channel_data`
--

CREATE TABLE `dqm_channel_data` (
  `utc` int(11) NOT NULL,
  `runs` text COLLATE latin1_general_cs NOT NULL,
  `prefixes` text COLLATE latin1_general_cs NOT NULL,
  `dat_files` text COLLATE latin1_general_cs NOT NULL,
  `avg_event_rate` float NOT NULL,
  `avg_livetime` float NOT NULL,
  `pulse_rates` text COLLATE latin1_general_cs NOT NULL,
  `avg_pulse_lengths` text COLLATE latin1_general_cs NOT NULL,
  `avg_baselines_mv` text COLLATE latin1_general_cs NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `dqm_control`
--

CREATE TABLE `dqm_control` (
  `utc_time` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `dqm_event_data`
--

CREATE TABLE `dqm_event_data` (
  `utc_time` int(11) NOT NULL,
  `run` int(11) NOT NULL,
  `prefix` text COLLATE latin1_general_cs NOT NULL,
  `event_rate` float NOT NULL,
  `livetime` float NOT NULL,
  `deadtime` float NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `dqm_event_display`
--

CREATE TABLE `dqm_event_display` (
  `utc` int(11) NOT NULL,
  `picture` longblob NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `globals`
--

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
  `string4` varchar(16) COLLATE latin1_general_cs DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `globals`
--

INSERT INTO `globals` (`name`, `int1`, `int2`, `int3`, `int4`, `double1`, `double2`, `double3`, `double4`, `string1`, `string2`, `string3`, `string4`) VALUES
('Master_alarm', 0, 0, 0, 0, 0, 0, 0, 0, '', '', '', 'sravan'),
('Title', 0, 0, 0, 0, 0, 0, 0, 0, 'Slow Control Sys', NULL, NULL, NULL),
('have_TS', 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL),
('have_RGA', 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL),
('have_Cams', 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL),
('have_LB', 1, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL),
('web_bg_colour', 0, 0, 0, 0, 0, 0, 0, 0, 'white', NULL, NULL, NULL),
('web_text_colour', 0, 0, 0, 0, 0, 0, 0, 0, 'navy', NULL, NULL, NULL),
('have_HV_crate', 0, 0, 0, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `lug_categories`
--

CREATE TABLE `lug_categories` (
  `l_c_indx` int(11) NOT NULL,
  `category` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `subcategory` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'General'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lug_categories`
--

INSERT INTO `lug_categories` (`l_c_indx`, `category`, `subcategory`) VALUES
(16, 'DAQ', 'General'),
(15, 'Disk', 'General'),
(3, 'System', 'General'),
(4, 'System', 'TempControl'),
(17, 'DAQ', 'Calibration'),
(8, 'Calibrations', 'General'),
(10, 'Calibrations', 'Co60'),
(18, 'DAQ', 'Physics'),
(12, 'Calibrations', 'Co57'),
(13, 'Calibrations', 'Ba133'),
(19, 'DAQ', 'CCDTesting'),
(20, 'System', 'Vacuum');

-- --------------------------------------------------------

--
-- Table structure for table `lug_entries`
--

CREATE TABLE `lug_entries` (
  `entry_id` int(11) NOT NULL,
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
  `strikeme` tinyint(1) DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `lug_entries`
--

INSERT INTO `lug_entries` (`entry_id`, `important_flag`, `action_user`, `action_time`, `edit_user`, `edit_time`, `run_number`, `category`, `subcategory`, `entry_description`, `entry_image`, `entry_image_thumb`, `entry_file`, `filename`, `source`, `strikeme`) VALUES
(12, 0, 'james', 1461523662, 'james', 1461523690, 1, 'System', 'General', 'Test of the log book system.', NULL, NULL, NULL, NULL, NULL, 0),
(13, 0, 'guest', 1461523701, 'james', 1461523729, 2, 'Disk', 'General', 'This is also a thing that works.', NULL, NULL, NULL, NULL, NULL, 0),
(14, 0, 'mtraina', 1637147316, 'root', 1637147629, 1, 'System', 'TempControl', 'First cooling test. Set cryocooler in power control mode PWOUT=70 W.\r\nCold-head temperature dropped fast to ~100 K in ~10 minutes.\r\nPressure decrease rate increased considerably. Cryostat pressure below the 1xE-5 mbar (stable above without cooling). ', NULL, NULL, NULL, NULL, NULL, 0),
(15, 0, 'root', 1637161582, 'root', 1637162368, 1, 'System', 'TempControl', 'Cryocooler tripped when set power to 180 W, and was not able to reach set power. Set power to 150 W, cooler stabilized. We suspect large thermal inefficiency from kapton patches between cooler coldhead and flexible copper strap.', NULL, NULL, NULL, NULL, NULL, 0),
(16, 0, 'mtraina', 1637222033, 'root', 1637222187, 1, 'System', 'TempControl', 'Cryocooler power stable to 150 W over night. Temperatur of cold mass table and cold fingers decreased by 50 K over night, going from ~260 K to ~210 K. Lead temperature stably around 295 K. Some improvement of thermal contact necessary, probably at cooler coldhead with copper straps.', NULL, NULL, NULL, NULL, NULL, 0),
(17, 0, 'mtraina', 1637309043, 'root', 1637309709, 1, 'System', 'TempControl', 'After switching the kapton tape at cooler coldhead, and securing screw with copper strap for thermal contact, we cooled down at 150 W. After cooling over night, Temperature is around 177 K on cold mass table, fingers and CCD box. We expect temprature to further decrease by ~10 K (if left to 150 W). We are increasing power in steps to assess functioning of cryocooler up to maximal power (it tripped at 180 W in previous configuration). Cooler worked at 160 W. Setting power to 180 and 170 W made it trip again. We plan to open up and improve thermal contact.', NULL, NULL, NULL, NULL, NULL, 0),
(18, 0, 'mtraina', 1637310216, 'root', 1637310594, 1, 'System', 'TempControl', 'Looking at the temperature of sensors on cold table and fingers, a change in cooling rate can be seen around 18:45 of Nov 18: it got significantly worse (lower). The cooler coldhead temperature shows a corresponding step-like drop, potentially hinting to an abrupt worsening of thermal contact. ', NULL, NULL, NULL, NULL, NULL, 0),
(19, 0, 'mtraina', 1637336122, 'mtraina', 1637336913, 1, 'System', 'TempControl', 'We have tried to improve thermal contact again: changed plastic washer (for electrical isolation) on cold head screw with metal washer, tried to screw tighter after seeing worse cooling rate, maybe due to screw material reaction to temperature change. \r\nCurrent sensor status:\r\nTc1-LS Channel A\r\nTc2-LS Channel B\r\nTChC(Copper cold finger)-LS Channel C\r\nTChA(CCD Box)-LS Channel D', NULL, NULL, NULL, NULL, NULL, 0),
(20, 0, 'mtraina', 1637847142, 'mtraina', 1637847572, 1, 'System', 'TempControl', 'We have managed to cool down efficiently and get the system to ~120 K with CCD box, thanks to a 2-washer solution. A plastic washer and a brass(?) one. Cooler power set to 150 W. We installed 6417 in copper box and started CCD testing at higher T ~ 160 K on CCD box.', NULL, NULL, NULL, NULL, NULL, 0),
(21, 0, 'mtraina', 1637847592, 'mtraina', 1637847946, 1, 'DAQ', 'CCDTesting', 'Started testing 6417. Images showed quite high dark current, potentially hinting at bad cooling of CCD sensor, despite box being ~10-20 K warmer than cold fingers (at desired target temperature).\r\nWe did preliminary noise optimization by checking several grounding settings, we found that cryogt controller should be isolated from rack, and that chamber should be grounded on SRS set in floating mode. Further optimization envisioned.\r\nLong exposures show background rate not as low as we hoped, with ~8 non-diffusion limited hits in 1h exposures. Now opening up to change CCD box to check if DC rate relation with box/cooling efficiency. \r\nPlan is to keep testing 6417.', NULL, NULL, NULL, NULL, NULL, 0),
(22, 0, 'mtraina', 1639417936, 'mtraina', 1639418003, 1, 'System', 'Vacuum', 'Starting vacuum test: turning pump off to check pressure trend.', NULL, NULL, NULL, NULL, NULL, 0),
(23, 0, 'priviter', 1639842048, 'priviter', 1639842646, 1, 'System', 'General', 'We have completed the LBC Slow Control installation. Alarms are now activated', NULL, NULL, NULL, NULL, NULL, 0),
(24, 0, 'mtraina', 1675768938, 'mtraina', 1675768984, 1, 'System', 'General', 'UPS load alarm cause Antoine plugged in vacuum cleaner in power strip', NULL, NULL, NULL, NULL, NULL, 0),
(25, 0, 'mtraina', 1676477174, 'mtraina', 1676477238, 1, 'System', 'General', 'Yesterday, Rachana changed drierite for RAD7. Used drierite can be restored by \"cooking\" it in an oven (check manual)', NULL, NULL, NULL, NULL, NULL, 0),
(26, 0, 'mtraina', 1676555587, 'mtraina', 1676555679, 1, 'System', 'General', 'RAD7 inlet tubes were lying on the ground.  We taped them to SC table leg at 12:30', NULL, NULL, NULL, NULL, NULL, 0),
(27, 0, 'mtraina', 1742829889, 'mtraina', 1742829981, 1, 'System', 'General', 'Moved the SC switch to the UPS. Connection of power strip with cooler fan to UPS, adam switch etc. inadvertently came off. Reject T rose steadily. We found root cause and fixed promptly. System operating nominally.', NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `msg_log`
--

CREATE TABLE `msg_log` (
  `msg_id` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `ip_address` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `subsys` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `msgs` text COLLATE latin1_general_cs DEFAULT NULL,
  `type` varchar(16) COLLATE latin1_general_cs DEFAULT NULL,
  `is_error` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `msg_log_types`
--

CREATE TABLE `msg_log_types` (
  `types` varchar(16) COLLATE latin1_general_cs NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `msg_log_types`
--

INSERT INTO `msg_log_types` (`types`) VALUES
('Alarm'),
('Alert'),
('Config.'),
('Error'),
('Setpoint'),
('Shifts');

-- --------------------------------------------------------

--
-- Table structure for table `runs`
--

CREATE TABLE `runs` (
  `num` int(11) NOT NULL,
  `start_t` int(11) NOT NULL,
  `end_t` int(11) NOT NULL DEFAULT 0,
  `file_path` varchar(128) COLLATE latin1_general_cs DEFAULT NULL,
  `file_root` varchar(128) COLLATE latin1_general_cs DEFAULT NULL,
  `note` char(128) COLLATE latin1_general_cs DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `runs`
--

INSERT INTO `runs` (`num`, `start_t`, `end_t`, `file_path`, `file_root`, `note`) VALUES
(1, 1342019232, 1457624232, '', '', 'Setup');

-- --------------------------------------------------------

--
-- Table structure for table `sc_insts`
--

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
  `notes` text COLLATE latin1_general_cs DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `sc_insts`
--

INSERT INTO `sc_insts` (`name`, `description`, `subsys`, `run`, `restart`, `WD_ctrl`, `path`, `dev_type`, `dev_address`, `start_time`, `last_update_time`, `PID`, `user1`, `user2`, `parm1`, `parm2`, `notes`) VALUES
('alarm_trip_sys', 'Alarm trip monitor program', 'Alarm', 1, 0, 1, 'alarm_trip_system/alarm_trip_sys', 'daemon', '', 1747210186, 1750880599, 6529, '', '', 0, 0, ''),
('alarm_alert_sys', 'Alarm alert monitor program', 'Alarm', 1, 0, 1, 'alarm_alert_system/alarm_alert_sys', 'daemon', '', 1747210186, 1750880606, 6534, 'Alarm_Light', 'Alarm_Siren', 0, 0, ''),
('Watchdog', 'Overseeing watchdog program', 'Watchdog', 1, 0, 0, '/home/damicm/SC_backend/slow_control_code/', 'watchdog', '', 1747210186, 1750880607, 6517, '', '', 0, 0, 'This should always be running.  If not, you must start it manually using the path above.  The Watchdog will start all other instruments/daemons automatically if their `run` flag is set.  '),
('alarm_siren', 'Light and Siren on top of the HV rack', 'Alarm', 0, 0, 1, 'ADAM6000/ADAM6060', 'modbus', '192.168.91.91', -1, -1, -1, NULL, NULL, 0, 0, ''),
('disk_free', 'Monitor free disk space', 'Sys', 1, 0, 1, 'disk_free/disk_free', 'daemon', NULL, 1747210186, 1750880598, 6546, NULL, NULL, 0, 0, ''),
('Arduino_IO', 'Arduino general IO', 'IO', 0, 0, 1, 'Arduino_ADC/Arduino_ADC', 'ethernet', '192.168.1.90', -1, -1, -1, '5000', NULL, 0, 0, NULL),
('CryoTelGT_AVC', 'Monitor and control CryoTelGT with AVC', 'LBC', 1, 0, 1, 'CryoTelGT_AVC/CryoTelGT_AVC', 'ethernet', '192.168.1.100', 1748958378, 1750880618, 1072358, '100', NULL, 0, 0, NULL),
('TPG361', 'Pfeiffer pressure controller', 'LBC', 0, 1, 1, 'TPG261/TPG261', 'ethernet', '192.168.1.11', -1, -1, -1, '8000', NULL, 0, 0, 'pump'),
('TPG362', '2-channel Pfeiffer pressure controller', 'LBC', 1, 0, 1, 'TPG362/TPG362', 'ethernet', '192.168.1.104', 1748958638, 1750880626, 1077512, '8000', NULL, 0, 0, 'cryostat\r\n\r\nWhen the gauges are switched off, the last recorded value of pressure is shown in interface. It won\'t go yellow after 10 minutes. It will when controller is switched off.'),
('KeysightE36312A', 'KeysightE36312A for UW amplifier board', 'LBC', 1, 0, 1, 'KeysightE36312A/KeysightE36312A', 'ethernet', '192.168.1.106', 1750880629, 1750880629, 1755431, '5025', NULL, 0, 0, NULL),
('Raritan_PX3', 'Monitor and control PDU', 'LBC', 1, 0, 1, 'Raritan_PX3/Raritan_PX3', 'modbus', '192.168.1.105', 1750685211, 1750880617, 3823804, '502', NULL, 0, 0, NULL),
('APC2200', 'APC2200 uninterrupted power supply', 'LBC', 1, 0, 1, 'APC2200/APC2200', 'modbus', '192.168.1.103', 1747210211, 1750880613, 6658, '502', NULL, 0, 0, NULL),
('WIENER_VME', 'Monitor and control VME crate for ACMs', 'LBC', 1, 0, 1, 'WIENER_VME/WIENER_VME', 'ethernet', '192.168.1.120', 1749127393, 1750880630, 2668556, '161', NULL, 0, 0, NULL),
('LS336', 'LS336 controller. Monitor temperature and control heaters', 'LBC', 1, 0, 1, 'LS336_eth/LS336', 'ethernet', '192.168.1.102', 1748958458, 1750880601, 1073939, '7777', NULL, 0, 0, NULL),
('HiCube80', 'HiCube 80 Pfeiffer vacuum pump', 'LBC', 1, 0, 1, 'HiCube80/HiCube80', 'ethernet', '192.168.1.101', 1750874851, 1750880619, 1686787, '5350', NULL, 0, 0, NULL),
('RAD7', 'Monitor and control RAD7 Radon sensor', 'LBC', 1, 0, 1, 'RAD7/RAD7', 'ethernet', '192.168.1.108', 1750880629, 1750880629, 1755434, '108', NULL, 0, 0, NULL),
('KeysightE36312A2', '2nd system KeysightE36312A for UW amplifier board', 'LBC', 1, 0, 1, 'KeysightE36312A2/KeysightE36312A2', 'ethernet', '192.168.1.107', 1750880629, 1750880629, 1755432, '5025', NULL, 0, 0, '2nd Leach');

-- --------------------------------------------------------

--
-- Table structure for table `sc_sensors`
--

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
  `notes` text COLLATE latin1_general_cs DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `sc_sensors`
--

INSERT INTO `sc_sensors` (`name`, `description`, `type`, `subtype`, `ctrl_priv`, `num`, `instrument`, `units`, `discrete_vals`, `al_set_val_low`, `al_set_val_high`, `al_arm_val_low`, `al_arm_val_high`, `al_set_rate_low`, `al_set_rate_high`, `al_arm_rate_low`, `al_arm_rate_high`, `alarm_tripped`, `grace`, `last_trip`, `settable`, `show_rate`, `hide_sensor`, `update_period`, `num_format`, `user1`, `user2`, `user3`, `user4`, `parm1`, `parm2`, `parm3`, `parm4`, `notes`) VALUES
('PDU_6_OnOff', 'Set PDU Outlet 6 On/Off (Rad7 power supply)', '21', 'outlet', 'full', 6, 'Raritan_PX3', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 30, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('dsk_shm', 'Free space on /dev/shm', '23', 'disk', 'full', 0, 'disk_free', '%', NULL, 50, 99, 1, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, '/dev/shm', NULL, NULL, NULL, 0, 0, 0, 0, ''),
('dsk_root', 'Free space on /', '23', 'disk', 'full', 0, 'disk_free', '%', NULL, 30, 70, 1, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, '/', NULL, NULL, NULL, 0, 0, 0, 0, ''),
('Temp_A', 'Temperature sensor A', '16', 'Temp', 'full', 1, 'LS336', 'K', NULL, 90, 0, 1, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Temp_B', 'Temperature sensor B', '16', 'Temp', 'full', 2, 'LS336', 'K', NULL, 95, 0, 1, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Rn_Concentration', 'RAD7 radon measurement', '24', 'rad_conc', 'full', 0, 'RAD7', 'Bq/m3', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Power_Output_AVC', 'Power output of AVC controller of CryoTelGT', '8', 'pwout', 'full', 0, 'CryoTelGT_AVC', 'W', NULL, 65, 0, 1, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, 'null', NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Reject_Temp', 'Reject temperature of CryoTelGT', '8', 'treject', 'full', 0, 'CryoTelGT_AVC', 'C', NULL, 0, 58, 0, 1, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Cooler', 'Set control mode of CryoTelGT (temperature or power)', '8', 'setcooler', 'full', 0, 'CryoTelGT_AVC', 'discrete', '0:2;Off:Power', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 5, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Power_Output', 'Set CryoTelGT power output in power control mode', '8', 'setpwout', 'full', 0, 'CryoTelGT_AVC', 'W', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 5, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Target_Temp', 'Set CryoTelGT target temperature in T control mode', '8', 'setttarget', 'full', 0, 'CryoTelGT_AVC', 'K', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Temp_Cooler', 'Temperature of CryoTelGT cold head', '8', 'tc', 'full', 0, 'CryoTelGT_AVC', 'K', NULL, 0, 160, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Temp_Target', 'CryoTelGT target temperature in T control mode', '8', 'ttarget', 'full', 0, 'CryoTelGT_AVC', 'K', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 1, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_1_OnOff', 'Set voltage on Amplifer board On/Off', '18', 'setonoff', 'full', 2, 'KeysightE36312A', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_3', 'Voltage on Leach board', '18', 'volt', 'full', 3, 'KeysightE36312A', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_2', 'Voltage on Amplifer- board', '18', 'volt', 'full', 2, 'KeysightE36312A', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_1', 'Voltage on Amplifer+ board', '18', 'volt', 'full', 1, 'KeysightE36312A', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Pressure_Gauge_1', 'Pressure of LBC cryostat', '9', 'pressure', 'full', 1, 'TPG362', 'mbar', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 5, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Pressure_Gauge_2', 'Pressure of LBC pump hose', '9', 'pressure', 'full', 2, 'TPG362', 'mbar', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 5, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Battery_Charge', 'Battery Charge of UPS', '19', 'battcharge', 'full', 0, 'APC2200', '%', NULL, 85, 0, 1, 0, 0, 0, 0, 0, 0, 60, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Power_Output', 'Power load of UPS', '19', 'apppower', 'full', 0, 'APC2200', '%', NULL, 0, 50, 0, 1, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Runtime', 'Apprx. runtime of UPS', '19', 'runtime', 'full', 0, 'APC2200', 'hours', NULL, 0.33, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Heater_100W', 'Heater power output percentage (sensor)', '16', 'Heater', 'full', 2, 'LS336', '%', NULL, 1, 85, 0, 1, 0, 0, 0, 0, 0, 60, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Heater100W_OnOff', 'Set power output setting on 100W Heater', '16', 'Heateronoff', 'full', 2, 'LS336', 'discrete', '0:1:3;Off:Low:High', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Ramprate_100W', 'Ramprate of 100W Heater', '16', 'Ramprate', 'full', 2, 'LS336', 'K/min', NULL, 0, 0.6, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Setpoint_100W', 'Setpoint of 100W Heater (sensor)', '16', 'Setpoint', 'full', 2, 'LS336', 'K', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Heater_100W', 'Setpoint of 100W Heater', '16', 'Setsetpoint', 'full', 2, 'LS336', 'K', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Ramprate', 'Set Ramprate of 100W Heater', '16', 'Setramprate', 'full', 2, 'LS336', 'K/min', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Temp_C', 'Temperature sensor C', '16', 'Temp', 'full', 3, 'LS336', 'K', NULL, 95, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Temp_D', 'Temperature sensor D', '16', 'Temp', 'full', 4, 'LS336', 'K', NULL, 0, 306, 0, 1, 0, 0, 0, 0, 0, 60, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Status_Gauge_1', 'Switch ON/OFF LBC cryostat pressure gauge', '9', 'status', 'full', 1, 'TPG362', 'discrete', '1:2;OFF:ON', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Status_Gauge_2', 'Switch ON/OFF LBC pump pressure gauge', '9', 'status', 'full', 2, 'TPG362', 'discrete', '1:2;OFF:ON', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_3_OnOff', 'Set voltage on Leach board On/Off', '18', 'setonoff', 'full', 3, 'KeysightE36312A', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Voltage_1', 'Set voltage on Amplifier+ board', '18', 'setvolt', 'full', 1, 'KeysightE36312A', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Voltage_2', 'Set voltage on Amplifier- board', '18', 'setvolt', 'full', 2, 'KeysightE36312A', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Voltage_3', 'Set voltage on Leach board', '18', 'setvolt', 'full', 3, 'KeysightE36312A', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_1', 'Current on Amplifer+ board', '18', 'current', 'full', 1, 'KeysightE36312A', 'A', NULL, 0.02, 0.04, 0, 0, 0, 0, 0, 0, 0, 1, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_2', 'Current on Amplifer- board', '18', 'current', 'full', 2, 'KeysightE36312A', 'A', NULL, 0.02, 0.04, 0, 0, 0, 0, 0, 0, 0, 1, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_3', 'Current on Leach board', '18', 'current', 'full', 3, 'KeysightE36312A', 'A', NULL, 0.024, 0.05, 0, 0, 0, 0, 0, 0, 0, 1, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Rotation_speed', 'Rotation Speed of Pump', '9', 'actualspd', 'full', 0, 'HiCube80', 'rpm', NULL, 78000, 0, 1, 0, 0, 0, 0, 0, 0, 30, -1, 0, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Pump_OffOn', 'Set Pump to HiCube On/Off', '9', 'pumpgstatn', 'full', 0, 'HiCube80', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 30, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Turbo_OffOn', 'Set Turbo Motor to HiCube Flag', '9', 'motorpump', 'full', 0, 'HiCube80', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Inlet_Current', 'Monitor rms current of PDU inlet', '21', 'current', 'full', 0, 'Raritan_PX3', 'A', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 10, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('PDU_7_OnOff', 'Acopians +/-15 V, -30 V (Set PDU Outlet 7 On/Off)', '21', 'outlet', 'full', 7, 'Raritan_PX3', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 5, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 'Was Set PDU Outlet 8 On/Off (Leach2).'),
('PDU_8_OnOff', 'VME crate (Set PDU Outlet 8 On/Off)', '21', 'outlet', 'full', 8, 'Raritan_PX3', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 5, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 'Was Set PDU Outlet 8 On/Off (Leach)'),
('PDU_2_OnOff', 'Set PDU Outlet 2 On/Off (DAQ 1 machine)', '21', 'outlet', 'full', 2, 'Raritan_PX3', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2nd_Current_1', 'Current on 2nd system Amplifer+ board', '22', 'current', 'full', 1, 'KeysightE36312A2', 'A', NULL, 0.02, 0.04, 0, 0, 0, 0, 0, 0, 0, 1, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2nd_Current_2', 'Current on 2nd system Amplifer- board', '22', 'current', 'full', 2, 'KeysightE36312A2', 'A', NULL, 0.02, 0.04, 0, 0, 0, 0, 0, 0, 0, 1, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2nd_Current_3', 'Current on 2nd system Leach board', '22', 'current', 'full', 3, 'KeysightE36312A2', 'A', NULL, 0.024, 0.05, 0, 0, 0, 0, 0, 0, 0, 1, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2ndSet_Voltage_1', 'Set voltage on 2nd system Amplifier+ board', '22', 'setvolt', 'full', 1, 'KeysightE36312A2', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2ndSet_Voltage_2', 'Set voltage on 2nd system Amplifier- board', '22', 'setvolt', 'full', 2, 'KeysightE36312A2', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2ndSet_Voltage_3', 'Set voltage on 2nd system Leach board', '22', 'setvolt', 'full', 3, 'KeysightE36312A2', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2nd_Voltage_1', 'Voltage on 2nd system Amplifier+ board', '22', 'volt', 'full', 1, 'KeysightE36312A2', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2nd_Voltage_2', 'Voltage on 2nd system Amplifier- board', '22', 'volt', 'full', 2, 'KeysightE36312A2', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2nd_Voltage_3', 'Voltage on 2nd system Leach board', '22', 'volt', 'full', 3, 'KeysightE36312A2', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2Voltage_1_OnOff', 'Set voltage on 2nd system Amplifer board On/Off', '22', 'setonoff', 'full', 2, 'KeysightE36312A2', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('2Voltage_3_OnOff', 'Set voltage on 2nd system Leach board On/Off', '22', 'setonoff', 'full', 3, 'KeysightE36312A2', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('PDU_5_OnOff', 'Set PDU Outlet 5 On/Off (Rad7 Startech)', '21', 'outlet', 'full', 5, 'Raritan_PX3', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('PDU_1_OnOff', 'Set PDU Outlet 1 On/Off (Slow Control machine)', '21', 'outlet', 'full', 1, 'Raritan_PX3', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('PDU_4_OnOff', 'Set PDU Outlet 4 On/Off (DAQ 2 machine)', '21', 'outlet', 'full', 4, 'Raritan_PX3', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('PDU_3_OnOff', 'Set PDU Outlet 3 On/Off (PC power strip w switch, ext disk, etc)', '21', 'outlet', 'full', 3, 'Raritan_PX3', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 1, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_u5', 'VME 12V_1', '25', 'outvolt', 'full', 5, 'WIENER_VME', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_u3', 'VME 3.3V', '25', 'outvolt', 'full', 3, 'WIENER_VME', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_u1', 'VME 12V_0', '25', 'outvolt', 'full', 1, 'WIENER_VME', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Voltage_u0', 'VME 5V', '25', 'outvolt', 'full', 0, 'WIENER_VME', 'V', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Set_Switch_Main', 'Set VME crate on/off', '25', 'setmain', 'full', 0, 'WIENER_VME', 'discrete', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 1, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Fan_Temp', 'VME fan air temperature', '25', 'fantemp', 'full', 0, 'WIENER_VME', 'degC', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_u5', 'VME 12V_1', '25', 'outcurr', 'full', 5, 'WIENER_VME', 'A', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_u3', 'VME 3.3V', '25', 'outcurr', 'full', 3, 'WIENER_VME', 'A', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_u1', 'VME 12V_0', '25', 'outcurr', 'full', 1, 'WIENER_VME', 'A', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Current_u0', 'VME 5V', '25', 'outcurr', 'full', 0, 'WIENER_VME', 'A', NULL, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL),
('Status', 'Status VME crate on/off', '25', 'status', 'full', 0, 'WIENER_VME', 'units', '0:1;Off:On', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, -1, 0, 0, 0, 60, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sc_sensor_types`
--

CREATE TABLE `sc_sensor_types` (
  `num` int(11) NOT NULL,
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `sc_sensor_types`
--

INSERT INTO `sc_sensor_types` (`num`, `name`) VALUES
(21, 'PDU'),
(16, 'Temperature'),
(8, 'Cryocooler'),
(9, 'Pressure'),
(18, 'PowerSupply'),
(19, 'UPS'),
(22, '2ndPowerSupply'),
(23, 'Sys'),
(24, 'Radon'),
(25, 'VMECrate');

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2ndSet_Voltage_1`
--

CREATE TABLE `sc_sens_2ndSet_Voltage_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2ndSet_Voltage_2`
--

CREATE TABLE `sc_sens_2ndSet_Voltage_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2ndSet_Voltage_3`
--

CREATE TABLE `sc_sens_2ndSet_Voltage_3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2nd_Current_1`
--

CREATE TABLE `sc_sens_2nd_Current_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2nd_Current_2`
--

CREATE TABLE `sc_sens_2nd_Current_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2nd_Current_3`
--

CREATE TABLE `sc_sens_2nd_Current_3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2nd_Voltage_1`
--

CREATE TABLE `sc_sens_2nd_Voltage_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2nd_Voltage_2`
--

CREATE TABLE `sc_sens_2nd_Voltage_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2nd_Voltage_3`
--

CREATE TABLE `sc_sens_2nd_Voltage_3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2Voltage_1_OnOff`
--

CREATE TABLE `sc_sens_2Voltage_1_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_2Voltage_3_OnOff`
--

CREATE TABLE `sc_sens_2Voltage_3_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Alarm_Light`
--

CREATE TABLE `sc_sens_Alarm_Light` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Alarm_Siren`
--

CREATE TABLE `sc_sens_Alarm_Siren` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Battery_Charge`
--

CREATE TABLE `sc_sens_Battery_Charge` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_1`
--

CREATE TABLE `sc_sens_Current_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_2`
--

CREATE TABLE `sc_sens_Current_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_3`
--

CREATE TABLE `sc_sens_Current_3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_u0`
--

CREATE TABLE `sc_sens_Current_u0` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_u1`
--

CREATE TABLE `sc_sens_Current_u1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_u3`
--

CREATE TABLE `sc_sens_Current_u3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Current_u5`
--

CREATE TABLE `sc_sens_Current_u5` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_dsk_root`
--

CREATE TABLE `sc_sens_dsk_root` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_dsk_shm`
--

CREATE TABLE `sc_sens_dsk_shm` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Fan_Temp`
--

CREATE TABLE `sc_sens_Fan_Temp` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Heater100W_OnOff`
--

CREATE TABLE `sc_sens_Heater100W_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Heater_100W`
--

CREATE TABLE `sc_sens_Heater_100W` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Inlet_Current`
--

CREATE TABLE `sc_sens_Inlet_Current` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_PDU_1_OnOff`
--

CREATE TABLE `sc_sens_PDU_1_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_PDU_2_OnOff`
--

CREATE TABLE `sc_sens_PDU_2_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_PDU_3_OnOff`
--

CREATE TABLE `sc_sens_PDU_3_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_PDU_4_OnOff`
--

CREATE TABLE `sc_sens_PDU_4_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_PDU_5_OnOff`
--

CREATE TABLE `sc_sens_PDU_5_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_PDU_6_OnOff`
--

CREATE TABLE `sc_sens_PDU_6_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_PDU_7_OnOff`
--

CREATE TABLE `sc_sens_PDU_7_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_PDU_8_OnOff`
--

CREATE TABLE `sc_sens_PDU_8_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Power_Output`
--

CREATE TABLE `sc_sens_Power_Output` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Power_Output_AVC`
--

CREATE TABLE `sc_sens_Power_Output_AVC` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Pressure_Gauge`
--

CREATE TABLE `sc_sens_Pressure_Gauge` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Pressure_Gauge_1`
--

CREATE TABLE `sc_sens_Pressure_Gauge_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Pressure_Gauge_2`
--

CREATE TABLE `sc_sens_Pressure_Gauge_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Pump_OffOn`
--

CREATE TABLE `sc_sens_Pump_OffOn` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Ramprate_100W`
--

CREATE TABLE `sc_sens_Ramprate_100W` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Reject_Temp`
--

CREATE TABLE `sc_sens_Reject_Temp` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Rn_Concentration`
--

CREATE TABLE `sc_sens_Rn_Concentration` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Rotation_speed`
--

CREATE TABLE `sc_sens_Rotation_speed` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Runtime`
--

CREATE TABLE `sc_sens_Runtime` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Setpoint_100W`
--

CREATE TABLE `sc_sens_Setpoint_100W` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Cooler`
--

CREATE TABLE `sc_sens_Set_Cooler` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Heater_100W`
--

CREATE TABLE `sc_sens_Set_Heater_100W` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Power_Output`
--

CREATE TABLE `sc_sens_Set_Power_Output` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Ramprate`
--

CREATE TABLE `sc_sens_Set_Ramprate` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Switch_Main`
--

CREATE TABLE `sc_sens_Set_Switch_Main` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Target_Temp`
--

CREATE TABLE `sc_sens_Set_Target_Temp` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Temp_A`
--

CREATE TABLE `sc_sens_Set_Temp_A` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Temp_B`
--

CREATE TABLE `sc_sens_Set_Temp_B` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Voltage_1`
--

CREATE TABLE `sc_sens_Set_Voltage_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Voltage_2`
--

CREATE TABLE `sc_sens_Set_Voltage_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Set_Voltage_3`
--

CREATE TABLE `sc_sens_Set_Voltage_3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Status`
--

CREATE TABLE `sc_sens_Status` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Status_Gauge`
--

CREATE TABLE `sc_sens_Status_Gauge` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Status_Gauge_1`
--

CREATE TABLE `sc_sens_Status_Gauge_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Status_Gauge_2`
--

CREATE TABLE `sc_sens_Status_Gauge_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_TC_onoff`
--

CREATE TABLE `sc_sens_TC_onoff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Temp_A`
--

CREATE TABLE `sc_sens_Temp_A` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Temp_B`
--

CREATE TABLE `sc_sens_Temp_B` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Temp_C`
--

CREATE TABLE `sc_sens_Temp_C` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Temp_Cooler`
--

CREATE TABLE `sc_sens_Temp_Cooler` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Temp_D`
--

CREATE TABLE `sc_sens_Temp_D` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Temp_Target`
--

CREATE TABLE `sc_sens_Temp_Target` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_TurboOff_On`
--

CREATE TABLE `sc_sens_TurboOff_On` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Turbo_OffOn`
--

CREATE TABLE `sc_sens_Turbo_OffOn` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_1`
--

CREATE TABLE `sc_sens_Voltage_1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_1_OnOff`
--

CREATE TABLE `sc_sens_Voltage_1_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_2`
--

CREATE TABLE `sc_sens_Voltage_2` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_3`
--

CREATE TABLE `sc_sens_Voltage_3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_3_OnOff`
--

CREATE TABLE `sc_sens_Voltage_3_OnOff` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_u0`
--

CREATE TABLE `sc_sens_Voltage_u0` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_u1`
--

CREATE TABLE `sc_sens_Voltage_u1` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_u3`
--

CREATE TABLE `sc_sens_Voltage_u3` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `sc_sens_Voltage_u5`
--

CREATE TABLE `sc_sens_Voltage_u5` (
  `time` int(11) NOT NULL,
  `value` double NOT NULL,
  `rate` double NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

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
  `privileges` text COLLATE latin1_general_cs DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_name`, `password`, `full_name`, `affiliation`, `email`, `sms`, `phone`, `on_call`, `shift_status`, `privileges`) VALUES
('guest', '084e0343a0486ff05530df6c705c8bb4', 'guest', 'none', '', '', '', 0, 'Off', 'basic,guest'),
('root', 'fb22ee6ae38d5dd1139dd25579a81bc8', 'root', 'root', 'root', 'root', 'root', 0, 'Off', 'admin,basic,config,full,siren'),
('mtraina', 'f0836261532ee6e6d0423f5a8548a80b', 'Michelangelo Traina', 'IFCA', 'traina@ifca.unican.es', '+393486641155', '+393486641155', 1, 'On Shift', 'admin,basic,config,full'),
('RomainGaior', 'fb22ee6ae38d5dd1139dd25579a81bc8', 'Romain Gaior', 'LPNHE', 'damicatlpnhe@gmail.com', NULL, NULL, 1, 'On Shift', NULL),
('smida', '4ea71f586688055838f4b33717ce6c6e', 'Radomir Smida', 'UChicago', 'smida@kicp.uchicago.edu', '3124836318@tmomail.net', '+13124836318', 1, 'Shift Manager', 'admin,basic,config,full'),
('shifter', 'fb22ee6ae38d5dd1139dd25579a81bc8', 'Shifter', 'DAMIC-M', 'damicm.lbc@gmail.com', NULL, NULL, 1, 'On Shift', 'basic,config,full'),
('priviter', '66714e088d7f4fadb41d2605be75396d', 'Paolo Privitera', 'UChicago/LPNHE', 'priviter@astro.uchicago.edu', '7733229331@tmomail.net', '+17733229391', 0, 'On Shift', 'admin,basic,config,full'),
('dvenegas', 'e99a18c428cb38d5f260853678922e03', 'Diego Venegas', NULL, NULL, NULL, NULL, 0, 'Off', 'basic,config,full'),
('PaoloPrivitera2', '66714e088d7f4fadb41d2605be75396d', 'Paolo Privitera', 'UChicago/LPNHE', 'priviterp@gmail.com', NULL, NULL, 1, 'On Shift', NULL),
('dnorcini', '30d3016ed2b8e3e62f1cdb9b124a1347', 'Danielle Norcini', 'Hopkins', 'dnorcini@jhu.edu', '6105059248@tmomail.net', '+16105059248', 0, 'System Manager', 'admin,basic,config,full'),
('sravan', 'e99a18c428cb38d5f260853678922e03', 'Sravan Munagavalasa', 'UChicago', 'sravan@UCHICAGO.EDU', NULL, NULL, 1, 'On Shift', 'admin,basic,config,full'),
('cdedomin', '2b9ff3efc4a999ecfacd18c4bbc57a2e', 'Claudia De Dominicis', 'LPNHE', 'cdedomin@lpnhe.in2p3.fr', NULL, NULL, 1, 'On Shift', 'basic,config,full'),
('rachana', 'fb22ee6ae38d5dd1139dd25579a81bc8', 'Rachana Yajur ', 'UChicago', 'rachanayajur@uchicago.edu', NULL, NULL, 0, 'On Shift', 'basic,config,full'),
('alidfard', 'e99a18c428cb38d5f260853678922e03', 'Ali Dastgheibi Fard', 'LPSC', 'dastgheibi@lpsc.in2p3.fr', NULL, NULL, 0, 'Off', 'basic,full,guest');

-- --------------------------------------------------------

--
-- Table structure for table `user_privileges`
--

CREATE TABLE `user_privileges` (
  `u_p_indx` int(11) NOT NULL,
  `name` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `allowed_host` varchar(16) COLLATE latin1_general_cs NOT NULL DEFAULT 'all'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `user_privileges`
--

INSERT INTO `user_privileges` (`u_p_indx`, `name`, `allowed_host`) VALUES
(1, 'guest', 'all'),
(2, 'basic', 'all'),
(3, 'full', 'all'),
(4, 'admin', 'all'),
(5, 'config', 'all');

-- --------------------------------------------------------

--
-- Table structure for table `user_shift_status`
--

CREATE TABLE `user_shift_status` (
  `status` varchar(16) COLLATE latin1_general_cs NOT NULL,
  `can_manage` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_cs;

--
-- Dumping data for table `user_shift_status`
--

INSERT INTO `user_shift_status` (`status`, `can_manage`) VALUES
('Off', 0),
('Shift Leader', 1),
('Shift Manager', 1),
('On Shift', 0),
('System Manager', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daq_control`
--
ALTER TABLE `daq_control`
  ADD PRIMARY KEY (`entry_id`);

--
-- Indexes for table `daq_control_tmp`
--
ALTER TABLE `daq_control_tmp`
  ADD KEY `utc` (`utc`);

--
-- Indexes for table `daq_presets`
--
ALTER TABLE `daq_presets`
  ADD PRIMARY KEY (`entry_id`);

--
-- Indexes for table `dqm_channel_data`
--
ALTER TABLE `dqm_channel_data`
  ADD KEY `time` (`utc`);

--
-- Indexes for table `dqm_control`
--
ALTER TABLE `dqm_control`
  ADD KEY `utc_time` (`utc_time`);

--
-- Indexes for table `dqm_event_data`
--
ALTER TABLE `dqm_event_data`
  ADD KEY `utc_time` (`utc_time`);

--
-- Indexes for table `dqm_event_display`
--
ALTER TABLE `dqm_event_display`
  ADD KEY `utc_time` (`utc`);

--
-- Indexes for table `globals`
--
ALTER TABLE `globals`
  ADD UNIQUE KEY `names` (`name`);

--
-- Indexes for table `lug_categories`
--
ALTER TABLE `lug_categories`
  ADD PRIMARY KEY (`l_c_indx`);

--
-- Indexes for table `lug_entries`
--
ALTER TABLE `lug_entries`
  ADD PRIMARY KEY (`entry_id`);

--
-- Indexes for table `msg_log`
--
ALTER TABLE `msg_log`
  ADD PRIMARY KEY (`msg_id`);

--
-- Indexes for table `msg_log_types`
--
ALTER TABLE `msg_log_types`
  ADD UNIQUE KEY `name` (`types`);

--
-- Indexes for table `runs`
--
ALTER TABLE `runs`
  ADD UNIQUE KEY `num_2` (`num`),
  ADD KEY `num` (`num`);

--
-- Indexes for table `sc_insts`
--
ALTER TABLE `sc_insts`
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sc_sensors`
--
ALTER TABLE `sc_sensors`
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `sc_sensor_types`
--
ALTER TABLE `sc_sensor_types`
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `num` (`num`);

--
-- Indexes for table `sc_sens_2ndSet_Voltage_1`
--
ALTER TABLE `sc_sens_2ndSet_Voltage_1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_2ndSet_Voltage_2`
--
ALTER TABLE `sc_sens_2ndSet_Voltage_2`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_2ndSet_Voltage_3`
--
ALTER TABLE `sc_sens_2ndSet_Voltage_3`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_2nd_Current_1`
--
ALTER TABLE `sc_sens_2nd_Current_1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_2nd_Current_2`
--
ALTER TABLE `sc_sens_2nd_Current_2`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_2nd_Current_3`
--
ALTER TABLE `sc_sens_2nd_Current_3`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_2nd_Voltage_1`
--
ALTER TABLE `sc_sens_2nd_Voltage_1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_2nd_Voltage_2`
--
ALTER TABLE `sc_sens_2nd_Voltage_2`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_2nd_Voltage_3`
--
ALTER TABLE `sc_sens_2nd_Voltage_3`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_2Voltage_1_OnOff`
--
ALTER TABLE `sc_sens_2Voltage_1_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_2Voltage_3_OnOff`
--
ALTER TABLE `sc_sens_2Voltage_3_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Alarm_Light`
--
ALTER TABLE `sc_sens_Alarm_Light`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Alarm_Siren`
--
ALTER TABLE `sc_sens_Alarm_Siren`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Battery_Charge`
--
ALTER TABLE `sc_sens_Battery_Charge`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_1`
--
ALTER TABLE `sc_sens_Current_1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_2`
--
ALTER TABLE `sc_sens_Current_2`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_3`
--
ALTER TABLE `sc_sens_Current_3`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_u0`
--
ALTER TABLE `sc_sens_Current_u0`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_u1`
--
ALTER TABLE `sc_sens_Current_u1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_u3`
--
ALTER TABLE `sc_sens_Current_u3`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Current_u5`
--
ALTER TABLE `sc_sens_Current_u5`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_dsk_root`
--
ALTER TABLE `sc_sens_dsk_root`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_dsk_shm`
--
ALTER TABLE `sc_sens_dsk_shm`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Fan_Temp`
--
ALTER TABLE `sc_sens_Fan_Temp`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Heater100W_OnOff`
--
ALTER TABLE `sc_sens_Heater100W_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Heater_100W`
--
ALTER TABLE `sc_sens_Heater_100W`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Inlet_Current`
--
ALTER TABLE `sc_sens_Inlet_Current`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_PDU_1_OnOff`
--
ALTER TABLE `sc_sens_PDU_1_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_PDU_2_OnOff`
--
ALTER TABLE `sc_sens_PDU_2_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_PDU_3_OnOff`
--
ALTER TABLE `sc_sens_PDU_3_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_PDU_4_OnOff`
--
ALTER TABLE `sc_sens_PDU_4_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_PDU_5_OnOff`
--
ALTER TABLE `sc_sens_PDU_5_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_PDU_6_OnOff`
--
ALTER TABLE `sc_sens_PDU_6_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_PDU_7_OnOff`
--
ALTER TABLE `sc_sens_PDU_7_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_PDU_8_OnOff`
--
ALTER TABLE `sc_sens_PDU_8_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Power_Output`
--
ALTER TABLE `sc_sens_Power_Output`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Power_Output_AVC`
--
ALTER TABLE `sc_sens_Power_Output_AVC`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Pressure_Gauge`
--
ALTER TABLE `sc_sens_Pressure_Gauge`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Pressure_Gauge_1`
--
ALTER TABLE `sc_sens_Pressure_Gauge_1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Pressure_Gauge_2`
--
ALTER TABLE `sc_sens_Pressure_Gauge_2`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Pump_OffOn`
--
ALTER TABLE `sc_sens_Pump_OffOn`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Ramprate_100W`
--
ALTER TABLE `sc_sens_Ramprate_100W`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Reject_Temp`
--
ALTER TABLE `sc_sens_Reject_Temp`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Rn_Concentration`
--
ALTER TABLE `sc_sens_Rn_Concentration`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Rotation_speed`
--
ALTER TABLE `sc_sens_Rotation_speed`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Runtime`
--
ALTER TABLE `sc_sens_Runtime`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Setpoint_100W`
--
ALTER TABLE `sc_sens_Setpoint_100W`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Cooler`
--
ALTER TABLE `sc_sens_Set_Cooler`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Heater_100W`
--
ALTER TABLE `sc_sens_Set_Heater_100W`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Power_Output`
--
ALTER TABLE `sc_sens_Set_Power_Output`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Ramprate`
--
ALTER TABLE `sc_sens_Set_Ramprate`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Switch_Main`
--
ALTER TABLE `sc_sens_Set_Switch_Main`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Target_Temp`
--
ALTER TABLE `sc_sens_Set_Target_Temp`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Temp_A`
--
ALTER TABLE `sc_sens_Set_Temp_A`
  ADD KEY `time` (`time`) USING BTREE;

--
-- Indexes for table `sc_sens_Set_Temp_B`
--
ALTER TABLE `sc_sens_Set_Temp_B`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Voltage_1`
--
ALTER TABLE `sc_sens_Set_Voltage_1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Voltage_2`
--
ALTER TABLE `sc_sens_Set_Voltage_2`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Set_Voltage_3`
--
ALTER TABLE `sc_sens_Set_Voltage_3`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Status`
--
ALTER TABLE `sc_sens_Status`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Status_Gauge`
--
ALTER TABLE `sc_sens_Status_Gauge`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Status_Gauge_1`
--
ALTER TABLE `sc_sens_Status_Gauge_1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Status_Gauge_2`
--
ALTER TABLE `sc_sens_Status_Gauge_2`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_TC_onoff`
--
ALTER TABLE `sc_sens_TC_onoff`
  ADD KEY `time` (`time`) USING BTREE;

--
-- Indexes for table `sc_sens_Temp_A`
--
ALTER TABLE `sc_sens_Temp_A`
  ADD KEY `time` (`time`) USING BTREE;

--
-- Indexes for table `sc_sens_Temp_B`
--
ALTER TABLE `sc_sens_Temp_B`
  ADD KEY `time` (`time`) USING BTREE;

--
-- Indexes for table `sc_sens_Temp_C`
--
ALTER TABLE `sc_sens_Temp_C`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Temp_Cooler`
--
ALTER TABLE `sc_sens_Temp_Cooler`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Temp_D`
--
ALTER TABLE `sc_sens_Temp_D`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Temp_Target`
--
ALTER TABLE `sc_sens_Temp_Target`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_TurboOff_On`
--
ALTER TABLE `sc_sens_TurboOff_On`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Turbo_OffOn`
--
ALTER TABLE `sc_sens_Turbo_OffOn`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_1`
--
ALTER TABLE `sc_sens_Voltage_1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_1_OnOff`
--
ALTER TABLE `sc_sens_Voltage_1_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_2`
--
ALTER TABLE `sc_sens_Voltage_2`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_3`
--
ALTER TABLE `sc_sens_Voltage_3`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_3_OnOff`
--
ALTER TABLE `sc_sens_Voltage_3_OnOff`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_u0`
--
ALTER TABLE `sc_sens_Voltage_u0`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_u1`
--
ALTER TABLE `sc_sens_Voltage_u1`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_u3`
--
ALTER TABLE `sc_sens_Voltage_u3`
  ADD KEY `time` (`time`);

--
-- Indexes for table `sc_sens_Voltage_u5`
--
ALTER TABLE `sc_sens_Voltage_u5`
  ADD KEY `time` (`time`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `username` (`user_name`);

--
-- Indexes for table `user_privileges`
--
ALTER TABLE `user_privileges`
  ADD UNIQUE KEY `index` (`u_p_indx`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `user_shift_status`
--
ALTER TABLE `user_shift_status`
  ADD UNIQUE KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `daq_control`
--
ALTER TABLE `daq_control`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daq_presets`
--
ALTER TABLE `daq_presets`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lug_categories`
--
ALTER TABLE `lug_categories`
  MODIFY `l_c_indx` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `lug_entries`
--
ALTER TABLE `lug_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `msg_log`
--
ALTER TABLE `msg_log`
  MODIFY `msg_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `runs`
--
ALTER TABLE `runs`
  MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sc_sensor_types`
--
ALTER TABLE `sc_sensor_types`
  MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `user_privileges`
--
ALTER TABLE `user_privileges`
  MODIFY `u_p_indx` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
