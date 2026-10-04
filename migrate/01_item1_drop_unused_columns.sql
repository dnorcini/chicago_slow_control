-- Item 1 (REFACTOR_ISSUES.md §2.3, §2.4): drop columns that are no longer used.
-- Safe for the legacy app as well: legacy only reads these with isset() and
-- never writes them unless a user types into the (now removed) fields.
-- Applied to the Docker DB on 2026-10-04; run again on production at cut-over.
-- Snapshot taken before: snapshots/die_qc+assay_qc-pre-item1-2026-10-04.sql

USE die_qc;

-- §2.4 Pitch adaptor ID (never filled), *_File columns (uploads are found on disk)
ALTER TABLE MODULE_SURFACE
  DROP COLUMN Pitch_Adaptor_ID,
  DROP COLUMN Trace_High_File,
  DROP COLUMN Image1_Low_File,  DROP COLUMN Image1_High_File,
  DROP COLUMN Image2_Low_File,  DROP COLUMN Image2_High_File,
  DROP COLUMN Image3_Low_File,  DROP COLUMN Image3_High_File,
  DROP COLUMN Image4_Low_File,  DROP COLUMN Image4_High_File,
  DROP COLUMN Image5_Low_File,  DROP COLUMN Image5_High_File;

-- §2.4 underground Pitch adaptor / Activation / Humidity / Radon (never filled), Trace file
-- §2.3 duplicate reference columns: the form only uses _A; B-D equal A or hold
--      the wildcard pattern of A's file list (e.g. avg_Image_4_High_Temp_110_*_*_*.fz)
ALTER TABLE MODULE_UNDERGROUND2
  DROP COLUMN Pitch_Adaptor_ID,
  DROP COLUMN Activation,
  DROP COLUMN Humidity,
  DROP COLUMN Radon,
  DROP COLUMN Trace_High_File,
  DROP COLUMN Image1_High_Reference_B, DROP COLUMN Image1_High_Reference_C, DROP COLUMN Image1_High_Reference_D,
  DROP COLUMN Image2_High_Reference_B, DROP COLUMN Image2_High_Reference_C, DROP COLUMN Image2_High_Reference_D,
  DROP COLUMN Image4_High_Reference_B, DROP COLUMN Image4_High_Reference_C, DROP COLUMN Image4_High_Reference_D,
  DROP COLUMN Image4_Low_Reference_B,  DROP COLUMN Image4_Low_Reference_C,  DROP COLUMN Image4_Low_Reference_D,
  DROP COLUMN Image5_Low_Reference_B,  DROP COLUMN Image5_Low_Reference_C,  DROP COLUMN Image5_Low_Reference_D;

-- §2.4 DIE file columns (2 rows held old absolute paths; uploads are found on disk)
ALTER TABLE DIE
  DROP COLUMN Trace_File,
  DROP COLUMN Image1_File, DROP COLUMN Image2_File, DROP COLUMN Image3_File,
  DROP COLUMN Image4_File, DROP COLUMN Image5_File, DROP COLUMN Image6_File;
