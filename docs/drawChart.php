<?php
date_default_timezone_set('UTC');
$windows = array(
  array('text' => '1 min sampling interval', 'size' => 0),
  array('text' => 'average +- 5 min', 'size' => 300),
  array('text' => 'average +- 60 min', 'size' => 3600)
);
$timeString = "d.m.Y H:i";
$timestampFormat = "hour";
$points = 1440;
require_once("drawChart-common.php");
