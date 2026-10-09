<?php
// index.php
// Welcome page. James Nikkel, 2016.
require __DIR__ . '/bootstrap.php';
require_priv('guest');

page_start();
?>
<div align="center">
<br><br><br><br>
<font size="+2">
Welcome to the Quality Control drop page!
<br>
If you have a username and password, enter them in the fields above and click the log in button or press enter.
<br>
If you do not, contact an admin for this experiment.
<br>
Have a lovely day.
</font>
<br><br>
</div>
<?php page_end();
