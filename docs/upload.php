<?php
if($_GET['key'] != 'toTri7WnjrNoQeNi5kUssW')
  die("wrong transaction-key");

$postdata = file_get_contents("php://input");
//$postdata = file_get_contents("php://stdin");

include("database.php");
$db->addUploadToDatabase($_GET['id'], $postdata);

echo "done";