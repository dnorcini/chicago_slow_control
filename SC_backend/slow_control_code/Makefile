
SUBDIRS = lib \
	SC_Watchdog \
	alarm_alert_system \
	alarm_trip_system \
	ADAM6000 \
	Alicat \
	AMI286 \
	Arduino_ADC \
	Arduino_Counter \
	Arduino_IO \
	BK_1856D_counter \
	CAEN_HV \
	cams \
	CDL204 \
	CP2800 \
        disk_free \
	DWP_scale \
	CryoCon18C \
        CryoCon24C \
	CryoCon34 \
	CryoCon44 \
        CryoMag_LM500 \
        DiffP \
	Edwards_TIC \
        GFM373 \
	Hornet \
	Keithley485 \
	LeCroy1458 \
        LevelBox \
	Listen_Daemon \
	LS218 \
	LS330 \
	LS330Keithley \
	LS331 \
	LS335_eth \
	LUX_ACRS \
	LUX_DAQ \
	MetOne_PC \
	MKS925C \
	MP320 \
	NI_PCI_622X \
	ORTEC974 \
	PDR2000 \
	postproc \
        Remote_Watchdog \
	SL130 \
        SPS125 \
	SRC_Control \
	SRS350 \
	SRS_RGA \
	SuperLogics \
	TPG26X \
	Transcell_Scale \
        TSctrl \
	TTi_PowerSupply \
	UTI \
	UTILM_RH \
	Watlow_EZ_TC \
        YLM001 \

.PHONY: $(SUBDIRS)


all clean install:
	for dir in $(SUBDIRS); do \
	  make -C $$dir $@; \
	done


