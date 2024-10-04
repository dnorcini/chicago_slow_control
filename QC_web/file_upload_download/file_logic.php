<?php 
  // file_logic.php
  // D.Norcini, UChicago, 2021
  // used in edit_ccd.php
  // following https://codewithawa.com/posts/how-to-upload-and-download-files-php-and-mysql
  //

  // connect to database (already done in edit_ccd.php)
  session_start();
  $req_priv = "full";
  include("db_login.php");

  // uploads file
  //if save button on the form is clicked	
  if (isset($_POST['upload'])) { 
    //name of the uploaded file
    $filename = $_FILES['myfile']['name'];

    //destination on server
    $destination = '/home/damic/ccdqc_upload/' . $filename;

    //get file extension
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    //physical file in temp directory (upload_tmp_dir in /etc/opt/remi/php56/php.ini)
    $file = $_FILES['myfile']['tmp_name'];
    $size = $_FILES['myfile']['size'];

    //conditions on file type, size...
     if (!in_array($extension, ['pdf', 'ini'])) {
        echo "File extension must be .ini (config files) or .pdf (reports).";
    	}
	elseif ($_FILES['myfile']['size'] > 10000000) { //10 Megabyte
        echo "File must be <10 MB!";
	}
	else {
             // move the uploaded temp file to destination
	     if (move_uploaded_file($file, $destination)){
             $sql = "INSERT INTO files (name, size, downloads) VALUES ('$filename', $size, 0)";
             	  if (mysql_query($conn, $sql)) {
                  echo "File uploaded successfully!";
		  }
		}
		else {
            	  echo "Failed to upload file.";
        	  }
              }
    }
?>
