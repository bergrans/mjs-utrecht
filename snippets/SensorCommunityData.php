<?php

$radius = 8.15; // km radius around ...
$center = [52.09691672548126, 5.0807821349173965]; // Utrecht - Douwe Egberts

$url = "https://data.sensor.community/airrohr/v1/filter/area=$center[0],$center[1],8";

$json = file_get_contents($url);
$measurements = json_decode($json, true);

$data = array();
$count = array();

foreach ($measurements as $measurement) {
  if ($measurement['location']['indoor'] == 0) {
    $id = $measurement['sensor']['id'];



    foreach ($measurement['sensordatavalues'] as $value) {
      if ($value['value_type'] == 'temperature' ) {
        $data[] = [
          'id' => $id,
          'values' => $value['value'],
          'location' => [
            'latitude' => $measurement['location']['latitude'],
            'longitude' => $measurement['location']['longitude']
          ],
        ];
        
        $count[$id] = array_key_exists($id, $count) ? ++$count[$id] : 1;
      }
    }
  }
}

var_dump($data);
var_dump($count);
