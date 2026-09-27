<?php
// history_section.php
// Requires: $hist_type ('ccd'|'die'|'module'), $id (int)

include("db_login.php");

$hist_sub_id    = (int)$id;
$hist_type_safe = mysql_real_escape_string($hist_type);

if (isset($_POST['history_new'])) {
  $h_date     = mysql_real_escape_string(trim($_POST['hist_date']));
  $h_action   = mysql_real_escape_string(trim($_POST['hist_action']));
  $h_location = mysql_real_escape_string(trim($_POST['hist_location']));
  $h_reviewer = mysql_real_escape_string(trim($_POST['hist_reviewer']));
  $result = mysql_query("INSERT INTO `history` (`type`,`sub_id`,`date`,`action`,`location`,`reviewer`)
                           VALUES ('$hist_type_safe',$hist_sub_id,'$h_date','$h_action','$h_location','$h_reviewer')");
  if (!$result) die("Could not add history entry: " . mysql_error());
}

if (isset($_POST['history_update'])) {
  $h_entry    = (int)$_POST['history_edit_id'];
  $h_date     = mysql_real_escape_string(trim($_POST['hist_date']));
  $h_action   = mysql_real_escape_string(trim($_POST['hist_action']));
  $h_location = mysql_real_escape_string(trim($_POST['hist_location']));
  $h_reviewer = mysql_real_escape_string(trim($_POST['hist_reviewer']));
  $result = mysql_query("UPDATE `history`
                           SET `date`='$h_date', `action`='$h_action',
                               `location`='$h_location', `reviewer`='$h_reviewer'
                           WHERE `entry`=$h_entry AND `type`='$hist_type_safe' AND `sub_id`=$hist_sub_id
                           LIMIT 1");
  if (!$result) die("Could not update history entry: " . mysql_error());
}

$hist_editing_id = (isset($_POST['history_edit']) && is_numeric($_POST['history_edit']))
  ? (int)$_POST['history_edit'] : 0;

$result = mysql_query("SELECT * FROM `history`
                        WHERE `type`='$hist_type_safe' AND `sub_id`=$hist_sub_id
                        ORDER BY (`date` IS NULL OR `date` = '') ASC, `date` ASC, `entry` ASC");
if (!$result) die("Could not query history: " . mysql_error());

$hist_rows = [];
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
  $hist_rows[] = $row;

mysql_close($connection);
?>

<br>
<strong>History</strong> &nbsp; <small><i><?php echo htmlspecialchars($name ?? ''); ?></i></small>
<TABLE border="1" cellpadding="4" width="100%">
  <TR>
    <TH align="left" width="12%">Date</TH>
    <TH align="left" width="22%">Action</TH>
    <TH align="left" width="22%">Location</TH>
    <TH align="left" width="36%">Reviewer</TH>
    <TH align="left" width="8%"></TH>
  </TR>

  <?php foreach ($hist_rows as $hrow): ?>
    <?php if ($hist_editing_id == (int)$hrow['entry']): ?>
      <TR>
        <FORM action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] ?? ''); ?>" method="post">
          <input type="hidden" name="history_edit_id" value="<?php echo (int)$hrow['entry']; ?>">
          <TD><input type="text" name="hist_date"        value="<?php echo htmlspecialchars($hrow['date'] ?? '');        ?>" style="width:100%;box-sizing:border-box"></TD>
          <TD><input type="text" name="hist_action"      value="<?php echo htmlspecialchars($hrow['action'] ?? '');      ?>" style="width:100%;box-sizing:border-box"></TD>
          <TD><input type="text" name="hist_location"    value="<?php echo htmlspecialchars($hrow['location'] ?? '');    ?>" style="width:100%;box-sizing:border-box"></TD>
          <TD><input type="text" name="hist_reviewer"    value="<?php echo htmlspecialchars($hrow['reviewer'] ?? '');    ?>" style="width:100%;box-sizing:border-box"></TD>
          <TD align="center"><input type="submit" name="history_update" value="Save"></TD>
        </FORM>
      </TR>
    <?php else: ?>
      <TR>
        <TD><?php echo htmlspecialchars($hrow['date'] ?? '');        ?></TD>
        <TD><?php echo htmlspecialchars($hrow['action'] ?? '');      ?></TD>
        <TD><?php echo htmlspecialchars($hrow['location'] ?? '');    ?></TD>
        <TD><?php echo htmlspecialchars($hrow['reviewer'] ?? '');    ?></TD>
        <TD align="center">
          <FORM action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] ?? ''); ?>" method="post">
            <input type="hidden" name="history_edit" value="<?php echo (int)$hrow['entry']; ?>">
            <input type="submit" value="Edit">
          </FORM>
        </TD>
      </TR>
    <?php endif; ?>
  <?php endforeach; ?>

  <TR>
    <FORM action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] ?? ''); ?>" method="post">
      <TD><input type="text" name="hist_date"        placeholder="YYYY-MM-DD"  style="width:100%;box-sizing:border-box"></TD>
      <TD><input type="text" name="hist_action"      placeholder="Action"      style="width:100%;box-sizing:border-box"></TD>
      <TD><input type="text" name="hist_location"    placeholder="Location"    style="width:100%;box-sizing:border-box"></TD>
      <TD><input type="text" name="hist_reviewer"    placeholder="Reviewer"    style="width:100%;box-sizing:border-box"></TD>
      <TD align="center"><input type="submit" name="history_new" value="Add"></TD>
    </FORM>
  </TR>

</TABLE>