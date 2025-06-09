<?php

use App\Http\Controllers\API\Admin\CustomerController;
use App\Http\Controllers\API\Admin\DashboardController;
use App\Http\Controllers\API\Admin\LaporanController;
use App\Http\Controllers\API\Admin\OutletController;
use App\Http\Controllers\API\Admin\ProductController;
use App\Http\Controllers\API\Admin\ReportController as AdminReportController;
use App\Http\Controllers\API\Admin\StockProductController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Cashier\ReportController;
use App\Http\Controllers\API\Cashier\TransactionController;
use App\Http\Controllers\API\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    /**
     * Cashier Routes
     */
    Route::apiResource('transactions', TransactionController::class)->only('index', 'store', 'show');
    Route::get('dashboard', [DashboardController::class, 'laporan']);

    Route::prefix('reports')->group(function () {
        Route::get('/daily', [ReportController::class, 'dailyReport']);
        Route::get('/monthly', [ReportController::class, 'monthlyReport']);
        Route::get('/yearly', [ReportController::class, 'yearlyReport']);
    });

    /**
     * Admin Routes API
     */
    Route::prefix('admin')->group(function () {
        Route::apiResource('customer', CustomerController::class)->only('index', 'store');
        Route::apiResource('outlet', OutletController::class)->only('index', 'store', 'show', 'update', 'destroy');
        Route::apiResource('product', ProductController::class)->only('index', 'store', 'show', 'update', 'destroy');
        Route::apiResource('stock-product', StockProductController::class)->only('store');

        Route::prefix('report')->group(function () {
            Route::get('/daily-export', [AdminReportController::class, 'exportReportByDate']);
            Route::get('/yearly-export', [AdminReportController::class, 'exportReportByMonth']);
            Route::get('/sort-by-date', [AdminReportController::class, 'sortByDate']);
            Route::get('/sort-by-month', [AdminReportController::class, 'sortByMonth']);
        });
    });

    /**
     * Route 2 role
     */
    Route::prefix('update')->group(function(){
        Route::put('profile', [ProfileController::class, 'updateProfile']);
        Route::put('password', [ProfileController::class, 'updatePassword']);
    });
});
