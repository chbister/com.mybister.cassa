<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/hi', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('hi');

Route::get('/', [PosController::class, 'index'])->name('home');

/*
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::post('products/import', [ProductController::class, 'import'])->name('products.import');
    Route::get('products/export', [ProductController::class, 'export'])->name('products.export');
});
*/
Route::get('/qz/certificate', function () {
    $path = storage_path('app/private/qz/digital-certificate.txt');

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

    $privateKeyPath = storage_path('app/qz/private-key.pem');
    abort_unless(file_exists($privateKeyPath), 500, 'Private key not found');

    $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
    if ($privateKey === false) {
        abort(500, 'Unable to read private key');
    }

    $payload = $request->string('data')->toString();
    $signature = '';

    $ok = openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA512);
    openssl_free_key($privateKey);

    if (!$ok) {
        abort(500, 'Unable to sign payload');
    }

    return Response::make(base64_encode($signature), 200, [
        'Content-Type' => 'text/plain',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
