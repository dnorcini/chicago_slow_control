# use: python3 energypeaks.py --id 19 --csvpath MODULE_UNDERGROUND2.csv
# Plot energy peaks over the modules
# Cinyu Zhu, JHU, Sept 2025

# todo: move the script to ccdqc server and make it automatic

# ## contents
# - prepare a function to convert a string of fitted result to [center, error], and also for the case of one number/NULL
# - prepare a numpy array[module, ccd, image4-7, peak, content/error] (shape = 28 * 4 * 4 * 2 * 2)
# - prepare a function to query csv given a id + column name 
# - query the sql db according to the column name assembled from the above, given specific indiced
# - fill the numpy array with numbers
# - plot

### csv is obtained from exporting csv from phpMyAdmin (direct connection to the database is not allowed)
# ````
# SELECT id, Name, Image4_Low_Peak1_A, Image4_Low_Peak1_B, Image4_Low_Peak1_C, Image4_Low_Peak1_D, Image4_Low_Peak2_A, Image4_Low_Peak2_B, Image4_Low_Peak2_C, Image4_Low_Peak2_D, Image5_Low_Peak1_A, Image5_Low_Peak1_B, Image5_Low_Peak1_C, Image5_Low_Peak1_D, Image5_Low_Peak2_A, Image5_Low_Peak2_B, Image5_Low_Peak2_C, Image5_Low_Peak2_D, Image6_Low_Peak1_A, Image6_Low_Peak1_B, Image6_Low_Peak1_C, Image6_Low_Peak1_D, Image6_Low_Peak2_A, Image6_Low_Peak2_B, Image6_Low_Peak2_C, Image6_Low_Peak2_D, Image7_Low_Peak1_A, Image7_Low_Peak1_B, Image7_Low_Peak1_C, Image7_Low_Peak1_D, Image7_Low_Peak2_A, Image7_Low_Peak2_B, Image7_Low_Peak2_C, Image7_Low_Peak2_D
# FROM MODULE_UNDERGROUND2
# ````

import numpy as np
import pandas as pd
import matplotlib.pyplot as plt
import argparse
import pymysql
import os



def fetch_columns_from_db(columns, 
                          host="localhost", 
                          user="myuser", 
                          password="mypassword", 
                          database="mydb", 
                          table="MODULE_SURFACE", 
                          id_column="id"):
    """
    Fetch specific columns for entries with id=1..28 from a MySQL/MariaDB database.
    
    Parameters
    ----------
    columns : list of str
        Column names to fetch.
    host, user, password, database : str
        Database connection settings.
    table : str
        Table name.
    id_column : str
        Primary key column name (default "id").
    
    Returns
    -------
    pd.DataFrame
    """
    # Ensure columns are properly formatted for SQL
    cols = ", ".join([f"`{c}`" for c in columns])
    query = f"""
        SELECT {cols}
        FROM `{table}`
        ORDER BY `{id_column}`;
    """
    
    # Connect and fetch
    conn = pymysql.connect(host=host, user=user, password=password, database=database)
    try:
        df = pd.read_sql(query, conn)
    finally:
        conn.close()
    
    return df



def parse_value_error(s: str) -> np.ndarray:
    """Parse 'x +/- y', single number, or empty → [x, y] with -1 fallbacks."""
    if s is None:
        return np.array([0, 0])
    if isinstance(s, float) and np.isnan(s):
        return np.array([0, 0])
    s = str(s).strip()
    if s == "":
        return np.array([0, 0])
    if "+/-" in s:
        try:
            left, right = s.split("+/-", 1)
            return np.array([float(left.strip()), float(right.strip())], dtype=float)
        except Exception:
            print(f"Error parsing string: {s}")
            return np.array([0, 0])
    try:
        return np.array([float(s), 0], dtype=float)
    except Exception:
        print(f"Error parsing string: {s}")
        return np.array([0, 0])
    
