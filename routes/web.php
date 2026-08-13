<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BrokerController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockAnalysisController;
use App\Http\Controllers\StockController;
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

// Stock features gated by permission (sidebar uses @can with the same keys).
Route::middleware(['auth', 'permission:view stock charts'])->group(function () {
    Route::get('/stock/chart/litechart', [StockController::class, 'showChart'])->name('stock.chart.litechart');
    Route::get('/stock/chart/tv', [StockController::class, 'showChartTv'])->name('stock.chart.tv');
    Route::get('/stock/drawings', [StockController::class, 'getDrawings'])->name('stock.drawings');
    Route::post('/stock/drawings', [StockController::class, 'saveDrawings'])->name('stock.drawings.save');
    Route::get('/stock/ohlc', [StockController::class, 'getOhlc'])->name('stock.ohlc');
});
Route::middleware(['auth', 'permission:view stock analysis'])->group(function () {
    Route::get('/stock/analyze', [StockAnalysisController::class, 'index'])->name('stock.analyze');
    Route::get('/stock/broker-summary', [BrokerSummaryController::class, 'index'])->name('stock.broker-summary');
    Route::get('/stock/broker-summary/{code}', [BrokerSummaryController::class, 'data'])->name('stock.broker-summary.data');
});
Route::middleware(['auth', 'permission:view brokers'])->group(function () {
    Route::get('/stock/brokers', [BrokerController::class, 'index'])->name('stock.brokers');
});
