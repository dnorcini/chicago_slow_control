April 2024: SC installation on Debian12

Refer to old notes (i.e. CentOS 7 install) for more detailed explanation of certain steps. Here we provide a “quick (-er)” guide to get the SC running on Debian 12 (DAMIC-M official OS).

Update: January 2026, Cinyu Zhu

Editing and adding notes for damicm-sc installation

## Dependencies
* LAMP
  * Apache2 webserver, v2.4.59
  * php v5, v5.6.40
  * MariaDB/MySQL + utilities, v10.11.6
  * phpMyAdmin, v4.9.1
* SC software
  * C/C++ dev tools
  * gcc, v12.2.0

## LAMP Install
### Apache
To install and start:
```
# apt-get install apache2
# systemctl enable apache2
# systemctl start apache2
# systemctl status apache2
```

Determine where the `DirectoryRoot` is located in the config file:
```
# emacs -nw /etc/apache2/apache2.conf
DirectoryRoot = /var/www/html
```

Add a short html file to that directory called `index.html`:
```
 <html><body><h1>It works!</h1></body></html>
```

Point the browser to http://localhost. If everything has gone well, you should see "It works!".

### MySQL
First, be sure to check you don’t have any MySQL/MariaDB packages already installed as there can be many conflicts due to different versioning:
```
# dpkg -l | grep -e mysql -e mariadb
```

