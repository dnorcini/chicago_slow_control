<?php
	$hostname = 'localhost';
	$username = 'control_user';
	$password = 'MyLife4AiurUser';
	$database = 'control';

	$link = mysql_connect("$hostname", "$username", "$password");
		if (!$link) {
			echo "<p>Could not connect to the server '" . $hostname . "'</p>\n";
        	echo mysql_error();
		}else{
			echo "<p>Successfully connected to the server '" . $hostname . "'</p>\n";
		}
	if ($link && !$database) {
		echo "<p>No database name was given. Available databases:</p>\n";
		$db_list = mysql_list_dbs($link);
		echo "<pre>\n";
		while ($row = mysql_fetch_array($db_list)) {
     		echo $row['Database'] . "\n";
		}
		echo "</pre>\n";
	}
	if ($database) {
    $dbcheck = mysql_select_db("$database");
		if (!$dbcheck) {
        	echo mysql_error();
		}else{
			echo "<p>Successfully connected to the database '" . $database . "'</p>\n";
			// Check tables
			$sql = "SHOW TABLES FROM `$database`";
			$result = mysql_query($sql);
			if (mysql_num_rows($result) > 0) {
				echo "<p>Available tables:</p>\n";
				echo "<pre>\n";
				while ($row = mysql_fetch_row($result)) {
					echo "{$row[0]}\n";
				}
				echo "</pre>\n";
			} else {
				echo "<p>The database '" . $database . "' contains no tables.</p>\n";
				echo mysql_error();
			}
		}
	}
?>
