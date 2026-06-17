<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test normal
$request1 = Illuminate\Http\Request::create('/api/kbot/analyze', 'POST', ['message' => 'saya pusing dan demam sejak kemarin']);
$controller = app()->make(App\Http\Controllers\KbotController::class);
echo "=== Test Normal ===\n";
echo $controller->analyze($request1)->getContent() . "\n\n";

// Test Emergency
$request2 = Illuminate\Http\Request::create('/api/kbot/analyze', 'POST', ['message' => 'saya pingsan dan sesak napas berat nyeri dada hebat']);
echo "=== Test Emergency ===\n";
echo $controller->analyze($request2)->getContent() . "\n\n";

// Test Out of Domain
$request3 = Illuminate\Http\Request::create('/api/kbot/analyze', 'POST', ['message' => 'halo puskesmas, bagaimana cara mengurus ktp yang hilang?']);
echo "=== Test OOD ===\n";
echo $controller->analyze($request3)->getContent() . "\n\n";
