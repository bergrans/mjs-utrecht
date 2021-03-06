<?php
include 'GPSDistance.php';

/** Settings */

$center = [52.09691672548126, 5.0807821349173965]; // Utrecht - Douwe Egberts
$radius = 8.15; // km radius around 'center'
$lastHours = 1; // get data from last n hours
$deltaTempAllert = 5.0; // allert (red) range in °C
$url = 'https://meetjestad.net/data/?type=sensors&format=json';

/** end settings */

$start_date = new DateTime('NOW');
$start_date->modify("-$lastHours hour");
$end_date = new DateTime('NOW');
$end_date->modify('+1 minute'); // be sure to get the latest measurements

$url .= '&begin=' . $start_date->format('Y-m-d,H:i') . '&end=' . $end_date->format('Y-m-d,H:i');

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
$result = curl_exec($ch);
$measurements = json_decode($result);
curl_close($ch);

$nodes = array();
$temperatures = array();

foreach ($measurements as $measurement) {
  if (array_key_exists('latitude', $measurement) && array_key_exists('longitude', $measurement)) {
    $location = array($measurement->latitude, $measurement->longitude);
    $dist = distance($center[0], $center[1], $location[0], $location[1]);
    if ($dist < $radius) {
      $nodes[$measurement->id] = $measurement;
      $temperatures[$measurement->id] = $measurement->temperature;
    }
  }
}

$diff = $end_date->diff($start_date);
$medianTemp = median($temperatures);

echo ("\e[90m" . $url . "\e[0m\n");
echo ("Results: " .  sizeof($measurements) . " measurements in the last " . $diff->format("%h hour(s)") . "\n");
echo ("Utrecht: " .  sizeof($nodes) . " nodes\n");
echo ("Temperature median: " . number_format($medianTemp, 2, ',', '') . "°C\n");
echo ("\e[90mTemperature average: " . number_format(average($temperatures), 2, ',', '') . "°C\e[0m\n");

ksort($nodes);
foreach ($nodes as $nid => $node) {
  $delta_temp = $node->temperature - $medianTemp;
  $color = abs($delta_temp) > $deltaTempAllert ? "[31m" : "[32m";
  $firmware = array_key_exists('firmware_version', $node) ? $node->firmware_version : 0;
  $fwv = sprintf("%02X", $firmware);
  printf("\e\033[1m%4s\e\033[0m | ", $nid); // id in bold
  echo ( $fwv . ' | ' . number_format($node->temperature, 4, '.', '') . "°C\t\e" . $color . ' ∆ ' . sprintf("%+G", number_format($delta_temp, 2, '.', '')) . "°C \e[0m \n");
}

/*
 * Get the median of a given array of numbers
 * 
 */
function median($numbers)
{
  sort($numbers);
  $count = sizeof($numbers);
  $index = floor($count / 2);
  if (!$count) {
    return NULL;
  } elseif ($count & 1) {
    return $numbers[$index];
  } else {
    return ($numbers[$index - 1] + $numbers[$index]) / 2;
  }
}

/*
 * Get the average of a given array of numbers
 * 
 */
function average($numbers)
{
  $sum = 0;
  foreach ($numbers as $value) {
    $sum += $value;
  }
  return $sum / sizeof($numbers);
}
