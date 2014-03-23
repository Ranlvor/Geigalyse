<?php
echo "lorem ipsum dolor sit amet"."\n";
$pid = pcntl_fork();
echo "\$pid = $pid\n";
if ($pid == -1) {
     die('Konnte nicht verzweigen');
} else if ($pid) {
     echo "parent: calling pcntl_waitpid to cleanup zombies"."\n";
     $status = 1;
     while($status > 0) {
         $status = pcntl_wait($tmp, WNOHANG);
	 echo $status;
     }
     echo "in parent process, imidiatly exit"."\n";
} else {
     echo "in child process, waiting for 10 seconts"."\n";
     sleep(10);
     echo "waiting done, exit"."\n";
     posix_setsid (); //change our parent to avoid becoming a zombie
     posix_kill(getmypid(),SIGTERM); //we want to destroy this process to avid spawning more and more fcgi-processes
}
