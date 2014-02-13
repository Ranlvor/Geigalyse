<?php
if($_GET['key'] != 'toTri7WnjrNoQeNi5kUssW')
  die("wrong transaction-key");

require_once("database.php");
$db->addGetUploadToDatabase($_GET['id'], $_GET['time'], $_GET['count']);

print_r($_GET);
echo "done";
require_once("../analyse/populateMesurements.php");
