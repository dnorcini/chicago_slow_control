<?php
// slow_control_config.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2010.
// james.nikkel@yale.edu
//
session_start();
$never_ref = 1;
$req_priv = "config";
include("db_login.php");

if (isset($_POST['text_colour']))
{
    mysql_close($connection);
    include("master_db_login.php");
    $query = "UPDATE `globals` SET `string1` = \"".$_POST['text_colour']."\" WHERE `name` = \"web_text_colour\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (isset($_POST['bg_colour']))
{
    mysql_close($connection);
    include("master_db_login.php");
    $query = "UPDATE `globals` SET `string1` = \"".$_POST['bg_colour']."\" WHERE `name` = \"web_bg_colour\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

//////////////////////////////////////////////////////////////////////
if (!empty($_POST['new_priv']))
{
    mysql_close($connection);
    include("master_db_login.php");
    if (!get_magic_quotes_gpc())
    {
	$_POST['new_priv'] = addslashes($_POST['new_priv']);
	$_POST['new_priv_host'] = addslashes($_POST['new_priv_host']);
    }
    $_POST['new_priv'] = preg_replace('/\s+/', '', $_POST['new_priv']);
    $_POST['new_priv_host'] = preg_replace('/\s+/', '', $_POST['new_priv_host']);

    $query = "INSERT into `user_privileges` (`name`, `allowed_host`) VALUES ('".$_POST['new_priv']."', '".$_POST['new_priv_host']."')"; 
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (isset($_POST['del_priv']))
{
    mysql_close($connection);
    include("master_db_login.php");
    if (!get_magic_quotes_gpc())
    {
      $_POST['del_priv'] = addslashes($_POST['del_priv']);
      $_POST['del_priv_host'] = addslashes($_POST['del_priv_host']);
    }
    if ( (strcmp($_POST['del_priv'], "config") == 0) ||  (strcmp($_POST['del_priv'], "admin") == 0) ||
	 (strcmp($_POST['del_priv'], "basic") == 0) ||  (strcmp($_POST['del_priv'], "full") == 0))
    {
	echo("You can't delete that privilege.");
    }
    else
    {
	$query = "DELETE FROM `user_privileges` WHERE `name` = \"".$_POST['del_priv']."\"  AND `allowed_host` = \"".$_POST['del_priv_host']."\" LIMIT 1";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
    }
 }

//////////////////////////////////////////////////////////////////////

if (!empty($_POST['new_sens_type']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    if (!get_magic_quotes_gpc())
      $_POST['new_sens_type'] = addslashes($_POST['new_sens_type']);
    $_POST['new_sens_type'] = preg_replace('/\s+/', '', $_POST['new_sens_type']);

    $query = "INSERT into `sc_sensor_types` (`name`)  VALUES ('".$_POST['new_sens_type']."')"; 

    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());
  }

if (isset($_POST['del_sens_type']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    $_POST['del_sens_type'] = (int)$_POST['del_sens_type'];
    
    $query = "DELETE FROM `sc_sensor_types` WHERE `num` = \"".$_POST['del_sens_type']."\" LIMIT 1";
    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());
  }

//////////////////////////////////////////////////////////////////////

if (!empty($_POST['new_lug_cat']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    if (!get_magic_quotes_gpc())
      $_POST['new_lug_cat'] = addslashes($_POST['new_lug_cat']);
    $_POST['new_lug_cat'] = preg_replace('/\s+/', '', $_POST['new_lug_cat']);

    $query = "INSERT into `lug_categories` (`category`, `subcategory`)  
              VALUES ('".$_POST['new_lug_cat']."', 'General')"; 

    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());
  }

if (!empty($_POST['new_lug_subcat']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    if (!get_magic_quotes_gpc())
      $_POST['new_lug_subcat'] = addslashes($_POST['new_lug_subcat']);
    $_POST['new_lug_subcat'] = preg_replace('/\s+/', '', $_POST['new_lug_subcat']);

    $query = "INSERT into `lug_categories` (`category`, `subcategory`)  
              VALUES ('".$_POST['new_lug_subcat_cat']."', '".$_POST['new_lug_subcat']."')"; 

    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());
  }
    
if (isset($_POST['del_cat']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    if (!get_magic_quotes_gpc())
      {
	$_POST['del_cat'] = addslashes($_POST['del_cat']);
	$_POST['del_subcat'] = addslashes($_POST['del_subcat']);
      }
    $query = "DELETE FROM `lug_categories` WHERE `category` = \"".$_POST['del_cat']."\"  AND `subcategory` = \"".$_POST['del_subcat']."\" LIMIT 1";
    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());
      
  }

//////////////////////////////////////////////////////////////////////


include("slow_control_page_setup.php");
mysql_close($connection);

/////////////   start HTML here:
echo ('<br>');
echo ('<br>');


echo ('<TABLE border="0" cellpadding="4" width=100%>');
echo ('<TR>');
echo ('<TH>');
echo ('<br>');
echo ('<FORM action="slow_control_sens_config.php" method="post">');
echo ('<BUTTON type="submit" style="font-size: 25pt"> Edit Sensor Configuration </BUTTON>');
echo ('</FORM>');
echo ('</TH>');

echo ('<TH>');
echo ('<br>');
echo ('<FORM action="slow_control_inst_config.php" method="post">');
echo ('<BUTTON type="submit" style="font-size: 25pt"> Edit Instrument Configuration </BUTTON>');
echo ('</FORM>');
echo ('</TH>');
echo ('</TR>');
echo ('</TABLE>');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo ('<TABLE border="0" cellpadding="4" width=100%>');
echo ('<TR>');
echo ('<TH colspan="2">');
echo ('<br>');
echo ('<br>');
echo ('<br>');
echo ('<hr>');
echo ('Set colours for web front-end.  These are set globally, not just this session!');
echo ('</TH>');
echo ('</TR>');

echo ('<TR>');
echo ('<TH>');
echo ('<br>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<BUTTON type="submit" style="font-size: 15pt" title="Set new text colour"> Set text colour </BUTTON>');
echo ('<SELECT name="text_colour" style="font-size: 15pt; color: '.$_SESSION['textcolour'].'">');
foreach ($html_colours as $colour)
{
    echo('<option ');
    if (strcmp($colour, $_SESSION['textcolour']) == 0)
	echo ('selected="selected"');
    echo(' value="'.$colour.'" style="color: '.$colour.'"> '.$colour.'</option>');
}
echo ('</SELECT>');
echo ('</FORM>');
echo ('</TH>');

echo ('<TH>');
echo ('<br>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<BUTTON type="submit" style="font-size: 15pt" title="Set new background colour"> Set background colour </BUTTON>');
echo ('<SELECT name="bg_colour" style="font-size: 15pt; color: '.$_SESSION['bgcolour'].'">');
foreach ($html_colours as $colour)
{
    echo('<option ');
    if (strcmp($colour, $_SESSION['bgcolour']) == 0)
	echo ('selected="selected"');
    echo(' value="'.$colour.'"style="color: '.$colour.'" > '.$colour.'</option>');
}
echo ('</SELECT>');
echo ('</FORM>');
echo ('</TH>');
echo ('</TR>');
echo ('</TABLE>');

//////////////////////////////////////////////////////////////////////////////////////////////////////////////

echo ('<br>');
echo ('<hr>');

echo ('<TABLE border="0" cellpadding="0">');   // set up columns for tables:
echo ('<TR>');  // new row

///////////////////////////////////////  privilege table 
echo ('<TD>');  // new cell
echo ('<TABLE border="2" cellpadding="1">'); 
echo ('<TR>');
echo ('<TH>');
echo ('Privilege Names');
echo ('</TH>');
echo ('<TH>');
echo ('Allowed Hosts (all or by IP)');
echo ('</TH>');
echo ('</TR>');

echo ('<TR>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TD>');
echo ('<input type="text" name="new_priv" size = 16>');
echo ('</TD>');
echo ('<TD>');
echo ('<input type="text" name="new_priv_host" size = 16>');
echo ('<BUTTON type="submit"> New </BUTTON>');
echo ('</TD>');
echo ('</FORM>');
echo ('</TR>');

foreach ($privilege_array as $priv)
{
    echo ('<TR>');
    echo ('<TD>');
    echo ($priv);
    echo ('</TD>');
    echo ('<TD>');
    echo ('<TABLE border="0" cellpadding="1">');
    for ($i = 0; $i < count($allowed_host_array[0]); $i++)
    {
        if (strcmp($allowed_host_array[0][$i], $priv) == 0)
	{
	    echo ('<TD>');
	    echo (' '.$allowed_host_array[1][$i]);
	    echo ('</TD>');
	    echo ('<TD>');
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<input type="image" src="pixmaps/drop.png" title="Delete this privilege/host entry">');
	    echo ('<input type="hidden" name="del_priv" value='.$priv.'>');
	    echo ('<input type="hidden" name="del_priv_host" value='.$allowed_host_array[1][$i].'>');
	    echo ('</FORM>');
	    echo ('</TD>');
	}
    }
    echo ('</TABLE>');
    echo ('</TD>');
    echo ('</TR>');
}
echo ('</TABLE>');
echo ('</TD>');

///////////////////////////////////////  sensor type table 
echo ('<TD>');
echo ('<TABLE border="2" cellpadding="1">');  
echo ('<TR>');
echo ('<TH>');
echo ('Sensor Types');
echo ('</TH>');
echo ('</TR>');

echo ('<TR>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TD>');
echo ('<input type="text" name="new_sens_type" size = 16>');
echo ('<BUTTON type="submit"> New </BUTTON>');
echo ('</TD>');
echo ('</FORM>');
echo ('</TR>');

foreach ($sens_type_array_index as $index)
{
  echo ('<TR>');
  echo ('<TD>');
  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
  echo ($sens_type_array[$index].'  <input type="image" src="pixmaps/drop.png" title="Delete this sensor type entry">');
  echo ('<input type="hidden" name="del_sens_type" value='.$index.'>');
  echo ('</FORM>');	    
  echo ('</TD>');
  echo ('</TR>');
}
echo ('</TABLE>');
echo ('</TD>');

///////////////////////////////////////  category / subcategory table
echo ('<TD>');  // new cell
echo ('<TABLE border="2" cellpadding="1">');  
echo ('<TR>');
echo ('<TH>');
echo ('Logbook Categories');
echo ('</TH>');
echo ('<TH>');
echo ('Subcategories');
echo ('</TH>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD colspan="2">');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="text" name="new_lug_cat" size = 24>');
echo ('<BUTTON type="submit"> New </BUTTON>');
echo ('</FORM>');
echo ('</TD>');
echo ('</TR>');

foreach ($lug_cat_array as $lug_cat)
{
  echo ('<TR>');
  echo ('<TD>');
  echo ($lug_cat);
  echo ('</TD>');
  echo ('<TD>');
  echo ('<TABLE border="0" cellpadding="1">');
  for ($i = 0; $i < count($lug_subcat_array[0]); $i++)
    {
      if (strcmp($lug_subcat_array[0][$i], $lug_cat) == 0)
	{
	  echo ('<TD>');
	  echo (' '.$lug_subcat_array[1][$i]);
	  echo ('</TD>');
	  echo ('<TD>');
	  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	  echo ('<input type="image" src="pixmaps/drop.png" title="Delete this entry">');
	  echo ('<input type="hidden" name="del_cat" value='.$lug_cat.'>');
	  echo ('<input type="hidden" name="del_subcat" value='.$lug_subcat_array[1][$i].'>');
	  echo ('</FORM>');
	  echo ('</TD>');
	}
    }
  echo ('<TD>');
  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
  echo ('<input type="text" name="new_lug_subcat" size = 16>');
  echo ('<input type="hidden" name="new_lug_subcat_cat" value='.$lug_cat.'>');
  echo ('<BUTTON type="submit"> New </BUTTON>');
  echo ('</FORM>');
  echo ('</TD>');
  echo ('</TABLE>');
  echo ('</TD>');
  echo ('</TR>');
}
echo ('</TABLE>');
echo ('</TD>');




echo ('</TR>');
echo ('</TABLE>');
echo(' </body>');
echo ('</HTML>');
?>
