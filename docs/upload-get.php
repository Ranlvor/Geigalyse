<?php
if($_GET['key'] != 'toTri7WnjrNoQeNi5kUssW')
  die("wrong transaction-key");

include("database.php");
$db->addGetUploadToDatabase($_GET['id'], $_GET['time'], $_GET['count']);

print_r($_GET);
echo "done";
include("../analyse/populateMesurements.php");
