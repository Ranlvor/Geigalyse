<?php
require_once("../docs/database.php");
$db = null; //causes the Database to be destructed causing the Database to be closed

$pid = pcntl_fork();
if ($pid == -1) {
     //this shouldn't happen
} else if ($pid) {

     //destroy all child zombie processes
     $status = 1;
     while($status > 0) {
         $status = pcntl_wait($tmp, WNOHANG);
         echo $status;
     }
} else {
    $db = new GeigalyseDatabse();

    $db->processUnprocessedGetUploads();
    $db->processUnprocessedMesurements();
    $db->populateSlidingAverageCache(0);
    $db->populateSlidingAverageCache(300);
    $db->populateSlidingAverageCache(3600);

    posix_kill(getmypid(),SIGTERM); //we want to destroy this process to avid spawning more and more fcgi-processes. While the parent ignores us we will be a zombie
}
