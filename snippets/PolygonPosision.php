<?php
/*
Description: The point-in-polygon algorithm allows you to check if a point is
inside a polygon or outside of it.
Author: Michaël Niessen (2009)
Website: http://AssemblySys.com

If you find this script useful, you can show your
appreciation by getting Michaël a cup of coffee ;)
donation to Michaël

As long as this notice (including author name and details) is included and
UNALTERED, this code is licensed under the GNU General Public License version 3:
http://www.gnu.org/licenses/gpl.html
*/

class pointLocation
{
    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices?

    function pointInPolygon($point, $polygon, $pointOnVertex = true)
    {
        $this->pointOnVertex = $pointOnVertex;

        // Transform string coordinates into arrays with x and y values
        $point = $this->pointStringToCoordinates($point);
        $vertices = array();
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex);
        }

        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }

        // Check if the point is inside the polygon or on the boundary
        $intersections = 0;
        $vertices_count = count($vertices);

        for ($i = 1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i - 1];
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) {
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }
        // If the number of edges we passed through is odd, then it's in the polygon.
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }

    function pointOnVertex($point, $vertices)
    {
        foreach ($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
    }

    function pointStringToCoordinates($pointString)
    {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }
}

function test()
{
    $pointLocation = new pointLocation();
    // $points = array("50.2 70.8","70 40","-20 30","100 10","-10 -10","40 -20","110 -20");
    $points = array(
        "52.1012 5.00412", // node 779 inside
        "52.1018 5.1796", // node 28 outside
        "52.0958 5.1377", // node 778 inside
        "52.0550 5.1384", // node 742 inside
    );

    $polygonUtrecht = [
        "52.05006083597394 5.1430564712290145",
        "52.07717089841263 5.196799369446457",
        "52.098339185800356 5.170157590842938",
        "52.11921538843297 5.152702632447529",
        "52.14064563993643 5.089772914021977",
        "52.12485592765918 5.061293771376836",
        "52.13246952328015 5.046135518033455",
        "52.11949743234871 5.033273969742101",
        "52.1440284216607 5.004335486086555",
        "52.13134166527723 4.971722274347765",
        "52.12288182013201 4.96804754626452",
        "52.11159952807335 4.977234366472631",
        "52.10370022449206 4.97218161535817",
        "52.09862136179658 4.974478320410197",
        "52.09862136179658 4.988258550722362",
        "52.08987307585104 4.9928519608264175",
        "52.088179661066505 5.002957463055338",
        "52.06531227093807 4.996526688909661",
        "52.03480419362686 5.042001448939805",
        "52.024912847436305 5.063131135418458",
        "52.05316803208116 5.065427840470486",
        "52.05966419844845 5.077830047751434"
    ];

    // The last point's coordinates must be the same as the first one's, to "close the loop"
    foreach ($points as $key => $point) {
        echo "point " . ($key + 1) . " ($point): " . $pointLocation->pointInPolygon($point, $polygonUtrecht) . "\n";
    }
}

/** Only run test when this file is run directly from commandline */
if (!count(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS))) {
    test();
}
