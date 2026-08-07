<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'permission:view roles'])->group(function () {
    Route::resource('roles', RoleController::class);
    Route::resource('permissions', PermissionController::class);
});

Route::middleware(['auth', 'permission:view audit logs'])->group(function () {
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{activityLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::middleware('auth')->group(function () {
    Route::get('/stock/chart/litechart', [App\Http\Controllers\StockController::class, 'showChart'])->name('stock.chart.litechart');
    Route::get('/stock/chart/tv', [App\Http\Controllers\StockController::class, 'showChartTv'])->name('stock.chart.tv');
    Route::get('/stock/drawings', [App\Http\Controllers\StockController::class, 'getDrawings'])->name('stock.drawings');
    Route::post('/stock/drawings', [App\Http\Controllers\StockController::class, 'saveDrawings'])->name('stock.drawings.save');
    Route::get('/stock/ohlc', [App\Http\Controllers\StockController::class, 'getOhlc'])->name('stock.ohlc');
    Route::get('/stock/analyze', [App\Http\Controllers\StockAnalysisController::class, 'index'])->name('stock.analyze');
    Route::get('/stock/brokers', [App\Http\Controllers\BrokerController::class, 'index'])->name('stock.brokers');
});
