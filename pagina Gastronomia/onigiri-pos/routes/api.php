<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Onigiri POS
|--------------------------------------------------------------------------
|
| Prefijo: /api
| Formato: JSON
| Versión: v1 (sin versionado explícito en MVP)
|
*/

// ---------------------------------------------------------------------------
// CATÁLOGO — Público (sin autenticación)
// ---------------------------------------------------------------------------
Route::prefix('catalog')->name('api.catalog.')->group(function () {
    Route::get('/',           [CatalogController::class, 'index'])->name('index');
    Route::get('/featured',   [CatalogController::class, 'featured'])->name('featured');
    Route::get('/{id}/stock', [CatalogController::class, 'stock'])->name('stock');
});

// ---------------------------------------------------------------------------
// CARRITO — Público (validación de stock en tiempo real)
// ---------------------------------------------------------------------------
Route::prefix('cart')->name('api.cart.')->group(function () {
    Route::post('/validate',          [CartController::class, 'validate'])->name('validate');
    Route::get('/stock/{productId}',  [CartController::class, 'stockForProduct'])->name('stock');
});

// ---------------------------------------------------------------------------
// PEDIDOS
// ---------------------------------------------------------------------------
Route::prefix('orders')->name('api.orders.')->group(function () {
    Route::get('/',              [OrderController::class, 'index'])->name('index');
    Route::post('/',             [OrderController::class, 'store'])->name('store');
    Route::get('/{id}',          [OrderController::class, 'show'])->name('show');
    Route::post('/{id}/confirm', [OrderController::class, 'confirm'])->name('confirm');
    Route::patch('/{id}/status', [OrderController::class, 'updateStatus'])->name('status');
});

// ---------------------------------------------------------------------------
// FACTURAS
// ---------------------------------------------------------------------------
Route::prefix('invoices')->name('api.invoices.')->group(function () {
    Route::get('/{id}',          [InvoiceController::class, 'show'])->name('show');
    Route::get('/{id}/download', [InvoiceController::class, 'download'])->name('download');
    Route::post('/{id}/retry',   [InvoiceController::class, 'retry'])->name('retry');
});
