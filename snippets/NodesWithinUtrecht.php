<?php
include 'GPSDistance.php';

/** Settings */

$center = [52.09691672548126, 5.0807821349173965]; // Utrecht - Douwe Egberts
$radius = 8.15; // km radius around 'center'
$lastHours = 4; // get data from last n hours
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
      $temperatures[] = $measurement->temperature;
    }
  }
}

$diff = $end_date->diff($start_date);

echo ("\e[90m" . $url . "\e[0m\n");
echo ("Results: " .  sizeof($measurements) . " measurements in the last " . $diff->format("%h hour(s)") . "\n");
echo ("Utrecht: " .  sizeof($nodes) . " nodes\n");
echo ("Temperature median: " . number_format(median($temperatures), 2, ',', '') . "°C\n");
echo ("\e[90mTemperature average: " . number_format(average($temperatures), 2, ',', '') . "°C\e[0m\n");

ksort($nodes);
foreach ($nodes as $key => $node) {
  $delta_temp = $node->temperature - median($temperatures);
  $color = abs($delta_temp) > $deltaTempAllert ? "[31m" : "[32m";
  $firmware = array_key_exists('firmware_version', $node) ? $node->firmware_version : '?';
  printf("%4s - ", $key);
  echo ($firmware . ' - ' . number_format($node->temperature, 4, ',', '') . "°C \e" . $color . ' ∆ ' . number_format($delta_temp, 2, ',', '') . "°C \e[0m \n");
}

/* Get the median of a given array of numbers */
function median($numbers)
{
  sort($numbers);
  $count = sizeof($numbers);   // cache the count
  $index = floor($count / 2);  // cache the index
  if (!$count) {
    return NULL;
  } elseif ($count & 1) {    // count is odd
    return $numbers[$index];
  } else {                   // count is even
    return ($numbers[$index - 1] + $numbers[$index]) / 2;
  }
}

/* Get the average of a given array of numbers */
function average($numbers)
{
  $sum = 0;
  foreach ($numbers as $value) {
    $sum += $value;
  }
  return $sum / sizeof($numbers);
}
