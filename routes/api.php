<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MatchController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\OfferController;
use App\Http\Controllers\Api\V1\PlayerController;
use App\Http\Controllers\Api\V1\PointTransactionController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RedemptionController;
use App\Http\Controllers\Api\V1\ScanController;
use App\Http\Controllers\Api\V1\StaffAuthController;
use App\Http\Controllers\Api\V1\StandingController;
use App\Http\Middleware\SetAppContextFromHeader;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['throttle:api', 'api.locale'])
    ->group(function (): void {
        Route::post('/auth/register', [AuthController::class, 'register']);
        Route::post('/auth/login', [AuthController::class, 'login']);
        Route::post('/auth/accept-invite', [AuthController::class, 'acceptInvite']);
        Route::post('/staff/login', [StaffAuthController::class, 'login']);

        Route::middleware(SetAppContextFromHeader::class)->group(function (): void {
            Route::get('/content/news', [NewsController::class, 'index']);
            Route::get('/content/news/{news}', [NewsController::class, 'show']);
            Route::get('/content/fixtures', [MatchController::class, 'fixtures']);
            Route::get('/content/results', [MatchController::class, 'results']);
            Route::get('/content/standings', [StandingController::class, 'index']);
        });

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
