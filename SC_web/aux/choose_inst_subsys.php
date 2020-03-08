<?php
// choose_inst_subsys.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006, 2010
// james.nikkel@yale.edu
//

$unique_inst_subsys = make_unique($inst_subsys);

if (empty($_SESSION['choose_subsys']))
    $_SESSION['choose_subsys'] = $unique_inst_subsys[0];

if (!(empty($_POST['s_all'])))             // is the select all button has been pressed do this:
    $_SESSION['choose_subsys']= $unique_inst_subsys;

if (!(empty($_POST['choose_subsys_P'])))
  $_SESSION['choose_subsys'] = $_POST['choose_subsys_P'];

if (!(empty($_POST['h_all'])))
    $_SESSION['choose_subsys'] = array_combine($inst_names, make_new_ones_array(count($inst_names)));


echo ('<TABLE border="1" cellpadding="2" width=100%>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('<input type="submit" name="s_all" value="Select All" title="Select all categories listed to the right" style="font-size: 10pt">');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('Choose Subsystem: &#160 &#160');
foreach ($unique_inst_subsys as $u_i_s)
{
    echo ($u_i_s.':<input type="checkbox" name="choose_subsys_P[]" value="'.$u_i_s.'" ');
    if (in_array($u_i_s, $_SESSION['choose_subsys']))
	echo ('checked="checked" />  &#160 &#160; ');
    else 
	echo ('/>  &#160 &#160');
    echo ('</font>');
}
echo ('<input type="image" src="pixmaps/edit.png" alt="Change" title="Refresh page with selected inst subsys">');
echo ('</TH>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TH>');
echo ('<input type="submit" name="h_all" value="Deselect All" title="Deselect all categories listed to the left" style="font-size: 10pt">');
echo ('</TH>');
echo ('</FORM>');

echo ('</TABLE>');
?>
