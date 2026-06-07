<?php
$lat = -6.9147;
$lng = 107.6098;
$dLat = deg2rad(-6.9170 - $lat);
$dLng = deg2rad(107.6090 - $lng);
$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat)) * cos(deg2rad(-6.9170)) * sin($dLng/2) * sin($dLng/2);
$c = 2 * atan2(sqrt($a), sqrt(1-$a));
$distance = 6371 * $c;
echo number_format($distance, 1, ',', '.');
