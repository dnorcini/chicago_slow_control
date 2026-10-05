-- Fix a mistyped year: die 33 (09_19_2024_batch_2_C) has Test_Date 2924-09-19.
-- Its name and its history entry ("testing", 2024-09-19) show it was 2024-09-19.
-- Safe to run again: only changes the row while it still holds the wrong date.
-- Applied to the Docker DB on 2026-10-04; run on production before the item-2 cut-over.
-- Snapshot taken before: snapshots/die_qc-pre-03-fix-date-2026-10-04.sql

USE die_qc;

UPDATE DIE SET Test_Date = '2024-09-19' WHERE id = 33 AND Test_Date = '2924-09-19';
