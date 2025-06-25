/* This is a library for the instrument structures fns. */
/* James Nikkel */
/* james.nikkel@yale.edu */
/* Copyright 2009 */
/* James public licence. */

#include "SC_inst_interface.h"

int register_inst(struct inst_struct *i_s)
{
    //  If you are using daemonize(), you must call that
    // first, as the PID changes after  daemonize() is called.

    if (i_s->PID != -1)
    {
	fprintf(stderr, "Program is already running. \n");
	exit(1);	
    }
    i_s->PID = getpid();
    i_s->start_time = time(NULL);
    i_s->last_update_time = time(NULL);
    return(update_inst_state(i_s));
}


int unregister_inst(struct inst_struct *i_s)
{
    char   query_strng[1024];

    i_s->PID = -1;
    i_s->start_time = -1;
    i_s->last_update_time = -1;
    sprintf(query_strng, "UPDATE `sc_insts` SET `PID`=-1, `start_time`=-1, `last_update_time`=-1  WHERE `name` = \"%s\" ", 
	    i_s->name);
    return(write_to_mysql(query_strng));
}


int update_inst_state(struct inst_struct *i_s)
{
    char   query_strng[1024];
    int    my_errors = 0;

    sprintf(query_strng, "UPDATE `sc_insts` SET `PID`=%d, `start_time`=%lu, `last_update_time`=%lu  WHERE `name` = \"%s\" ", 
	    i_s->PID, (unsigned long)i_s->start_time,  (unsigned long)i_s->start_time, i_s->name);
    my_errors += write_to_mysql(query_strng);

    return(my_errors);
}


int read_inst_struct_file(struct inst_struct *i_s, char *inst_file_name)
{
    // If the DB is not available, read_mysql_inst_struct will
    // not work, use read_inst_struct_file to read the structure from
    // a pre-filled file
    
    FILE *f;
    
    if ((f=fopen(inst_file_name, "r")) == NULL)
        return(1);
    
    if (fread(i_s, sizeof(struct inst_struct), 1, f) != 1)
    {
	fclose(f);
	return(1);
    }
    
    fclose(f);
    return(0);
}

int write_inst_struct_file(struct inst_struct *i_s, char *inst_file_name)
{
    // If the DB is not available, read_mysql_inst_struct will
    // not work, use read_inst_struct_file to read the structure from
    // a file filled by this function.
    
    FILE *f;
    
    if ((f=fopen(inst_file_name, "w")) == NULL)
        return(1);
    
    if (fwrite(i_s, sizeof(struct inst_struct), 1, f) != 1)
    {
	fclose(f);
	return(1);
    }
    
    fclose(f);
    return(0); 
}

int read_mysql_inst_struct(struct inst_struct *i_s, char *inst_name)
{
    //  Opens the db, and reads in the values of the instrument 'inst_name' into 
    //  the structure 'i_s'.  This  structure is defined in "SC_db_interface.h".
  
    char   query_strng[1024];  
  
    sprintf(query_strng, "SELECT name FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_string(query_strng, i_s->name, sizeof(i_s->name)))
	return(1);
    sprintf(query_strng, "SELECT description FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_string(query_strng, i_s->description, sizeof(i_s->description)))
	return(1);
    sprintf(query_strng, "SELECT subsys FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_string(query_strng, i_s->subsys, sizeof(i_s->subsys)))
	return(1);
    sprintf(query_strng, "SELECT run FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_int(query_strng, &i_s->run))
	return(1);
    sprintf(query_strng, "SELECT restart FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_int(query_strng, &i_s->restart))
	return(1);
    sprintf(query_strng, "SELECT WD_ctrl FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_int(query_strng, &i_s->WD_ctrl))
	return(1);
    sprintf(query_strng, "SELECT path FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_string(query_strng, i_s->path, sizeof(i_s->path)))
	return(1);
    sprintf(query_strng, "SELECT dev_type FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_string(query_strng, i_s->dev_type, sizeof(i_s->dev_type)))
	return(1);
    sprintf(query_strng, "SELECT dev_address FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_string(query_strng, i_s->dev_address, sizeof(i_s->dev_address)))
	return(1);
    sprintf(query_strng, "SELECT start_time FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_time(query_strng, &i_s->start_time))
	return(1);
    sprintf(query_strng, "SELECT last_update_time FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_time(query_strng, &i_s->last_update_time))
	return(1);
    sprintf(query_strng, "SELECT PID FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_int(query_strng, &i_s->PID))
	return(1);
    sprintf(query_strng, "SELECT user1 FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_string(query_strng, i_s->user1, sizeof(i_s->user1)))
	return(1);
    sprintf(query_strng, "SELECT user2 FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_string(query_strng, i_s->user2, sizeof(i_s->user2)))
	return(1);
    sprintf(query_strng, "SELECT parm1 FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_double(query_strng, &i_s->parm1))
	return(1);
    sprintf(query_strng, "SELECT parm2 FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", inst_name);
    if (read_mysql_double(query_strng, &i_s->parm2))
	return(1);
    return(0);
}


int mysql_inst_run_status(struct inst_struct *i_s)
{
  /*
    Opens the db, and reads in the values of the instrument 'inst_name' into 
    the structure 'i_s'.  This  structure is defined in "SC_db_interface.h".
  */
  
  char   query_strng[256];  
  int    my_errors = 0;
  
  sprintf(query_strng, "SELECT run FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", i_s->name);
  my_errors += read_mysql_int(query_strng, &i_s->run);
  
  sprintf(query_strng, "SELECT restart FROM `sc_insts` WHERE `name` = \"%s\" LIMIT 1", i_s->name);
  my_errors += read_mysql_int(query_strng, &i_s->restart);
  
  if (i_s->restart == 1)
    {
      sprintf(query_strng, "UPDATE `sc_insts` SET `restart` = 0 WHERE `name` = \"%s\" ", i_s->name);
      my_errors += write_to_mysql(query_strng);
      my_signal = SIGHUP;
    }
  if (i_s->run == 0)
    {
      my_signal = SIGTERM;
    }
  
  if (time(NULL) - INST_TABLE_UPDATE_PERIOD > i_s->last_update_time)
    {
      sprintf(query_strng, "UPDATE `sc_insts` SET `last_update_time` = %lu WHERE `name` = \"%s\" ",  (unsigned long)time(NULL), i_s->name);
      my_errors += write_to_mysql(query_strng);
      i_s->last_update_time = time(NULL);
    }
  fflush(stdout);
  fflush(stderr);
  return(my_errors);
}


