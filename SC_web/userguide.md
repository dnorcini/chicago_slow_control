# Slow Control Front-End: User Guide
> Author: Cinyu Zhu, Johns Hopkins, March 2026

Short guide to basic operations in the slow control web interface for **end users**.
## 1. User Account
### 1.1 Register: 
Ask an admin (Cinyu Zhu, Danielle Norcini, Radomir Smida, etc.) to create an account for you.
- The admin will create the account with a username (no spaces, ≤16 characters). and an initial password.
- After that, you can log in and edit your own profile from **Users** → **Edit** next to your name. You can edit Full Name, Password, Email, SMS address, Phone, On Call, Shift Status.

### 1.2 Login and Logout
- **Log in:** In the top bar, enter Username and Password, then click the Log in button (or press Enter). If you see these fields still after logged in, you may have `guest` in your privilege list; it doesn't hurt.
- **Log out:** Click the Log out icon in the top-right of the header.

### 1.3 Privilege and Status
- **Privileges** are a comma-separated list (e.g., `basic, full, guest`). Each page requires one privilege; you need that privilege in your list to see the link and open the page. They are additive (e.g. having both basic and full gives you all basic and full pages).
- **What each privilege grants:**
  - **guest**, **basic** – **Plots**, **Text** (view data only).
  - **full** – **MPlot**, **Scatter**, **Sys Log**, **Runs**, **Users**, **Alarms** (view and acknowledge), **Control**, **Edit User** (own or others’ profile). For **Control**, the page requires full, and each settable sensor may also require an additional matching privilege, which is set in Config → sensor.
  - **config** – Edit Instrument Configuration, Edit Sensor Configuration (i.e. delete/reset instrument and sensor).
  - **admin** – **Delete user**, **add new user**, and **set any user’s privileges** in Edit User. 
- **On Call** – When On Call is set, your **email** and **SMS** addresses are used for alarm alerts: first alerts go only to on-call users; if the alarm is not acknowledged, later alerts escalate to all users (see Alarm section).
- **Shift Status** – e.g., Off, On Shift, Shift Leader, Shift Manager, System Manager. It only affects the order of users in the `user` panel.

### 1.4 Email, SMS address, and Phone Number
- **Email** and **SMS** in your profile are the addresses used by the alarm alert system to send notifications when an alarm is triggered. Both email and SMS using the system mail to send out alarm message. **The SMS field should be an email-to-SMS gateway address** (e.g., 123-456-7890@tmomail.net for T-Mobile), not a plain phone number; the mail is sent to that address, and the carrier delivers it as a text. There is no separate SMS API or phone dialer. 
- **On-call users** receive the first alerts (email and SMS). If the alarm is still not acknowledged after escalation (about 10 minutes), all users with a valid email/SMS get the alert. Ensure these fields are correct if you are on call or need to receive alarms.
- The Phone number field in your profile is **not used** by the alarm system at all; it is for display and contact reference only. 

## 2. Monitor data
### 2.1 Refresh time
- **Page refresh time**: In the header, use the Refresh time dropdown (e.g., 10s, 30s, 1m, 3m, 10m, 30m, or never) to choose how often the browser reloads the current page to retrieve data from the SQL database. The new refresh time works only when you submit the form by hitting the `enter` key or clicking the reload icon.
- After submitting a form (e.g. refresh time), the page restores your previous scroll position.
- You can click the reload icon to refresh manually.
- **Instrument/sensor update time** is a separate thing: each sensor has an update_period (in seconds) stored in the database. The backend instrument processes read sensors and writes to the DB for that period. Changing the dropdown only affects how often the web page reloads; it does not change how often instruments read or write data. See more about sensor update time in the Config section.
### 2.2 Text
- The **Text** tab shows the current (latest) value of each sensor, as stored in the database. Values are grouped by sensor type; you can choose which types to show.
- Settable sensors are not shown on Text (they appear on Control).
### 2.3 Plots
- **Plots** show a time series of sensor values. Use the **Begin time/date** and **End time/date** fields (or the arrow buttons: to start, to end) to set the time window. You can enter dates/times or use relative shortcuts (e.g., `-1h` from the end of data). Careful: selecting plotting data during a long period of time (e.g., months) may result in a long SQL query time. For queries spanning more than 90 days, the system will ask for confirmation before running.
- For a single-sensor detailed view, click the Calc (calculator) iconon the right side of each plot, you can:
  - Use **Zoom in/out** and the other click modes (move begin/central/end time/Selects value) to adjust the time window or check the exact value of a given time.
  - Calculate the log/integral/average/linear regression of the data
  - Download the data in your time frame as a text file
### 2.4 MPlot and Scatter
- **MPlot** – Multi-plot view: several sensors’ time-series in one page with a common time axis. Useful to compare trends.
- **Scatter** – Scatter view: plot one sensor vs another (e.g. X vs Y). 

## 3. Control
- The **Control** tab lists sensors that are **settable** (setpoints, on/off, etc.).
- Select the new value (or choose from discrete options) and submit. Your change is written to the database; the instrument process for that sensor reads it and applies it to the hardware. For **critical** sensors, a confirmation page is shown before the new value is applied. 

## 4. Alarm
### 4.1 Trigger an alarm
An alarm is triggered when a sensor value (or rate) goes outside configured limits. The alarm_trip_sys backend process checks sensors and sets an “alarm tripped” state.

