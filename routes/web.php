<?php

use App\Http\Controllers\PosController;
use App\Http\Controllers\SetupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/hi', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('hi');

Route::get('/init', [SetupController::class, 'index'])->name('setup.index');
Route::post('/init', [SetupController::class, 'store'])->name('setup.store');

Route::get('/', [PosController::class, 'index'])->name('home');

Route::get('/qz/certificate', function () {
    $path = storage_path('app/private/qz/public-cert.pem');
    // Log::info('/qz/certificate', [$path]);
    abort_unless(file_exists($path), 404, 'Certificate not found');

    return Response::make(file_get_contents($path), 200, [
        'Content-Type' => 'text/plain',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
});

Route::post('/qz/sign', function (Request $request) {
    $request->validate([
        'data' => ['required', 'string'],
    ]);

    $privateKeyPath = storage_path('app/private/qz/private-key.pem');
    // Log::info('/qz/sign', [$privateKeyPath]);
    abort_unless(file_exists($privateKeyPath), 500, 'Private key not found');

    $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
    if ($privateKey === false) {
        abort(500, 'Unable to read private key');
    }

    $payload = $request->string('data')->toString();
    $signature = '';

    $ok = openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA512);
    openssl_free_key($privateKey);

    if (! $ok) {
        abort(500, 'Unable to sign payload');
    }

    return Response::make(base64_encode($signature), 200, [
        'Content-Type' => 'text/plain',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
});

Route::get('/qz/certificate-download', function () {
    $path = storage_path('app/private/qz/public-cert.pem');

    if (! file_exists($path)) {
        abort(404, 'Public certificate not found');
    }

    return response()->download($path, 'public-cert.pem', [
        'Content-Type' => 'application/x-pem-file',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
})->name('qz.certificate-download');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
