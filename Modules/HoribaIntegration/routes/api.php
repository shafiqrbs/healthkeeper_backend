<?php

use App\Http\Middleware\HeaderAuthenticationMiddleware;
use Illuminate\Support\Facades\Route;
use Modules\HoribaIntegration\App\Http\Controllers\HoribaResultController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public — Bridge Agent health check
Route::get('/horiba-integration/horiba-results/ping', [HoribaResultController::class, 'ping']);

// Bridge Agent — token auth + IP whitelist + rate limited
Route::post('/horiba-integration/horiba-results/import-cbc', [HoribaResultController::class, 'importCbc'])
    ->middleware(['horiba.bridge', 'throttle:100,1']);

// Frontend — user authenticated routes
Route::prefix('/horiba-integration')->middleware([HeaderAuthenticationMiddleware::class, 'auth:api'])->group(function () {
    Route::get('horiba-results/stats', [HoribaResultController::class, 'stats']);
    Route::get('horiba-results/search-invoices', [HoribaResultController::class, 'searchInvoices']);
    Route::get('horiba-results/invoice-particulars/{invoiceId}', [HoribaResultController::class, 'invoiceParticulars']);
    Route::get('horiba-results', [HoribaResultController::class, 'index']);
    Route::get('horiba-results/{id}', [HoribaResultController::class, 'show']);
    Route::patch('horiba-results/{id}', [HoribaResultController::class, 'update']);
    Route::post('horiba-results/{id}/map', [HoribaResultController::class, 'mapResult']);
    Route::post('horiba-results/{id}/approve', [HoribaResultController::class, 'approveResult']);
});