When an alarm is triggered, The Master_alarm global is set. The alarm_alert_sys will:
  - Sends messages to **on-call users** (email and SMS).
  - If the alarm is not acknowledged, after about 10 minutes, it sends messages again.
  - On the web front-end, the alarm link and sensor text turn red, and an alert siren sound will play on the speaker. (The sound will stop only when the alarm is acknowledged and the webpage is refreshed)
  - The event is also written to the Sys Log (msg_log).

### 4.2 Set up an alarm
- Setup is done per sensor in Alarms page, where alarm setpoints are editable by full users. You can: 
  - enable or disable low and/or high alarm (value or rate)
  - set the setpoint values (e.g. `al_set_val_low`, `al_set_val_high`), 
  - (optional) a grace period.



### 4.3 Acknowledge an alarm
- An alarm should be acknowledged **only after** all of the following conditions are met:
  1. The operator understands the cause of the alarm.
  2. The system has been checked (locally if required).
  3. Any necessary corrective or protective action has been taken.
  4. The alarm condition is either resolved or confirmed to be expected⠀
  5. Acknowledgement does **not** mean the issue is fixed. It only indicates that a responsible operator has reviewed the condition.
- If an alarm is active, a large red button **“Acknowledge Current Alarm”** is shown on the top of every page. Click it to acknowledge the alarm and refresh the page to stop the siren. The acknowledgement with your username will be recorded in the system log.

## 5. Sys Log

- The **Sys Log** (table `msg_log`) stores timestamped messages: **Alarm**, **Config.** (instrument/sensor changes), **Shifts** (shift/on-call changes), **Setpoint**, **Error**, **Alert**, etc. Each entry has: time, message text, type, and whether it’s an error.
- **How to access:** Click **Sys Log** in the header. You can filter by **message type** (checkboxes), limit the number of messages, search by text, and optionally show only messages in the current plot time range (“From plot times”). You can page through the log (navigate by page) instead of loading all messages at once.

## 6. Config
Changing things here can affect instruments and sensors. Usually only touched when setting up an instrument.
### 6.1 Config instrument
- From **Config**, click **Edit Instrument Configuration** to open the instrument list. For each instrument, there is:
  - **Controlled by Watchdog?** – If checked, the watchdog is allowed to start this instrument when `run=1` and PID=-1, and to kill/restart it if it’s stuck (no heartbeat in time) or still running when `run=0`.
  - **Restart** – Click to request a restart: the backend sets `restart=1`; the instrument process sees it, clears it, and exits with SIGHUP so the watchdog can start it again. Use when the process is hung, or you want a clean restart. You can **restart all instruments** in one action from sensor_config page.
  - **Run** – Run checked = should be running; unchecked = should be stopped. The watchdog starts instruments with `run=1` , but no process is running; it kills instruments that have `run=0` and haven’t exited. (Watchdog itself is always set as “run”.)
  - **Device address**: usually ip address of the instrument, used in the backend code.
  - **Delete** / **Reset** – Delete removes the instrument from the table; Reset clears PID and start/last_update times .
- **Watchdog summary:** The **SC_Watchdog** process periodically: (1) starts instruments that have `run=1`, `PID=-1`, and `WD_ctrl=1`; (2) kills instruments that should be running but haven’t updated in time (no heartbeat); (3) kills instruments that should be stopped but are still running. So “Controlled by Watchdog” means the watchdog can start and kill this instrument; 
- **Run** and **Restart** are then the main user controls. To apply changes to instrument or sensor fields that interact with the backend program(ip address, update period, etc.), you must restart the instrument.
### 6.1.1 Backend Processes
Each instrument has a corresponding backend program that communicates with the instrument and takes care of the readout (read_sensor) and control (set_sensor). When an instrument is running, it should have **one and only one** process active with the pid shown on the config page. You can check the active processes from the terminal by `pidof <instrument>` (e.g., `pidof CenterThree` for the pressure gauge). If there is more than one pid, kill the extra processes by `sudo kill -9 <pid>`. Kill those different from what's shown on the config instrument page. Or kill them all and then restart the instrument. **This is the cause of strange readouts most of the time.**

If there isn't any pid active, but the instrument is set as run, check all the instrument and sensor configs are set correctly, restart the instrument in the config page, and there should be a new pid appear in the config page as well as in the output of `pidof <instrument>`. If the problem persists, also check the `/dev/shm/stderr.<instrument>` and take further actions. **Stderr** is also available from the web: 'log' link from the instrument config page to view each instrument’s stderr output.
### 6.2 Config sensor:
- Sensors are **linked to an instrument** in the database; the instrument backend program reads or sets their values and writes to the sensor tables. In **Config** → **Edit Sensor Configuration** you can change description, type, subtype, hide/show, settable, control privilege, update_period (sensor refresh time), and alarm setpoints/grace. 
- You can edit the **update interval** (update_period) for all—in one go then restart all the instruments from the top of sensor config page if needed.
- Sensors appear on the Text (and Plots) tabs for read-only, and on Control if they are settable.
### 6.3 Frontend Color
- In Config (main config page, not instrument/sensor), you can set web text colour and web background color (e.g., for dark/light theme). These are applied to the front-end globally across devices.

## 7. Other Unused features
### 7.1 Logbook
- **LogBook** – Logbook entries (categories, subcategories, run number, description). Use Add New LogBook Entry to create an entry. 
### 7.2 Runs
- **Runs** – Tracks “runs” (start/end time, file path, note). You can start a **new run** and set the plot time range from the current run. 
### 7.3 Webcam
- **Cams** – Webcam viewer.