<?php
if($_GET['key'] != 'toTri7WnjrNoQeNi5kUssW')
  die("wrong transaction-key");

$postdata = file_get_contents("php://input");
//$postdata = file_get_contents("php://stdin");

require_once("database.php");
$db->addUploadToDatabase($_GET['id'], $postdata);

if(strlen($postdata) == 0) {
	echo "WARNING: empty upload";
} else if (strlen($postdata) %6 != 0) {
	echo "WARNING: uploadlength %6 != 0";
} else {
	echo "Upload seems legit";
}
require_once("../analyse/populateMesurements.php");
