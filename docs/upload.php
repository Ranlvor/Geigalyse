<?php
print_r($_POST);
$postdata = file_get_contents("php://input");
echo "got „".$postdata."“\n";
file_put_contents ("tmpfile", $postdata);

$string = $postdata;
while (strlen($string) > 2) {
  $current = substr($string, 0, 6);
  $string = substr($string, 6);
  print_r(unpack('Vtime/vcount', $current));
}

