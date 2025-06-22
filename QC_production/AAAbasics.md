# SQL basics:
To verify it exists:
SHOW TABLES;

To check the columns:
DESCRIBE MODULE_UNDERGROUND;

To run a series of ALTER TABLE commands in MySQL to add these new columns.
ALTER TABLE MODULE_UNDERGROUND
ADD COLUMN Image5_Low_Defects_A TEXT,
ADD COLUMN Image5_Low_Resolution_A DECIMAL(10,2);

**UPDATE**
(use search&replace/python/gpt to do it for each module)
scripts are now available at ./AAAbasic.ipynb !!!
will check the already existed column for you!
and you can add columns with python scripts in one go!




# to change table type
ALTER TABLE MODULE_UNDERGROUND ENGINE=MyISAM;
or
ALTER TABLE MODULE_SURFACE ENGINE=InnoDB;

(according to gpt, InnoDB is generally better, and either is fine for the current usecase)

# edits on *.php files

## 1. Duplicate Files
cp edit_module_surface.php edit_module_underground.php
cp list_module_surface_entries.php list_module_underground_entries.php
cp list_module_surface_entries_summary.php list_module_underground_entries_summary.php

## 2. Update SQL Table Names

### 2.1 Replace every occurrence of MODULE_SURFACE with MODULE_UNDERGROUND.

$query = "SELECT * FROM MODULE_UNDERGROUND WHERE ID = $id";

### 2.? add a new table for a new image in edit_module_underground.php
in `aux/array_defs.php`, add new entry in `image_numbers`, and in `image_fields` if needed

````
$image_numbers_low = array("1_low_", "2_low_", "3_low_","31_low_","32_low_", "4_low_", "5_low_", "6_low_","7_low_","98_low_","99_low_");
$image_numbers_high = array("1_high_", "2_high_", "3_high_", "4_high_", "5_high_", "6_high_","98_high_", "99_high_");
````
Then use the second script in `AAAbasics.ipynb` to generate the php code for the new table in `edit_module_underground.php`

After all these you should be able to write new entries and read them from edit_module_underground.php website!

-----------------------------------------------------
# to add another attribute/column. to image99 (add a new column in the table)
### 1. add new column in MySQL

ALTER TABLE MODULE_UNDERGROUND ADD COLUMN Image98_Low_testxxx_A TEXT;
ALTER TABLE MODULE_UNDERGROUND ADD COLUMN Image98_Low_testxxx_B TEXT;
ALTER TABLE MODULE_UNDERGROUND ADD COLUMN Image98_Low_testxxx_C TEXT;
ALTER TABLE MODULE_UNDERGROUND ADD COLUMN Image98_Low_testxxx_D TEXT;

**UPDATE**
now you can add them via the aformentioned python code!

### 2. add to fix the name issue connecting sql and php:        
get_module_underground_vals.php:

${'image' . $img . 'testxxx_' . $amp} = isset($row['Image' . $capitalized_img . 'testxxx_' . $amp]) ? $row['Image' . $capitalized_img . 'testxxx_' . $amp] : "";


### 3. to write in and access this new column
arr_def.php:
````
$image_fields = array(
    'tracks_', 'noise_', 'defects_', 'saturation_', 'sharpness_tracks_', 'cti_code_', 'cti_visual_',
    'comments_', 'reference_', 'region_defect_', 'noise_overscan_', 'res_', 'gain_', 'dark_current_', 'column_defects_', 'pixel_defects_',
    'peak1_', 'peak2_', 'sigma_','front_' ,'testxxx_'
);
````
------------------------------------------
# take care of file uploading (png and log) 
### 1. for the browser able to find the file your uploaded:
`sudo ln -s /home/uploads /var/www/html/uploads`

This allows you to access /home/uploads/... via http://ccdqc.pha.jhu.edu/uploads/....

- /home/uploads = where images are stored
- /var/www/html/uploads = root directory of web

### 2. can't write image99?
check the upload block in `edit_module_underground.php` 

````
// Add image fields dynamically for image1 to image6
    // orginally for ($i = 1; $i <= 5; $i++) but since I put image99 as a test...
    for ($i = 1; $i <= 100; $i++) {
        $file_fields[] = 'image' . $i . '_low_file';
        $log_fields[] = 'image' . $i . '_low_log';
        $file_fields[] = 'image' . $i . '_high_file';
        $log_fields[] = 'image' . $i . '_high_log';
    }
````

# add a new module entry (a new row in the table)
INSERT INTO MODULE_UNDERGROUND () VALUES ();
