<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$count = \App\Models\Seller::where('verification_status', 'approved')
    ->whereHas('user', function ($q) {
        $q->where('is_banned', false);
    })->count();
echo "Approved & Not Banned: " . $count . "\n";

$sellers = \App\Models\Seller::where('verification_status', 'approved')
    ->whereHas('user', function ($q) {
        $q->where('is_banned', false);
    })->get();

foreach ($sellers as $seller) {
    echo $seller->store_name . " at " . $seller->latitude . "," . $seller->longitude . "\n";
}
