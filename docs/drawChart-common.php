<?php
$pChartPath = "../pChart2.1.4";

 /* pChart library inclusions */
 include("$pChartPath/class/pData.class.php");
 include("$pChartPath/class/pDraw.class.php");
 include("$pChartPath/class/pImage.class.php");
 include("../analyse/populateMesurements.php");

 /* Create and populate the pData object */
 $MyData = new pData();

 $data = array();
 for($i = 0; $i < count($windows); $i++) {
  $result = $db->getLatestProcessedMesurementsSlidingAverage($points, $windows[$i]['size']);
  while ($line = $result->fetchArray())
    $data[$line['timestamp']][$i] = $line['slidingAVG'];
  $result->finalize();
 }
 $firstTimestamp = array_keys($data)[0] - 2208988800;

 $data = array_reverse($data, true);
 //print_r($data);
 foreach($data AS $timestamp => $row)
  {
   $timestamp = $timestamp-2208988800;

   for($i = 0; $i < count($windows); $i++) {
     $MyData->addPoints($row[$i],$windows[$i]['text']);
     //echo $windows[$i]['text']."\n";
     //echo $line[$i]['slidingAVG']."\n";
   }
   //print_r($line);

   $MyData->addPoints($timestamp,"TimeStamp");
  }
 $MyData->setAxisName(0,"Radiation");
 $MyData->setAxisName(1,"TimeStamp");

 function axisFormator($value) {
   return "$value µS/h";
 }
 $MyData->setAxisDisplay(0,AXIS_FORMAT_CUSTOM, "axisFormator");

 $MyData->setSerieDescription("TimeStamp","time");
 $MyData->setAbscissa("TimeStamp");
 if($timestampFormat == "hour")
  $MyData->setXAxisDisplay(AXIS_FORMAT_TIME,"H:00"); 
 else
  $MyData->setXAxisDisplay(AXIS_FORMAT_DATE); 

 $MyData->loadPalette("$pChartPath/palettes/light.color", TRUE); //different colors

 /* Create the pChart object */
 $myPicture = new pImage(700,230,$MyData);

 /* Turn of Antialiasing */
 $myPicture->Antialias = TRUE;

 /* Add a border to the picture */
 $myPicture->drawRectangle(0,0,699,229,array("R"=>0,"G"=>0,"B"=>0));
 
 /* Write the chart title */ 
// $myPicture->setFontProperties(array("FontName"=>"$pChartPath/fonts/pf_arma_five.ttf","FontSize"=>11));
 $myPicture->setFontProperties(array("FontName"=>"$pChartPath/fonts/calibri.ttf","FontSize"=>11));

 $timeString = date($timeString,$firstTimestamp);
 $myPicture->drawText(150,25,"Radiation $timeString",array("FontSize"=>20,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE));

 /* Set the default font */
 $myPicture->setFontProperties(array("FontName"=>"$pChartPath/fonts/pf_arma_five.ttf","FontSize"=>6));
 
 /* Define the chart area */
 $myPicture->setGraphArea(60,20,680,200);

 /* Draw the scale */
 $scaleSettings = array("XMargin"=>10,"YMargin"=>10,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"RemoveSkippedAxis"=>TRUE,"DrawSubTicks"=>FALSE,"Mode"=>SCALE_MODE_START0,"LabelingMethod"=>LABELING_DIFFERENT);
 $myPicture->drawScale($scaleSettings);

 /* Turn on Antialiasing */
 $myPicture->Antialias = TRUE;

 /* Draw the line chart */
// $myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10));
 $myPicture->drawLineChart();

 /* Write the chart legend */
 $myPicture->drawLegend(300,10,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_HORIZONTAL));

 /* Render the picture (choose the best way) */
 $myPicture->autoOutput();
?>
