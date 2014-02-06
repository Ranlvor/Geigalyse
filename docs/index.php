
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Geigercounter-Status</title>
</head>
<body>
<h1>Geigercounter-Status</h1>
<h2>Latest Measurement</h2>
<table>
<?php
include("../analyse/populateMesurements.php");
$array = $db->getLatestMesurementExtendet();
$table['Timestamp'] = date("d.m.y H:i:s", $array['timestamp'] - 2208988800);
$table['Counts in the previous 60 seconds'] = $array['count'];
$table['Calculated radiation'] = $array['mysvph']." µSv/h";
$table['Used Geiger tube'] = $array['name'];
$table['Used counts/minute to µSv/h-divisor'] = $array['cpm-per-mysvph']. " (counts/minute)/(µSv/h)";
$table['Used deadtime'] = $array['deadtimeS'].' s';

$average = $db->getLatestProcessedMesurementsSlidingAverage(1,5*60);
$table['5 minute average'] = $average->fetchArray()['slidingAVG']." µSv/h";
$average->finalize();

$average = $db->getLatestProcessedMesurementsSlidingAverage(1,60*60);
$table['60 minute average'] = $average->fetchArray()['slidingAVG']." µSv/h";
$average->finalize();

foreach($table as $key => $value) {
  echo "<tr><td>$key</td><td>$value</td></tr>\n";
}
?>
</table>
<h2>Older Data</h2>
<img src="drawChart.php" /><br>
<img src="drawChart-week.php" /><br>
<img src="drawChart-week-gnuplot.php" />
<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(["trackPageView"]);
  _paq.push(["enableLinkTracking"]);

  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://piwik.starletp9.de/";
    _paq.push(["setTrackerUrl", u+"piwik.php"]);
    _paq.push(["setSiteId", "5"]);
    var d=document, g=d.createElement("script"), s=d.getElementsByTagName("script")[0]; g.type="text/javascript";
    g.defer=true; g.async=true; g.src=u+"piwik.js"; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Piwik Code -->
</body>
