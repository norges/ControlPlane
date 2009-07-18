<?php

	/*
	 * Author: Andreas Linde <mail@andreaslinde.de>
	 *
	 * Copyright (c) 2009 Andreas Linde & Kent Sutherland. All rights reserved.
	 * All rights reserved.
	 *
	 * Permission is hereby granted, free of charge, to any person
	 * obtaining a copy of this software and associated documentation
	 * files (the "Software"), to deal in the Software without
	 * restriction, including without limitation the rights to use,
	 * copy, modify, merge, publish, distribute, sublicense, and/or sell
	 * copies of the Software, and to permit persons to whom the
	 * Software is furnished to do so, subject to the following
	 * conditions:
	 *
	 * The above copyright notice and this permission notice shall be
	 * included in all copies or substantial portions of the Software.
	 *
	 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
	 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
	 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
	 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
	 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
	 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
	 * OTHER DEALINGS IN THE SOFTWARE.
	 */

//
// Shows a list of crashes for a given crash group
//
// Shows all crashes for a given crash group. You can download each crash
// from this view, or see all the relevant information about this crash
// that is available
//

require_once('../config.php');

function end_with_result($result)
{
	return '<html><body>'.$result.'</body</html>'; 
}

$allowed_args = ',groupid,bundleidentifier,version,';

$link = mysql_connect($server, $loginsql, $passsql)
    or die(end_with_result('No database connection'));
mysql_select_db($base) or die(end_with_result('No database connection'));

foreach(array_keys($_GET) as $k) {
    $temp = ",$k,";
    if(strpos($allowed_args,$temp) !== false) { $$k = $_GET[$k]; }
}

if (!isset($groupid)) $groupid = "";
if (!isset($bundleidentifier)) $bundleidentifier = "";
if (!isset($version)) $version = "";

if ($bundleidentifier == "" && $version == "") die(end_with_result('Wrong parameters'));

$whereclause = "";
$pagelink = "";
if ($groupid == "") {
	$pagelink = '?bundleidentifier='.$bundleidentifier.'&version='.$version;
	$whereclause = " WHERE bundleidentifier = '".$bundleidentifier."' AND version = '".$version."'";
} else {
	$pagelink = '?bundleidentifier='.$bundleidentifier.'&version='.$version.'&groupid='.$groupid;
	$whereclause = " WHERE groupid = ".$groupid;
}

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML  4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
echo '<html><head><link rel="stylesheet" type="text/css" href="body.css"></head><body>';

if (!$acceptallapps)
	echo '<a href="app_name.php">Apps</a> - ';

echo '<a href="app_versions.php?bundleidentifier='.$bundleidentifier.'">Versions</a> - <a href="groups.php?bundleidentifier='.$bundleidentifier.'&version='.$version.'">'.$bundleidentifier.' Version '.$version.'</a> - <a href="crashes.php'.$pagelink.'">Crashes</a><br/><br/>';


echo '<table class="top" cellspacing="0" cellpadding="2"><colgroup><col width="80"/><col width="180"/><col width="150"/><col width="300"/><col width="500"/><col width="100"/></colgroup>';
echo "<tr><th>System</th><th>Timestamp</th><th>User</th><th>Description</th><th>Log</th><th>Action</th></tr>";
echo '</table>';

// get all groups
$query = "SELECT userid, contact, systemversion, description, log, timestamp, id FROM ".$dbcrashtable.$whereclause." ORDER BY systemversion desc, timestamp desc";
$result = mysql_query($query) or die(end_with_result('Error in SQL '.$query));

$numrows = mysql_num_rows($result);
if ($numrows > 0) {
	// get the status
	while ($row = mysql_fetch_row($result))
	{
		$userid = $row[0];
		$contact = $row[1];
		$systemversion = $row[2];
		$description = $row[3];
		$log = $row[4];
		$timestamp = $row[5];
		$crashid = $row[6];
		
		echo '<table class="bottom" cellspacing="0" cellpadding="2"><colgroup><col width="80"/><col width="180"/><col width="150"/><col width="300"/><col width="500"/><col width="100"/></colgroup>';
		echo "<tr valign='top' align='center'><td>".$systemversion."</td><td>".$timestamp."</td><td>".$userid."<br/>".$contact."</td><td><textarea rows='10' style='width:95%' readonly>".$description."</textarea></td><td><textarea rows='10' style='width:95%' wrap='off' readonly>".$log."</textarea></td><td><a href='download.php?crashid=".$crashid."'>Download</a></td></tr>";
		echo '</table>';
	}
	
	mysql_free_result($result);
}

mysql_close($link);

echo '</body></html>';

?>