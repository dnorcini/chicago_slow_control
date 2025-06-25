Rad7_SC="python3 /home/damicm/SC_backend/slow_control_code/RAD7/rad7_sc.py"
wait_time=600

while :
do
	$Rad7_SC
	echo "Press [CTRL+C] to stop."
	sleep $wait_time
done
