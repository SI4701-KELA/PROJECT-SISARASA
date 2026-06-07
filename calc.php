<?php
$lat = -6.974000;
$lng = 107.630000;

$sellers = [
    ['latitude' => -6.17976000, 'longitude' => 106.98836000],
    ['latitude' => -6.97769000, 'longitude' => 107.63535000],
    ['latitude' => -6.96995000, 'longitude' => 107.63383000],
];

$earthRadius = 6371;
foreach ($sellers as $seller) {
    $dLat = deg2rad($seller['latitude'] - $lat);
    $dLng = deg2rad($seller['longitude'] - $lng);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat)) * cos(deg2rad($seller['latitude'])) *
         sin($dLng/2) * sin($dLng/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthRadius * $c;

    echo number_format($distance, 1, ',', '.') . " KM\n";
}
