<?php
$postdata = file_get_contents("php://input");
//$postdata = file_get_contents("php://stdin");

include("database.php");
$db->addUploadToDatabase(0, $postdata);

echo "done";