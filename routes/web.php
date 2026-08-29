<?php

use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

/*
|--------------------------------------------------------------------------
| Rutas Autenticadas (Requieren cuenta activa)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Cambio obligatorio/voluntario de contraseña cuando se asigna credencial temporal
    Route::get('/password/change', [ChangePasswordController::class, 'showForm'])->name('password.change');
    Route::post('/password/change', [ChangePasswordController::class, 'update'])->name('password.update');

    // Rutas protegidas de navegación completa (requieren haber cambiado la contraseña si era obligatorio)
    Route::middleware('must_change_password')->group(function () {
        // Perfil de usuario
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/logout-other-devices', [ProfileController::class, 'logoutOtherDevices'])->name('profile.logout-others');

        // Módulo de Administración de Usuarios (Solo Administradores)
        Route::middleware('can:users.manage')->prefix('users')->as('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::post('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        });

        // Catálogos Base (Categorías, Proveedores, Clientes)
        Route::post('categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
        Route::resource('categories', CategoryController::class)->except(['destroy']);

        Route::post('suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggle-status');
        Route::resource('suppliers', SupplierController::class)->except(['destroy']);

        Route::post('customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
        Route::resource('customers', CustomerController::class)->except(['destroy']);

        // Productos y Núcleo de Inventario (K-004)
        Route::post('products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::post('products/{product}/initial-stock', [ProductController::class, 'initialStock'])->name('products.initial-stock');
        Route::resource('products', ProductController::class)->except(['destroy']);

        Route::resource('stock-entries', \App\Http\Controllers\StockEntryController::class)->except(['show', 'destroy', 'edit', 'update']);
        Route::get('stock-entries/{stock_entry}', [\App\Http\Controllers\StockEntryController::class, 'show'])->name('stock-entries.show');
        Route::get('stock-entries/{stock_entry}/edit', [\App\Http\Controllers\StockEntryController::class, 'edit'])->name('stock-entries.edit');
        Route::put('stock-entries/{stock_entry}', [\App\Http\Controllers\StockEntryController::class, 'update'])->name('stock-entries.update');
        Route::delete('stock-entries/{stock_entry}', [\App\Http\Controllers\StockEntryController::class, 'destroy'])->name('stock-entries.destroy');
        Route::post('stock-entries/{stock_entry}/confirm', [\App\Http\Controllers\StockEntryController::class, 'confirm'])->name('stock-entries.confirm');

        Route::get('inventory/adjustments/create', [\App\Http\Controllers\InventoryAdjustmentController::class, 'create'])->name('inventory.adjustments.create');
        Route::post('inventory/adjustments', [\App\Http\Controllers\InventoryAdjustmentController::class, 'store'])->name('inventory.adjustments.store');

        Route::get('inventory/movements', [\App\Http\Controllers\InventoryMovementController::class, 'index'])->name('inventory.movements.index');
        Route::get('inventory/kardex', [\App\Http\Controllers\InventoryMovementController::class, 'kardexForm'])->name('inventory.kardex.form');
        Route::get('inventory/kardex/report', [\App\Http\Controllers\InventoryMovementController::class, 'kardex'])->name('inventory.kardex.report');
    });
});
