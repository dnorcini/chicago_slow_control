/* This is the alarm system for the slow control. */
/* that checks for free disk space.  It will insert an error */
/* if the free space % goes below a set value. */
/* This should be run on the master db computer. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2011 */
/* James public licence. */

#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"

#include <sys/statvfs.h>

#define INSTNAME "disk_free"

#define _def_read_sensor
int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    struct statvfs this_fs;
        
    if (statvfs(s_s->user1, &this_fs) == -1)
    {
	perror("Read FS Status");
   	return(1);
    }  
    
    *val_out = 100.0 * (this_fs.f_bavail) / (double)(this_fs.f_blocks);
    
    //fprintf(stdout, "%s, blocks: %d, avail: %d, free: %d, block size: %d \n", s_s->user1, this_fs.f_blocks, this_fs.f_bavail, this_fs.f_bfree, this_fs.f_frsize);

    sleep(2);
    return(0);
}


#include "main.h"
