<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ClassType;
$types = ClassType::all();
foreach ($types as $type) {
    echo "ID: {$type->id}, Name: {$type->name}\n";
}
