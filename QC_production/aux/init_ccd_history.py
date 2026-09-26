#!/usr/bin/env python3
"""
init_ccd_history.py
Seed die_qc.history with one 'packaging' entry per pre-production CCD
that has a non-empty Packaging_date.

Expected formats: MM/YYYY, MM/DD/YYYY, YYYY/MM/DD (and common variants).
Run once; skips CCDs that already have a packaging history entry.
"""

import re
import sys
import pymysql
from datetime import date

DB = dict(host='localhost', user='control_user', password='MyLife4AiurUser', db='die_qc')


def parse_date(raw):
    s = raw.strip()
    if not s:
        return None

    parts = re.split(r'[/\-]', s)

    if len(parts) == 2:
        a, b = parts
        # MM/YYYY
        if len(b) == 4 and b.isdigit() and a.isdigit():
            try:
                m, y = int(a), int(b)
                if 1 <= m <= 12:
                    return date(y, m, 1)
            except ValueError:
                pass
        # YYYY/MM
        if len(a) == 4 and a.isdigit() and b.isdigit():
            try:
                y, m = int(a), int(b)
                if 1 <= m <= 12:
                    return date(y, m, 1)
            except ValueError:
                pass

    elif len(parts) == 3:
        a, b, c = parts
        # YYYY/MM/DD  or  YYYY/DD/MM (fallback when month part > 12)
        if len(a) == 4 and a.isdigit():
            try:
                y, x, z = int(a), int(b), int(c)
                if 1 <= x <= 12 and 1 <= z <= 31:          # YYYY/MM/DD
                    return date(y, x, z)
                elif 1 <= z <= 12 and 1 <= x <= 31:         # YYYY/DD/MM
                    return date(y, z, x)
            except ValueError:
                pass
        # MM/DD/YYYY  or  M/D/YYYY
        if len(c) == 4 and c.isdigit():
            try:
                m, d, y = int(a), int(b), int(c)
                if 1 <= m <= 12 and 1 <= d <= 31:
                    return date(y, m, d)
            except ValueError:
                pass

    return None


def main():
    db = pymysql.connect(**DB)
    cur = db.cursor()

    cur.execute("""
        SELECT ID, Name, Packaging_date
        FROM CCD
        WHERE Packaging_date IS NOT NULL AND Packaging_date != ''
        ORDER BY ID
    """)
    rows = cur.fetchall()

    inserted, skipped, errors = 0, 0, []

    for ccd_id, name, raw in rows:
        # Avoid duplicates
        cur.execute(
            "SELECT COUNT(*) FROM history WHERE type='ccd' AND sub_id=%s AND action='packaging'",
            (ccd_id,)
        )
        if cur.fetchone()[0] > 0:
            print(f"  SKIP  ID={ccd_id:3d}  {name}  (already exists)")
            skipped += 1
            continue

        parsed = parse_date(raw)
        if parsed is None:
            print(f"  ERROR ID={ccd_id:3d}  {name}  raw='{raw}'  (could not parse)")
            errors.append((ccd_id, name, raw))
            continue

        cur.execute(
            """INSERT INTO history (type, sub_id, date, action, location, reviewer)
               VALUES ('ccd', %s, %s, 'packaging', 'UW', 'Cinyu')""",
            (ccd_id, parsed.strftime('%Y-%m-%d'))
        )
        print(f"  INSERT ID={ccd_id:3d}  {name}  '{raw}' -> {parsed}")
        inserted += 1

    db.commit()
    cur.close()
    db.close()

    print(f"\nDone: {inserted} inserted, {skipped} skipped, {len(errors)} unparseable")
    if errors:
        print("\nCould not parse — fix these manually:")
        for ccd_id, name, raw in errors:
            print(f"  ID={ccd_id}  {name}  raw='{raw}'")
    return 1 if errors else 0


if __name__ == '__main__':
    sys.exit(main())