def build_array_from_db(
    table = "MODULE_UNDERGROUND2",
    columns=["id", "Image4_Low_Peak1_A", "Image4_Low_Peak1_B", "Image4_Low_Peak1_C", "Image4_Low_Peak1_D", 
             "Image4_Low_Peak2_A", "Image4_Low_Peak2_B", "Image4_Low_Peak2_C", "Image4_Low_Peak2_D"],
    images=(4,5,6),
    peaks=(1,2),
    ccd_letters=("A","B","C","D"),
    fill_value: float = 0,) -> np.ndarray:
    """
    Build array with shape (28, 4, 4, 2, 2) ordered as:
    [module(0..27), ccd(0..3), image_idx(0..3 for 4..7), peak(0..1 for 1..2), content/error].
    must have columns named like: 'Image{4..7}_Low_Peak{1|2}_{A|B|C|D}'.
    Also requires an 'id' column giving module numbers 1..28.
    """
    
    df = fetch_columns_from_db(
    columns,
    user="root", password="MyLife4Aiur", database="die_qc", table=table)
    if "id" not in df.columns:
        raise ValueError("CSV must contain a column named 'id' with values 1..28")

    # output array
    # [module(0..27), ccd(0..3), image_idx(0..3 for 4..7), peak(0..1 for 1..2), content/error].

    arr = np.full((len(df), 4, len(images), 2, 2), fill_value, dtype=float)

    # precompute index maps
    img_to_axis = {img: i for i, img in enumerate(images)}
    ccd_to_axis = {letter: i for i, letter in enumerate(ccd_letters)}
    peak_to_axis = {p: i for i, p in enumerate(peaks)}

    # verify expected columns exist; warn if not
    expected_cols = []
    for img in images:
        for p in peaks:
            for c in ccd_letters:
                expected_cols.append(f"Image{img}_Low_Peak{p}_{c}")
    missing = [c for c in expected_cols if c not in df.columns]
    if missing:
        print(f"Warning: {len(missing)} expected columns not found; examples: {missing[:5]}")

    # fill array
    for _, row in df.iterrows():
        try:
            mval = int(row["id"])
            m_idx = mval - 1
        except Exception:
            continue

        for img in images:
            i_idx = img_to_axis[img]
            for p in peaks:
                p_idx = peak_to_axis[p]
                for c in ccd_letters:
                    c_idx = ccd_to_axis[c]
                    col = f"Image{img}_Low_Peak{p}_{c}"
                    if col not in df.columns:
                        continue
                    parsed = parse_value_error(row[col])
                    arr[m_idx, c_idx, i_idx, p_idx, 0] = parsed[0]
                    arr[m_idx, c_idx, i_idx, p_idx, 1] = parsed[1]

    return arr



def plot_module_energy_peaks(
    arr: np.ndarray,
    module_number: int = 20,
    images=(4, 5, 6),
    peaks=(1, 2),
    *,
    surface_arr: np.ndarray = None,
    underground_marker: str = "o",
    surface_marker: str = "D",
):
    """
    Plot energy peaks with error bars for a given module.
    """

    m_idx = module_number - 1
    x = np.arange(4)  # ext 0..3
    x_labels = [f"EXT{i}" for i in range(1, 5)]

    # style maps
    default_marker_map = {4: "o", 5: "s", 6: "^", 7: "v"}
    color_map = {1: "red", 2: "blue"}

    plt.figure(figsize=(9, 6))

    # reference lines with labels
    plt.axhline(y=5.89, color=color_map[1], linestyle="--", label=r"$K_{\alpha} \ 5.89 \mathrm{KeV}$")
    plt.axhline(y=6.49, color=color_map[2], linestyle="--", label=r"$K_{\beta} \ 6.49 \mathrm{KeV}$")

    if surface_arr is None:
        # -------------------------------
        # Mode 1: Underground-only
        # -------------------------------
        offsets = np.linspace(-0.15, 0.15, num=len(images))
        offset_map = {img: offsets[i] for i, img in enumerate(images)}

        for pk in peaks:
            p_idx = pk - 1
            for img in images:
                if img not in (4, 5, 6, 7):
                    continue
                i_idx = img - 4
                dx = offset_map[img]
                y = arr[m_idx, :, i_idx, p_idx, 0]
                yerr = arr[m_idx, :, i_idx, p_idx, 1]
                mask = y != -1
                if not np.any(mask):
                    continue

                plt.errorbar(
                    x[mask] + dx,
                    y[mask],
                    yerr=yerr[mask],
                    fmt=default_marker_map.get(img, "o"),
                    linestyle="none",
                    color=color_map[pk],
                    label=fr"Img{img} $K_{{{'α' if pk==1 else 'β'}}}$",
                    capsize=2,
                    linewidth=1,
                    markersize=5,
                )
    else:
        # -------------------------------
        # Mode 2: Underground vs Surface (single image)
        # -------------------------------
        i_idx = 0  # placeholder, adjust if you extend beyond image4
        ug_dx, sf_dx = -0.08, 0.08

        for pk in peaks:
            p_idx = pk - 1

            # Underground
            y_ug = arr[m_idx, :, i_idx, p_idx, 0]
            yerr_ug = arr[m_idx, :, i_idx, p_idx, 1]
            mask_ug = y_ug != -1
            if np.any(mask_ug):
                plt.errorbar(
                    x[mask_ug] + ug_dx,
                    y_ug[mask_ug],
                    yerr=yerr_ug[mask_ug],
                    fmt=underground_marker,
                    linestyle="none",
                    color=color_map[pk],
                    label=fr"udg $K_{{{'α' if pk==1 else 'β'}}}$",
                    capsize=2,
                    linewidth=1,
                    markersize=6,
                )

            # Surface
            y_sf = surface_arr[m_idx, :, i_idx, p_idx, 0]
            yerr_sf = surface_arr[m_idx, :, i_idx, p_idx, 1]
            mask_sf = y_sf != -1
            if np.any(mask_sf):
                plt.errorbar(
                    x[mask_sf] + sf_dx,
                    y_sf[mask_sf],
                    yerr=yerr_sf[mask_sf],
                    fmt=surface_marker,
                    linestyle="none",
                    color=color_map[pk],
                    label=fr"sur $K_{{{'α' if pk==1 else 'β'}}}$",
                    capsize=2,
                    linewidth=1,
                    markersize=6,
                )

    plt.xticks(x, x_labels)
    plt.xlabel("CCD extensions")
    plt.ylabel(r"Energy peak center [keV] ($K_{\alpha}, K_{\beta}$)")
    plt.ylim(5.4, 6.6)
    mode_suffix = "underground vs surface (Img 4)" if surface_arr is not None else "underground images"
    plt.title(f"Module {module_number}: Fe-55 energy reconstruction — {mode_suffix}")

    # de-duplicate legend entries
    handles, labels = plt.gca().get_legend_handles_labels()
    uniq = dict(zip(labels, handles))
    plt.legend(
        uniq.values(), uniq.keys(),
        ncol=1, frameon=True,
        fontsize=8,
        loc="upper left", bbox_to_anchor=(1.02, 1)   # outside top-right
    )
    plt.tight_layout()


