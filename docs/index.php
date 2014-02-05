
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Geigercounter-Status</title>
</head>
<body>
<h1>Geigercounter-Status</h1>
<h2>Latest Mesurement</h2>
<table>
<?php
include("../analyse/populateMesurements.php");
$array = $db->getLatestMesurementExtendet();
$table['Timestamp'] = date("d.m.y H:i:s", $array['timestamp'] - 2208988800);
$table['Counts in the previous 60 seconds'] = $array['count'];
$table['Calculated radiation'] = $array['mysvph']." µSv/h";
$table['Used Geiger tube'] = $array['name'];
$table['Used counts/minute to µSv/h-divisor'] = $array['cpm-per-mysvph']. " (counts/minute)/(µSv/h)";
$table['Used deadtime'] = $array['deadtimeS'].' s';

$average = $db->getLatestProcessedMesurementsSlidingAverage(1,5*60);
$table['5 minute average'] = $average->fetchArray()['slidingAVG'];
$average->finalize();

$average = $db->getLatestProcessedMesurementsSlidingAverage(1,60*60);
$table['60 minute average'] = $average->fetchArray()['slidingAVG'];
$average->finalize();

foreach($table as $key => $value) {
  echo "<tr><td>$key</td><td>$value</td></tr>\n";
}
?>
</table>
<h2>Older Data</h2>
<img src="drawChart.php" />
</body>
