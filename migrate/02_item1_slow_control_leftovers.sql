-- Item 1 (REFACTOR_ISSUES.md §2.5): leftovers from the slow-control system.
-- Nothing in legacyAPP or targetAPP reads these.
-- Applied to the Docker DB on 2026-10-04; run again on production at cut-over.

USE die_qc;

DELETE FROM globals
 WHERE name IN ('have_Cams', 'have_HV_crate', 'have_LB', 'have_RGA', 'have_TS', 'Master_alarm');

DROP TABLE user_shift_status;
