<?php
header("Cache-Control: no-cache, must-revalidate"); 
header("Content-Type: image/png");
include("../analyse/populateMesurements.php");
$db->populateSlidingAverageCache(1440*7, 3600);
$db->populateSlidingAverageCache(1440*7, 300);
$handle = popen ("../analyse/plot-weekly.sh", "r");
$img = new Imagick();
$img->readImageFile($handle);
pclose($handle);

$array = $db->getLatestMesurementExtendet();
$table['Timestamp'] = date("d.m.y H:i:s", $array['timestamp'] - 2208988800);
$table['Counts in the previous 60 seconds'] = $array['count'];
$table['Calculated radiation'] = $array['mysvph']." µSv/h";
$table['Used Geiger tube'] = $array['name'];
$table['Used counts/minute to µSv/h-divisor'] = $array['cpm-per-mysvph']. " (counts/minute)/(µSv/h)";
$table['Used deadtime'] = $array['deadtimeS'].' s';

$average = $db->getLatestProcessedMesurementsSlidingAverage(1,5*60);
$table['5 minute average'] = $average->fetchArray()['slidingAVG']." µSv/h";
$average->finalize();

$average = $db->getLatestProcessedMesurementsSlidingAverage(1,60*60);
$table['60 minute average'] = $average->fetchArray()['slidingAVG']." µSv/h";
$average->finalize();

$comment = 'Latest Data available at http://geigalyse.starletp9.de/'."\n";
$comment .= "\n";
$comment .= "Information about latest measurement:\n";

foreach($table as $key => $value) {
  $comment .= "$key: $value\n";
}


$img->commentImage($comment);
echo $img;
