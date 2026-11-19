<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function (): void {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('profile/password', [PasswordController::class, 'edit'])->name('profile.password.edit');
    Route::put('profile/password', [PasswordController::class, 'update'])->name('profile.password.update');

    Route::resource('purchase-requests', PurchaseRequestController::class);
    Route::post('purchase-requests/{purchase_request}/submit', [PurchaseRequestController::class, 'submit'])->name('purchase-requests.submit');
    Route::post('purchase-requests/{purchase_request}/approve', [PurchaseRequestController::class, 'approve'])->name('purchase-requests.approve');
    Route::post('purchase-requests/{purchase_request}/reject', [PurchaseRequestController::class, 'reject'])->name('purchase-requests.reject');

    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::post('purchase-orders/{purchase_order}/issue', [PurchaseOrderController::class, 'issue'])->name('purchase-orders.issue');
    Route::post('purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel');

    Route::resource('goods-receipts', GoodsReceiptController::class)->except(['edit', 'update', 'destroy']);
    Route::get('stock', [StockController::class, 'index'])->name('stock.index');
    Route::get('stock/adjust', [StockController::class, 'create'])->name('stock.adjust.create');
    Route::post('stock/adjust', [StockController::class, 'store'])->name('stock.adjust.store');

    Route::resource('suppliers', SupplierController::class)->except(['show']);
    Route::resource('items', ItemController::class)->except(['show']);
    Route::resource('warehouses', WarehouseController::class)->except(['show', 'destroy']);
    Route::resource('users', UserController::class)->except(['show']);

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/outstanding', [ReportController::class, 'outstanding'])->name('reports.outstanding');
    Route::get('reports/suppliers', [ReportController::class, 'suppliers'])->name('reports.suppliers');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/reorder', [ReportController::class, 'reorder'])->name('reports.reorder');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

    Route::get('activities', [ActivityLogController::class, 'index'])->name('activities.index');
});