If you need to remove old  packages, [follow the purge instructions here:](https://unix.stackexchange.com/questions/550154/the-following-packages-have-unmet-dependencies-mariadb-server). To see all available add-on packages on the mirror (in past on CentOS must have `mariadb-devel` installed for it to find global SC header files):
```
# apt-cache search mariadb
```

To work with our code, we DO NOT want to install MariaDB >v10, otherwise, when you go to compile the SC code, you will get issues finding `myglobals.h` and `mysql.h` includes (even if you see them in the includes directory). Stick with v10 (default of Debian12):
```
# apt-get install mariadb-server=“1:10.11.6-0+deb12u1”
# apt-get install libmariadb-dev

# systemctl enable mariadb
# systemctl start mariadb
# systemctl status mariadb
```

By default, MariaDB does not use secure settings. To enable:
```
# sudo mysql_secure_installation
```

Using the root credentials, make sure you can run MySQL:
```
$ mysql -uroot -p
```

Once inside the mysql shell, add `mysql` as the default database (if not, will get logging in issues when testing out phpMyAdmin):
```
> USE mysql;
```

Put the MariaDB includes path in your bashrc so your compiler knows where to find them:
```
$ mariadb-config
ADD OUTPUT HERE
$ emacs -nw ~/.bashrc
export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:”/usr/local/lib:/usr/include/mariadb:/usr/include/mariadb/mysql”
```

If you are using a separate hard disk (here `/data`) to store the database, you  need to change the default location. First confirm the default location:
```
$  mysql -u root -p -e "SELECT @@datadir;"
+-----------------+
| @@datadir       |
+-----------------+
| /var/lib/mysql/ |
+-----------------+
1 row in set (0.00 sec)
MariaDB [(none]] > exit
```

Shutdown database:
```
$ sudo systemctl stop mariadb
```

Copy the files and permissions (note: `mysql.sock` will not copy, it is created open starting MariaDB). This will ensure that mysql is set to the owner/group of this directory:
```
$ sudo cp -R -p /var/lib/mysql/ /data/mysql
```

Now point to directory and configure socket via `/etc/my.cnf` and `/etc/my.cnf.d/mariadb-server.cnf`:
```
$ sudo emacs -nw /etc/my.cnf
[mysqld]
datadir=/data/mysql
socket=/data/mysql/mysql.sock

[client]
port=3306
socket=/data/mysql/mysql.sock

!includedir /etc/my.cnf.d

$ sudo emacs -nw /etc/my.cnf.d/mariadb-server.cnf
[mysqld]
datadir=/data/mysql
socket=/data/mysql/mysql.sock
```

Restart MariaDB, check the new default path, remove old file:
```
$ sudo chown -R mysql:mysql /data/mysql
$ sudo systemctl start mariadb
$ mysql -u root -p -e "SELECT @@datadir;"
MariaDB [(none)] > select @@datadir;
+----------------------------+
| @@datadir                  |
+----------------------------+
| /data/mysql/ |
+----------------------------+
1 row in set (0.01 sec)

$ sudo rm -rf /var/lib/mysql
```


### php5.6
Although it has not been supported in, forever, we continue to use php v5 until we update the web code syntax for newer versions. Since the deb mirror only supports php8+ (check via `$apt-cache show php | grep Version`), we need to compile from source:
```
# apt install software-properties-common ca-certificates lsb-release apt-transport-https
# sh -c 'echo "deb https://packages.sury.org/php/ $(lsb_release -sc) main" > /etc/apt/sources.list.d/php.list'
# wget -qO - https://packages.sury.org/php/apt.gpg | sudo apt-key add -
# apt update
# apt install php5.6 php5.6-mysqlnd php5.6-cli php5.6-gd php5.6-zip
```

In DirectoryRoot add a `phpinfo.php` file with the follow contents:
```
<?php phpinfo(); ?>
```

Point the browser to [http:// localhost/phpinfo.php](http://%20localhost/phpinfo.php). You should see all of the info about the php package you have installed, including where the config files are located/loaded:
```
/etc/apache2/apache2.conf default is OK
/etc/php/5.6/apache2/php.ini
```

In the php config file, change a few of the settings:
```
# /etc/php/5.6/apache2/php.ini
 max_execution_time = 180
 max_input_time = 180
 memory_limit = 512M
 post_max_size = 50M
 upload_max_filesize = 50M
 date.timezone = America/Chicago
```

### phpMyAdmin
phpMyAdmin is a tool to access MySQL through a GUI (natively just through command line). To get the version we want, it’s easiest to install from source: 
```
$ wget https://files.phpmyadmin.net/phpMyAdmin/4.9.1/phpMyAdmin-4.9.1-all-languages.tar.gz 
$ tar -xf phpMyAdmin-4.9.1-all-languages.tar.gz
```

Move the source code to install location (we added it to where `apt` would install), and copy the config file:
```
$ sudo mv phpMyAdmin-4.9.1-all-languages /usr/share/phpMyAdmin
$ sudo cp -pr /usr/share/phpMyAdmin/config.sample.inc.php /usr/share/phpMyAdmin/config.inc.php
```

A blowfish secret needs to be generated and added to the config file (encryption to keep password safe in web). Can do this from the command line with `pwgen` package:
```
$ sudo apt-get install pwgen
$ pwgen -s 32 1
$ sudo emacs -nw /usr/share/phpMyAdmin/config.inc.php
$cfg[‘blowfish_secret’] = ‘FILL IN HERE’; /* YOU MUST FILL IN THIS FOR COOKIE AUTH! */
```
 
Import `create_tables.sql` so that new tables can be created via phpMyAdmin:
```
$ sudo mysql < /usr/share/phpMyAdmin/sql/create_tables.sql -uroot -p
```

Create an alias in apache so we can access phpMyAdmin via http://localhost/phpmyadmin (copy and paste below into the file):
```
$ sudo emacs -nw /etc/apache2/conf-available/phpMyAdmin.conf

Alias /phpMyAdmin /usr/share/phpMyAdmin
Alias /phpmyadmin /usr/share/phpMyAdmin

<Directory /usr/share/phpMyAdmin/>
   AddDefaultCharset UTF-8

   <IfModule mod_authz_core.c>
     # Apache 2.4
     <RequireAny> 
      Require all granted
     </RequireAny>
   </IfModule>
   <IfModule !mod_authz_core.c>
     # Apache 2.2
     Order Deny,Allow
     Deny from All
     Allow from 127.0.0.1
     Allow from ::1
   </IfModule>
</Directory>

<Directory /usr/share/phpMyAdmin/setup/>
   <IfModule mod_authz_core.c>
     # Apache 2.4
     <RequireAny>
       Require all granted
     </RequireAny>
   </IfModule>
   <IfModule !mod_authz_core.c>
     # Apache 2.2
     Order Deny,Allow
     Deny from All
     Allow from 127.0.0.1
     Allow from ::1
   </IfModule>
</Directory>
```

To reset settings:
```
$ sudo a2enconf phpmyadmin.conf 
```

Create a temp directory for phpMyAdmin, change permissions, and set ownership:
```
$ ps aux | egrep '(apache)'
www-data user
$ mkdir /usr/share/phpMyAdmin/tmp
$ sudo chmod 777 /usr/share/phpMyAdmin/tmp
$ sudo chown -R www-data /usr/share/phpMyAdmin
$ sudo systemctl restart apache2
```

Point browser to http://localhost/phpMyAdmin. If the login fails and complains about credentials, try resetting the root user info via `sudo mysql_secure_installation`.

## Install SC software
### clang essitials
```
sudo apt-get install -y build-essential
```
check installation:
```
$ make --version
GNU Make 4.3
$ gcc --version
gcc (Debian 12.2.0-14+deb12u1) 12.2.0
$ g++ --version
g++ (Debian 12.2.0-14+deb12u1) 12.2.0
```
### Get the source code
Give your user sudo permissions (in this case, `damicm` is the username):
```
# sudo visudo
username ALL=(ALL:ALL) ALL
su - damicm
```

Copy the SC code from GitHub. If you want to keep up-to-date with any changes, you should [clone the repo](https://dylancastillo.co/how-to-use-github-deploy-keys/) instead of downloading the files:
```
$ git clone git@github-chicago_SC:username/chicago_slow_control.git 
$ git checkout damicm-debian12             
```

If you need to deploy key as a developer:
```
$ ssh-keygen -t ed25519 -C "USERNAME@EMAIL.com"
$ touch ~/.ssh/config 
$ chmod 600 ~/.ssh/config 
$ emacs -nw ~/.ssh/config # Or: nano ~/.ssh/config
Host github-chicago_SC
	HostName github.com 
    AddKeysToAgent yes 
    PreferredAuthentications publickey 
    IdentityFile ~/.ssh/id_ed25519

$ cat ~/.ssh/id_ed25519.pub
ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAILs35pzG5jZakTEHDWeRErgkAmabhQj2yj/onxlIQgli USERNAME@EMAIL.com
```

The owner will have to go into the browser: repo -> settings -> deploy keys -> add deploy key. Copy the public key. Do not check the box that gives you write access from the deploy key. Then clone repo.

### Configure
To ensure any local changes you make don’t interfere with the code repo, it is best to make a copy of the pieces we need and put them in the appropriate directories:
```
$ cp -r chicago_slow_control/SC_backend /home/damicm
$ sudo cp -r chicago_slow_control/SC_web /var/www/html
```

Add the library path (for `libslow_control.so`) to your bashrc: 
```
$ emacs -nw ~/.bashrc
 export LD_LIBRARY_PATH=$LD_LIBRARY_PATH:/home/damic/SC_backend/
 slow_control_code/lib/
```

Now, we should be able to compile the libraries:
```
$ cd /home/damicm/SC_backend/slow_control_code/lib
$ make
```

Then, we can compile the SC programs. All can be done at once via:
```
$ cd /home/damicm/SC_backend/slow_control_code
$ make
```

or individually (useful when making small changes to a specific program):
```
$ cd /home/damicm/SC_backend/slow_control_code/LS336_eth
$ make
```

If you are not using the Debian 12 branch of the SC code, you will run into an issue when compiling (the short answer is the newer gcc version that is probably default on your OS is not compatible with old commands). The following flags need to be changed in the Makefile (again, only if using the generic SC branch):
```
CC = gcc -O3
CFLAGS = `mysql_config --cflags` -I../include
LDFLAGS = `mysql_config --libs`
```

change to, in `slow_control_code/lib/Makefile`:
```
CC = gcc -fcommon -O3 
CFLAGS = `mariadb_config --cflags` 
LDFLAGS = `mariadb_config --libs`
```

change to, in `slow_control_code/Makefile`:
```
CC = gcc -Wl,--no-as-needed -03
CFLAGS = `mariadb_config --cflags`
LDFLAGS = `mariadb_config --libs`
```

Without these new flags, you can get errors about undefined variables (can’t link to the shared library), multiple definitions, etc.. You can see which variables are loaded in the library via:
```
$ nm -D /home/damicm/SC_backend/slow_control_code/lib/libslow-control.so
```

### Set up the database
Before setting up the database, copy the config file example to the `/etc` directory and edit it with the location of the master database (localhost if on same computer), and credentials to log into MySQL through control_user (which we will add to MySQL users in the next step:
```
$ sudo cp SC_backend/Docs/slow_control_db_conf.ini /etc
$ sudo emacs -nw /etc/slow_control_db_conf.ini
[client]
host=localhost
database=control
user=control_user
password=TYPE_PASSWORD_HERE    #BE CAREFUL!!
port=3306
```

Now, you can import the default database, and call it `control`. You can do it through the GUI phpMyAdmin interface or more streamlined in the terminal:
```
$ zcat /home/damicm/SC_backend/Docs/damicm_template.sql.gz | mysql -u 'root' -p control
```

You can check out the structure in phpMyAdmin. Most of the instruments you will use already have settings listed as entries so that you don’t have to do much work when you set-up the webpage.

For whatever reason, the collation (way that the database decode characters) is non-standard and will cause errors when you try to perform certain actions (mostly when in phpMyAdmin). It’s not completely necessary, but to avoid, we just change it now:
```
$ sudo emacs -nw /etc/mysql/mariadb.conf.d/50-server.cnf
# change to UNICODE
character-set-server  = utf8mb4
collation-server      = utf8mb4_unicode_ci
```

To load these new configs, In phpMyAdmin go to the `mysql` table and change the view:  mysql -> Views -> user -> structure -> edit view -> Go. Then in the `mysql` table: operations -> collation -> utf8mb4_unicode_ci -> Go

Now we can go to phpMyAdmin index page, choose `User accounts` on top panel, and create the `control_user` (with the same password as written in the config file). This user should be given all privileges and grant access over `localhost`. Set MariaDB to load the `control` database by default:
```
$ mysql -u root -p
> USE control;
```

### Set up the web interface
To have the frontend web interface connect to the database, we need to set up the credentials:
```
$ cd /var/www/html/SC_web
$ sudo emacs -nw db_login.php #and master_db_login.php
date_default_timezone_set('US/Central'); // set to server's time zone
$db_host='localhost';      // fill me in!
$db_database='control';
$db_username='control_user';
$db_password='TYPE_PASSWORD_HERE';    // fill me in as well!
```

So the webpage can display plots properly, we need to give Apache access:
``` 
$ sudo mkdir jpgraph_cache
$ sudo chmod a+rw jpgraph_cache
$ sudo chown -R www-data:www-data jpgraph_cache/
$ sudo systemctl restart apache2
```
DO NOT give full permissions to the other files.


## Run SC 
### Set up Watchdog 
The Watchdog program should always be running, as it gives us the ability to start all instrumentation/daemons automatically (if their ‘run’  flag is set).  This is the only program that needs to be run manually, and must be run with it's full path. First, we enter the path to the directory where `SC_Watchdog` is located into the Watchdog config: Config -> Edit Instrument Configuration.

Then execute the program using the full path to start the Watchdog:
```
$ /home/damicm/SC_backend/slow_control_code/SC_Watchdog/SC_Watchdog
```

To make this process more automatic, we can use an install script to link the shared libraries and start the Watchdog. The script is called `install.sh` within the `SC_backend/slow_code`directory. This creates a symbolic link to the slow control library and put it in our library path `/usr/local/lib`:
```
$ sudo /home/damicm/SC_backend/slow_control_code/install.sh
```

It will also create a script `slow_start.sh` which starts the Watchdog, located in `/usr/local/bin`.  You will need to update the MySQL password in this file (this is a work around for a bug, not the best practice but it does the job). To have this program execute upon boot, create a start-up service:
```
$ sudo emacs -nw /etc/systemd/system/slow-start.servce
[Unit]
Description=Starting slow control Watchdog system...
After=mariadb.service
  
[Service]
ExecStart=/usr/local/bin/slow_start.sh start
ExecStop=/usr/local/bin/slow_start.sh stop
RemainAfterExit=yes
  
[Install]
WantedBy=default.target
```

It is key that we ask the service to remain active after exiting, since the script itself does not continuously run, but the instruments it will turn on do. We need to make our script executable by changing the permission, and also installing the new systemd service so that is enabled upon boot:
```
$ sudo chmod 744 /usr/local/bin/slow_start.sh
$ sudo chmod 664 /etc/systemd/system/slow-start.service
$ sudo systemctl daemon-reload
$ sudo systemctl slow-start.service
Created symlink from /etc/systemd/system/default.target.wants/slow-
start.service to /etc/systemd/system/slow-start.service.
```

Start the Watchdog manually by:
```
$ sudo systemctl start slow-start.service
$ systemctl status slow-start.service
● slow-start.service - Starting slow control Watchdog system...
     Loaded: loaded (/etc/systemd/system/slow-start.service; enabled; vendor
  preset: disabled)
     Active: active (exited) since Mon 2020-03-02 14:54:55 CST; 8min ago
   	  CGroup: /system.slice/slow-start.service
             ├─4499 /home/damic/SC_backend/slow_control_code/SC_Watchdog/
  Mar 02 14:54:55 hermes.uchicago.edu systemd[1]: Started Starting slow control
  Watchdog system....
  Mar 02 14:54:55 hermes.uchicago.edu slow_start.sh[4496]: Watchdog is now
  running...
```

You can check the last time the database logged an entry on the web interface. Note it may take ~minute or so for all the Watchdog-monitored instruments to start-up and continue logging.

## Backup database
This can be done however you choose. We provide a simple script (`SC_backend/Docs/backupsql.sh`) that dumps the specified databases into a .sql file and then rsyncs them onto our cluster. We send an email when the process is complete and delete backups older then a specified number of days. Copy this file and put it in the directory that you want it to run from.

To have the backup script run at a certain time every day (or whatever interval), set up a cronjob:
```
$ crontab -e
crontab -e
SHELL=/bin/sh
PATH=/bin:/sbin:/usr/bin:/usr/sbin:/usr/local/bin:/usr/local/sbin
For details see man 4 crontabs
Example of job definition: # .---------------- minute (0 - 59) # | .------------- hour (0 - 23) # | | .---------- day of month (1 - 31) # | | | .------- month (1 - 12) OR jan,feb,mar,apr ... #| | | | .----dayofweek(0-6)(Sunday=0or7)OR sun,mon,tue,wed,thu,fri,sat #||||| # * * * * * user-name command to be executed
0  18 *  *  *             /home/damicm/backupsql.sh
```

This sets configuration will have the script run once everyday at 18:00.

## ssh access to the machine
````
sudo apt install openssh-server
sudo systemctl enable ssh
sudo systemctl status ssh
````
It should be active(running) and listening on port 22

## keep sc subnet ip address after reboot:
Debian 12 uses NetworkManager

Check the current ip address and device (which physical ethernet port is associated, e.g. enp129s0)
````
$ ip addr
````
````
$ nmcli device status
DEVICE      TYPE      STATE      CONNECTION
enp129s0    ethernet  connected  Wired connection 1
````
set up profile:
````
sudo nmcli connection add \
  type ethernet \
  ifname enp129s0 \
  con-name slow-control \
  ipv4.method manual \
  ipv4.addresses 192.168.1.1/24
````
bring it up:
````
sudo nmcli connection down slow-control 2>/dev/null
sudo nmcli connection up slow-control
````
verify:
````
nmcli device status
ip addr show enp129s0
````

## Setup Email SMTP for SC alarm
Email contents and mailing lists have already been taken care of by the `int send_mail_message(char *address, char *message)` in alarm trigger and alart system in the SC software. So all we need is to set up smtp 

### Goal: 
Enable this to work for a normal user

`echo "hello" | mail -s "test" someone@example.com`


### 0) Prep: Gmail side 

Create a new Gmail account, turn on 2-Step Verification (required for app password).

Create an App Password and copy it.

You will paste this into `/etc/msmtprc` as the password.

If you don’t use an App Password, you’ll often get:
`535 5.7.8 Username and Password not accepted`. 
Because the `mail` in Debian 12 OS doesn't support the advanced security required for Gmail.

### 2) Verify wiring (sendmail points to msmtp)
```
which mail
ls -l /usr/sbin/sendmail
readlink -f /usr/sbin/sendmail
```
expected output:
```
/usr/bin/mail
lrwxrwxrwx 1 root root 12 Feb  5  2023 /usr/sbin/sendmail -> ../bin/msmtp
/usr/bin/msmtp
```

### 3) Create a dedicated group for reading /etc/msmtprc
```
sudo groupadd -f msmtp
sudo usermod -aG msmtp <YOUR_USERNAME>
````

### 4) Create /etc/msmtprc (system-wide msmtp config)
`sudo nano /etc/msmtprc`
````
defaults
auth           on
tls            on
tls_starttls   on
tls_trust_file /etc/ssl/certs/ca-certificates.crt

# Log to the user's home directory (simplifies permissions)
logfile        /home/<YOUR_USERNAME>/.msmtp.log

account gmail
host smtp.gmail.com
port 587
from <YOUR_GMAIL>@gmail.com
user <YOUR_GMAIL>@gmail.com
password <YOUR_GMAIL_APP_PASSWORD>

account default : gmail
````

Lock down permissions:
````
sudo chown root:msmtp /etc/msmtprc
sudo chmod 640 /etc/msmtprc
````

### 5) Create the per-user logfile
````
touch ~/.msmtp.log
chmod 600 ~/.msmtp.log
````
### 6) Test via `mail`
`echo "mail test" | mail -s "subject mailx test" <DEST_EMAIL>`

Then check your `<DEST_EMAIL>` inbox and check the log via `tail -n 50 ~/.msmtp.log`



