<?php

/**
 *  This routine calculates the distance between two points (given the
 *  latitude/longitude of those points). It is being used to calculate
 *  the distance between two locations using GeoDataSource(TM) Products
 *
 *  Definitions:
 *    South latitudes are negative, east longitudes are positive
 *
 *  Passed to function:
 *    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)
 *    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)
 */

function distance($lat1, $lon1, $lat2, $lon2)
{
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    } else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist) * 60 * 1.853159616;
        return $dist;
    }
}

function test()
{
    $radius = 8.15; // km radius around ...
    $center = [52.09691672548126, 5.0807821349173965]; // Utrecht - Douwe Egberts

    $locations = [
        [52.1018, 5.1796], // #28 de Bilt
        [52.09577706165364, 5.137976028906382], // nmode #788
        [52.1011, 5.00412], // #779 Vleuten
        [52.02627094638443, 5.060890913760147], // Nedereindseplas
        [52.14745253688355, 5.084019113042767],  // Molenpolder
        [52.0567, 5.13727], // #742 Lunetten
        [52.14217013053726, 5.004003993400116], // Gieltjesdorp
        [52.07732166900299, 5.1951884375709865], // Bunnik
        [52.12259301413465, 4.969534629608233], // de Haar
    ];

    foreach ($locations as $location) {
        $dist = distance($center[0], $center[1], $location[0], $location[1]);
        echo $dist . " km";
        echo ($dist <= $radius) ? ' inside' : ' outside';
        echo "\n";
    }
}

/** Only run test when this file is run directly from commandline */
if (!count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))) {
    test();
}
