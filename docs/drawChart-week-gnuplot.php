<?php
header("Cache-Control: no-cache, must-revalidate"); 
header("Content-Type: image/png");
include("../analyse/populateMesurements.php");
$db->populateSlidingAverageCache(1440*7, 3600);
$db->populateSlidingAverageCache(1440*7, 300);
passthru ("../analyse/plot-weekly.sh");