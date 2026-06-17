<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$user = App\Models\User::where('email', 'pasien.bpjs@gmail.com')->first();
Auth::login($user);

$request = Illuminate\Http\Request::create('/pasien/daftar', 'GET');
$response = $kernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() === 403) {
    echo "Content: " . strip_tags($response->getContent());
} else if ($response->isRedirection()) {
    echo "Redirect: " . $response->getTargetUrl();
} else {
    echo "Success!";
}
