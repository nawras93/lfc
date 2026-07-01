<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OfferController;
use App\Http\Controllers\Api\V1\PlayerController;
use App\Http\Controllers\Api\V1\PointTransactionController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RedemptionController;
use App\Http\Controllers\Api\V1\ScanController;
use App\Http\Controllers\Api\V1\StaffAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware('throttle:api')
    ->group(function (): void {
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/accept-invite', [AuthController::class, 'acceptInvite']);
        Route::post('/staff/login', [StaffAuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::post('/auth/logout', [AuthController::class, 'logout']);
            Route::get('/me', ProfileController::class);
            Route::get('/players', [PlayerController::class, 'index']);
            Route::get('/players/{player}', [PlayerController::class, 'show']);
            Route::get('/players/{player}/transactions', [PointTransactionController::class, 'playerHistory']);
            Route::get('/me/transactions', [PointTransactionController::class, 'accountHistory']);
            Route::get('/scan-token', [ScanController::class, 'token']);
            Route::post('/scan', [ScanController::class, 'scan']);
            Route::get('/staff/fixtures', [ScanController::class, 'fixtures']);

            Route::get('/redemption-items', [RedemptionController::class, 'items']);
            Route::post('/redemptions', [RedemptionController::class, 'redeem']);
            Route::get('/redemptions', [RedemptionController::class, 'history']);
            Route::get('/offers', [OfferController::class, 'index']);
        });
    });
