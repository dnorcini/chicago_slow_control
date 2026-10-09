-- Item 2: the new ccdqc schema.
-- Loaded by migrate/migrate.php, which drops and recreates these tables.
-- Items in time order: ccd, die, module.

CREATE DATABASE IF NOT EXISTS ccdqc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ccdqc;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS measurement, test_info, ccd, die, module, history, users, user_privileges;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE ccd (                                 -- pre-production CCD
  id                  INT AUTO_INCREMENT PRIMARY KEY,  -- = old CCD.ID
  name                VARCHAR(64),
  ccd_type            VARCHAR(32),
  size                VARCHAR(16),
  status              VARCHAR(32),
  location            VARCHAR(64),
  wafer_id            VARCHAR(64),
  wafer_position      VARCHAR(64),
  production_date     VARCHAR(32),                 -- free text today ("02/23/2021", "2020/07")
  packager            VARCHAR(64),
  packaging_date      VARCHAR(32),
  cable_np            TINYINT(1) NOT NULL DEFAULT 0,
  jfet_u1             TINYINT(1) NOT NULL DEFAULT 0,
  jfet_l1             TINYINT(1) NOT NULL DEFAULT 0,
  jfet_u2             TINYINT(1) NOT NULL DEFAULT 0,
  jfet_l2             TINYINT(1) NOT NULL DEFAULT 0,
  glue_humid          DECIMAL(10,2),
  glue_temp           DECIMAL(10,2),
  glue_radon          DECIMAL(10,2),
  gluing_details      TEXT,
  wb_humid            DECIMAL(10,2),
  wb_temp             DECIMAL(10,2),
  wb_radon            DECIMAL(10,2),
  wb_power            DECIMAL(10,2),
  wb_time             DECIMAL(10,2),
  wb_date             VARCHAR(32),
  wirebonding_details TEXT,
  note                TEXT,
  updated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE die (
  id             INT AUTO_INCREMENT PRIMARY KEY,   -- = old DIE.id
  name           VARCHAR(64),
  status         VARCHAR(32),                      -- Not Tested / Tested / Failed
  wafer_id       VARCHAR(64),
  wafer_position VARCHAR(8),
  activation     INT,
  humidity       DECIMAL(10,2),
  radon          DECIMAL(10,2),
  module_id      INT NULL,                         -- module it was packaged into; NULL = none
  module_pos     CHAR(1) NULL,                     -- A..D
  amp            VARCHAR(4),                       -- L1/L2/U1/U2
  channel        VARCHAR(8),                       -- ch0..ch3
  updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY one_die_per_pos (module_id, module_pos)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE module (
  id         INT AUTO_INCREMENT PRIMARY KEY,       -- = old surface/underground id
  name       VARCHAR(64),                          -- DM-01 ... PD-08
  status     VARCHAR(32),
  activation INT,
  humidity   DECIMAL(10,2),
  radon      DECIMAL(10,2),
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- die is created before module (time order), so its module FK is added here
ALTER TABLE die ADD FOREIGN KEY (module_id) REFERENCES module(id);

CREATE TABLE test_info (
  id                INT AUTO_INCREMENT PRIMARY KEY,
  item_type         ENUM('ccd','die','module') NOT NULL,
  item_id           INT NOT NULL,                  -- ccd.id / die.id / module.id, by item_type
  stage             VARCHAR(32) NOT NULL,          -- protocol key in protocol.php: ccd, die, surface, underground
  test_number       TINYINT NOT NULL DEFAULT 1,    -- 1, 2, ... per item, across all stages
  tester            VARCHAR(128),
  test_date         DATE,
  test_time         TIME,
  chamber           VARCHAR(16),
  acm               VARCHAR(16),
  feedthru_position VARCHAR(8),
  script            VARCHAR(255),
  reviewer          VARCHAR(64),
  notes             TEXT,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY one_test (item_type, item_id, test_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE measurement (
  test_id    INT NOT NULL,
  section    VARCHAR(16) NOT NULL,             -- 'run', 'amp', 'trace', 'img4', 'img31', ...
  temp       VARCHAR(8)  NOT NULL DEFAULT '',  -- 'low' / 'high' / '' (stage uses one temperature)
  pos        VARCHAR(4)  NOT NULL DEFAULT '',  -- A..D / U1..L2 / 'AB' (crosstalk) / '' (whole test or image)
  metric     VARCHAR(32) NOT NULL,             -- 'noise', 'peak1', 'grade', ...
  value_num  DOUBLE NULL,
  value_err  DOUBLE NULL,
  value_text TEXT NULL,
  PRIMARY KEY (test_id, section, temp, pos, metric),
  KEY section_metric (section, temp, metric),
  FOREIGN KEY (test_id) REFERENCES test_info(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Copied from die_qc. Same columns as today, minus the item-1 deferred ones.

CREATE TABLE history (
  entry       INT AUTO_INCREMENT PRIMARY KEY,
  type        VARCHAR(50) NOT NULL COMMENT 'ccd | die | module',
  sub_id      INT NOT NULL,
  date        DATE,
  action      VARCHAR(255),
  location    VARCHAR(255),
  description VARCHAR(255),
  reviewer    VARCHAR(100),
  Last_update TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_type_subid (type, sub_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  user_name   VARCHAR(32) COLLATE utf8mb4_bin NOT NULL PRIMARY KEY,  -- case-sensitive, as today
  password    VARCHAR(255),                  -- holds today's MD5; room for password_hash() (item 3)
  full_name   VARCHAR(64),
  affiliation VARCHAR(64),
  email       VARCHAR(64),
  privileges  TEXT                           -- 'basic,full', 'admin,basic,full', ...
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_privileges (
  u_p_indx INT AUTO_INCREMENT PRIMARY KEY,
  name     VARCHAR(16) NOT NULL,             -- guest / basic / full / admin
  KEY name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
