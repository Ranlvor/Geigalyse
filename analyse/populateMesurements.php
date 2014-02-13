<?php
require_once("../docs/database.php");
$db->processUnprocessedGetUploads();
$db->processUnprocessedMesurements();