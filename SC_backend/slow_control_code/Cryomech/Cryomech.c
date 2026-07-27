/* Program for reading and controlling CryoMech CPA2850 Compressor*/
/* Cinyu Zhu, Hopkins, 2026*/
#include "SC_db_interface.h"
#include "SC_aux_fns.h"
#include "SC_sensor_interface.h"
#include "modbus.h"

// When called with the watchdog, a specific instrument is always given even if it is the same as the default. 
#define INSTNAME "Cryomech" // The default instrument name (used for database entries or configuration)
#define SLAVE    1 //0x01 
#define mod_port 502 // The default port 

modbus_param_t inst_dev;

#define _def_set_up_inst
// This initializes and connects to the Modbus device:
int set_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)  
{
    // modbus_init_tcp: Initializes a Modbus TCP connection with the device address and port.
    modbus_init_tcp(&inst_dev, i_s->dev_address, mod_port);
    
    // modbus_connect: Attempts to connect. If unsuccessful, it exits with an error.
    if (modbus_connect(&inst_dev) == -1) 
    {
      fprintf(stderr, "ERROR Connection failed\n");
      exit(1);
    }
    
    return(0);
}

#define _def_clean_up_inst 
// This function Closes the Modbus connection using modbus_close.
void clean_up_inst(struct inst_struct *i_s, struct sensor_struct *s_s_a)
{
  modbus_close(&inst_dev);
}

#define _def_read_sensor
// This function reads data from the Cryo based on the register in the modbus input register table (see on user manual)
// mbpoll -m tcp -t 3 -0 -r 1 -c 60 -1 192.168.1.93 //read first 60 registers
// you can also read the big-endian encoded float values from register 8-27, see details in radomir's pythons script

int read_sensor(struct inst_struct *i_s, struct sensor_struct *s_s, double *val_out)
{
    const int start_addr = (int)s_s->num; 
    const int max_tries = 5;

    if (strncmp(s_s->subtype, "cryoread", strlen("cryoread")) != 0) {
        fprintf(stderr, "Unsupported subtype: %s\n", s_s->subtype);
        return 1; // Failure
    }
    if (s_s->num != 1  && s_s->num != 2  && s_s->num != 34 &&    
        s_s->num != 36 && s_s->num != 37 && s_s->num != 40 && 
        s_s->num != 41 && s_s->num != 42 && s_s->num != 43 &&  
        s_s->num != 44 && s_s->num != 46 && s_s->num != 48 && s_s->num != 49 &&   
        s_s->num != 50 && s_s->num != 52 && s_s->num != 54){
        fprintf(stderr, "Unsupported modbus register address: %s\n", s_s->num);
        return 1; // Failure
    }

    for (int tries = 0; tries < max_tries; ++tries) {
        if (s_s->num == 50 || s_s->num == 52 || s_s->num == 54){
            // read two registers
            uint16_t regs[2] = {0, 0};
            int ret = read_input_registers(&inst_dev, SLAVE, start_addr, 2, regs);

            if (ret == 2) { // success
                // Combine 2x16-bit registers into a 32-bit value, 
                // pay special attention to the order, it writes in [50] register first
                uint32_t u32 = ((uint32_t)regs[1] << 16) | (uint32_t)regs[0]; 
                if (s_s->num == 50){
                    // Hours Of Operation in 1/10th Scale (int32)
                    *val_out = ((double)(int32_t)u32) / 10.0;
                    return 0;
                }
                else{
                    // convert the value of 2 registers to binary, then return
                    // (val_out is a double, so we return the numeric value,
                    //  and print/log the binary representation for debugging)

                    char bits[33];
                    for (int b = 31; b >= 0; --b) {
                        bits[31 - b] = ((u32 >> b) & 1u) ? '1' : '0';
                    }
                    bits[32] = '\0';

                    // fprintf(stderr,
                    //         "Sensor num %d: u32=0x%08X, bits=%s (regs: 0x%04X 0x%04X)\n",
                    //         s_s->num, u32, bits, regs[0], regs[1]);

                    *val_out = (double)u32;
                    return 0;
                }
            }
            else{
                //reading failed message
                fprintf(stderr, "read_input_registers failed (num=%d, start_addr=%d, count=2, ret=%d)\n",
                s_s->num, start_addr, ret);
                msleep(100);
                continue;
            }
        }

        else{
            //read 1 register
            uint16_t reg_val = 0;
            int ret = read_input_registers(&inst_dev, SLAVE, start_addr, 1, &reg_val);
            // On success, this function returns the number of registers read (1 here)
            if (ret == 1) {
                if (s_s->num == 34) {
                    *val_out = ((double)reg_val) / 100.0;
                } else if (s_s->num == 1 || s_s->num == 2 ){
                    *val_out = (double)reg_val;
                }
                else{
                    *val_out = ((double)reg_val) / 10.0;
                }
                return 0; // success
            }
            msleep(100);
            }
    }

    fprintf(stderr, "Failed to read input reg addr=%d (manual ~%d) subtype=%s after %d attempts.\n",
            start_addr, 30001 + start_addr, (s_s->subtype ? s_s->subtype : "unknown"), max_tries);
    return 1; // failure
}

#define _def_set_sensor
int set_sensor(struct inst_struct *i_s, struct sensor_struct *s_s)
{
    if (strncmp(s_s->subtype, "cryoset", 7) != 0)
    {
        fprintf(stderr, "Cryomech: Unsupported sensor subtype for setting: %s\n", s_s->subtype);
        return 1;
    }
    //   1   -> ON
    //   255 -> OFF
    int write_val = (int)(s_s->new_set_val);
    if (write_val != 1 && write_val != 255)
    {
        fprintf(stderr, "Cryomech: Invalid set value %d (allowed: 1=ON, 255=OFF)\n", write_val);
        return 1;
    }

    // mbpoll test:
    //   mbpoll ... -t 4 -0 -r 1 $IP <value>
    // So: holding register, address 1 (as used in your test).
    const int reg_addr = 1;
    int ret = preset_single_register(&inst_dev, SLAVE, reg_addr, write_val);

    if (ret <= 0)
    {
        fprintf(stderr,
                "Cryomech: Failed to write holding register %d with value %d (ret=%d)\n",
                reg_addr, write_val, ret);
        return 1;
    }
    return 0;
}


#include "main.h"

