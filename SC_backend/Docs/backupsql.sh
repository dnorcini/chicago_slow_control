#!/bin/sh

### This Script Requires an email server!!###

### Add this file to Cronjobs ###
#crontab -e
#0  18 *  *  * /home/damic/backupsql.sh


### System Setup ###
NOW=`date +%Y-%m-%d_%H.%M.%S`
NOW_TODAY=`date +%Y-%m-%d_%H`
#
### SSH Info ###
SHOST=zev.uchicago.edu
SUSER="dnorcini"
SDIR="/data/hermes_backup"
#
### MySQL Setup ###
MUSER="control_user"                            
MPASS="MyLife4AiurUser" 
MHOST="localhost" 
DBS="control ccd_qc"                      # space separated list of databases to backup
MDIR="/home/damic/tmp_backupsql"   
#
### Local Writable directory on the server ###
EMAILID="dnorcini@kicp.uchicago.edu"      # the email you want notification sent to
#
### Start MySQL Backup ###
attempts=0
for db in $DBS                             # for each listed database
do
    attempts=`expr $attempts + 1`           # count the backup attempts
    FILE=$MDIR/mysql-$db.$NOW.sql.gz        # Set the backup filename
                                            # Dump the MySQL and gzip it up
    mysqldump -q -u $MUSER -h $MHOST -p$MPASS $db | gzip -9  > $FILE
done
rsync -avzp $MDIR/mysql* $SUSER@$SHOST:$SDIR # send to backup server
                                             # deleting of old files on backup
ssh $SUSER@$SHOST "find $SDIR/mysql*.sql.gz -type f -daystart -mtime +60 -exec rm {} \\;"
#
### Mail me! ###
backupfiles=$(ssh $SUSER@$SHOST "ls -lh $SDIR/*.$NOW_TODAY*.sql.gz")
count=0                                   # count local files from today
while read -r file; do count=`expr $count + 1`; done <<<"$backupfiles"
#
### Send mail ###
mail -s "Backups Report" $EMAILID << END
Success with $count of $attempts
The following databases were attempted to be backed up
$DBS
Files transferred:
$backupfiles
END
#
### Delete tmp files from DB server ###
rm $MDIR/mysql*                             # delete files on db server
