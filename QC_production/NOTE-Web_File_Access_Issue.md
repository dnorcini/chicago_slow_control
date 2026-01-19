# Web File Access Issue
Cinyu Zhu, Hopkins, 7/4/2025 (C'est Jour de l'Indépendance!)

## Problem:

At around June 25 2025, the web interface suddenly lost the ability to read and write files at upload_dir under /home/uploads, 
making the website crushes when someone try to submit to sql database or upload a file. 

Access to surface database images via web was also denied by error message 'Forbidden'.

Changing Unix file permissions to 777(-wrxwrxwrx) did not fix it.

## Root Cause:
SELinux was enabled or switched back to enforcing mode, and it denied Apache (httpd) access to files labeled with the default context for user home directories (system_u:object_r:user_home_dir_t:s0).

## Confirmation:
Running `sudo setenforce 0` (setting SELinux to permissive mode) immediately fixed the problem

## Permanent Fix
We relabeled the upload directory /home/uploads to a web-accessible SELinux context:

`sudo setenforce 1` (set selinux back to enforced mode)

`sudo semanage fcontext -a -t httpd_sys_rw_content_t "/home/uploads(/.*)?"`

`sudo restorecon -Rv /home/uploads`

## set back the rwx permissions:
### for php code files:
Set directories to 755 (rwxr-xr-x): `find /var/www/html -type d -exec chmod 755 {} \;`

Set files to 644 (rw-r--r--): `find /var/www/html -type f -exec chmod 644 {} \;`

### for upload folder: 
`sudo chmod -R 775 <folder path>`