def main():
    # ---------------- Argument parsing ----------------
    parser = argparse.ArgumentParser(description="plot Fe55 evergy peaks")
    parser.add_argument("--id", type=int, help="Module number to plot (1-based index). If omitted, process all.")
    args = parser.parse_args()

    # for comparison between imgage456 in underground testing
    arr_udg = build_array_from_db(table = "MODULE_UNDERGROUND2", columns=["id", "Image4_Low_Peak1_A", "Image4_Low_Peak1_B", "Image4_Low_Peak1_C", "Image4_Low_Peak1_D", 
             "Image4_Low_Peak2_A", "Image4_Low_Peak2_B", "Image4_Low_Peak2_C", "Image4_Low_Peak2_D", 
             "Image5_Low_Peak1_A", "Image5_Low_Peak1_B", "Image5_Low_Peak1_C", "Image5_Low_Peak1_D", 
             "Image5_Low_Peak2_A", "Image5_Low_Peak2_B", "Image5_Low_Peak2_C", "Image5_Low_Peak2_D", 
             "Image6_Low_Peak1_A", "Image6_Low_Peak1_B", "Image6_Low_Peak1_C", "Image6_Low_Peak1_D", 
             "Image6_Low_Peak2_A", "Image6_Low_Peak2_B", "Image6_Low_Peak2_C", "Image6_Low_Peak2_D"],)
    
    arr_sur = build_array_from_db(table = "MODULE_SURFACE")


# ---------------- Decide which modules to process ----------------
    if args.id is not None:
        module_ids = [args.id]
    else:
        # Process all modules in arr_udg (assume shape (Nmod, ...))
        nmod = arr_udg.shape[0]
        module_ids = list(range(1, nmod + 1))  # 1-based indexing

    # ---------------- Loop ----------------
    for mid in module_ids:
        print("Processing module id:", mid)

        outdir = f"uploads/edit_module_underground/module_underground_{mid}"
        os.makedirs(outdir, exist_ok=True)

        # UG-only
        plot_module_energy_peaks(arr_udg, module_number=mid)
        plt.savefig(f"{outdir}/Fe55Energy.png", dpi=300)
        plt.close()

        # UG vs Surface (only if surface has this module index)
        if mid <= arr_sur.shape[0]:
            plot_module_energy_peaks(arr_udg, module_number=mid, surface_arr=arr_sur)
            plt.savefig(f"{outdir}/Fe55Energy_comparison.png", dpi=300)
            plt.close()

if __name__ == "__main__":
    main()