-- Item 1 removals that are confirmed but NOT applied yet, because the legacy
-- app (:8056) on the shared DB still depends on them. targetAPP no longer uses
-- them. Apply together with the item-2 migration / cut-over.
-- NOT applied as of 2026-10-04.

USE die_qc;

-- §2.4 module reviewer checkboxes (legacy writes Check_* on every module save)
ALTER TABLE MODULE_SURFACE
  DROP COLUMN Check_A, DROP COLUMN Check_B, DROP COLUMN Check_C, DROP COLUMN Check_D;
ALTER TABLE MODULE_UNDERGROUND2
  DROP COLUMN Check_A, DROP COLUMN Check_B, DROP COLUMN Check_C, DROP COLUMN Check_D;
