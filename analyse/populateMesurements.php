<?php
include("../docs/database.php");
$db->processUnprocessedGetUploads();
$db->processUnprocessedMesurements();