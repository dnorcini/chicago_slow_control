
SUBDIRS = lib \
	SC_Watchdog \
	alarm_alert_system \
	alarm_trip_system \
	AgilentE3646 \
	APC3000 \
	cams \
	CenterThree \
	disk_free \
	CryoTelGT_AVC \
	CTC100 \
	HiCube80 \
	KeysightE36312A \
	LS218 \
	LS321_eth \
	LS330 \
	LS331 \
	LS335_eth \
	LS336_eth \
	postproc \
	Raritan_PX3 \
	Remote_Watchdog \
	SRSDC205 \
        TPG261 \
	TPG26X \
	WIENER_VME \

.PHONY: $(SUBDIRS)


all clean install:
	for dir in $(SUBDIRS); do \
	  make -C $$dir $@; \
	done


