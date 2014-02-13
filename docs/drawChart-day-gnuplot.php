<?php
header("Cache-Control: no-cache, must-revalidate"); 
header("Content-Type: image/png");
require_once("database.php");
$handle = popen ("../analyse/plot-dayly.sh", "r");
$img = new Imagick();
$img->readImageFile($handle);
pclose($handle);

require_once("generate-latest-mesurement-array.php");

$comment = 'Latest Data available at http://geigalyse.starletp9.de/'."\n";
$comment .= "\n";
$comment .= "Information about latest measurement:\n";

foreach($table as $key => $value) {
  $comment .= "$key: $value\n";
}


$img->commentImage($comment);
echo $img;
