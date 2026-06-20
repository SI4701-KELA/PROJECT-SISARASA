<?php
$lat = -6.9147;
$lng = 107.6098;

$sellers = [
    ['lat' => -6.9147, 'lng' => 107.6098], // 0.0 KM
    ['lat' => -6.9170, 'lng' => 107.6090], // ~0.3 KM
    ['lat' => -6.9000, 'lng' => 107.6000], // ~1.9 KM
    ['lat' => -6.8500, 'lng' => 107.5000], // ~14.1 KM
];

$earthRadius = 6371;
foreach ($sellers as $seller) {
    $dLat = deg2rad($seller['lat'] - $lat);
    $dLng = deg2rad($seller['lng'] - $lng);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat)) * cos(deg2rad($seller['lat'])) *
         sin($dLng/2) * sin($dLng/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthRadius * $c;

    echo number_format($distance, 1, ',', '.') . " KM\n";
}
