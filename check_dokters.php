<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/pasien/daftar', 'GET');
$kernel->handle($request);

$dokters = \App\Models\User::query()->where('role', 'dokter')
    ->where('status', 'aktif')
    ->with('profilDokter')
    ->get();

echo "Total Dokters: " . $dokters->count() . "\n";
if ($dokters->count() > 0) {
    $doc = $dokters->first();
    echo "First Dokter Poli: " . ($doc->profilDokter ? $doc->profilDokter->poli : 'NULL') . "\n";
    echo "JSON: " . json_encode($doc) . "\n";
}
