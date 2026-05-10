<?php

use App\Http\Controllers\Admin\AddInController;
use App\Http\Controllers\Admin\BranchController as AdminBranchController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\ExcelController;
use App\Http\Controllers\Admin\FloorPlanController as AdminFloorPlanController;
use App\Http\Controllers\Admin\GameTableController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\MicheladaRecipeController;
use App\Http\Controllers\Admin\ProductCategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ScreenController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CaguamaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\FloorPlanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuOrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SongRequestController;
use App\Http\Controllers\BarOrderController;
use App\Http\Controllers\TableController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Kiosk display — no auth required
Route::get('/display', [DisplayController::class, 'index'])->name('display.index');
Route::get('/display/content', [DisplayController::class, 'content'])->name('display.content');

// Menú digital público QR
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/menu/order', [MenuOrderController::class, 'create'])->name('menu.order');
Route::post('/menu/order', [MenuOrderController::class, 'store'])->name('menu.order.store');

// Solicitar canciones (público)
Route::get('/songs', [SongRequestController::class, 'index'])->name('songs.index');
Route::post('/songs', [SongRequestController::class, 'store'])->name('songs.store');
Route::post('/songs/{song}/vote', [SongRequestController::class, 'vote'])->name('songs.vote');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Cambiar sucursal activa
    Route::post('/branch/switch', [BranchController::class, 'switch'])->name('branch.switch');

    // Mesas - operaciones
    Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
    Route::post('/tables/{table}/open', [TableController::class, 'openSession'])->name('tables.open');
    Route::get('/sessions/{session}', [TableController::class, 'showSession'])->name('tables.sessions.show');
    Route::post('/sessions/{session}/items', [TableController::class, 'addItem'])->name('tables.sessions.items.store');
    Route::post('/sessions/{session}/beer-items', [TableController::class, 'addBeerItem'])->name('tables.sessions.beer-items.store');
    Route::delete('/sessions/{session}/items/{itemId}', [TableController::class, 'removeItem'])->name('tables.sessions.items.destroy');
    Route::post('/sessions/{session}/close', [TableController::class, 'closeSession'])->name('tables.sessions.close');

    // Órdenes barra (sin mesa)
    Route::post('/bar-orders', [BarOrderController::class, 'create'])->name('bar-orders.create');
    Route::get('/bar-orders/{order}', [BarOrderController::class, 'show'])->name('bar-orders.show');
    Route::post('/bar-orders/{order}/items', [BarOrderController::class, 'addItem'])->name('bar-orders.items.store');
    Route::post('/bar-orders/{order}/beer-items', [BarOrderController::class, 'addBeerItem'])->name('bar-orders.beer-items.store');
    Route::delete('/bar-orders/{order}/items/{itemId}', [BarOrderController::class, 'removeItem'])->name('bar-orders.items.destroy');
    Route::post('/bar-orders/{order}/close', [BarOrderController::class, 'close'])->name('bar-orders.close');
    Route::post('/bar-orders/{order}/cancel', [BarOrderController::class, 'cancel'])->name('bar-orders.cancel');

    // Floor plan — cajero view
    Route::get('/floor-plan', [FloorPlanController::class, 'index'])->name('floor-plan.index');
    Route::get('/floor-plan/poll', [FloorPlanController::class, 'poll'])->name('floor-plan.poll');

    // Caguamas
    Route::get('/caguamas', [CaguamaController::class, 'index'])->name('caguamas.index');
    Route::post('/caguamas/open', [CaguamaController::class, 'open'])->name('caguamas.open');
    Route::post('/caguamas/{caguama}/pour', [CaguamaController::class, 'pour'])->name('caguamas.pour');
    Route::post('/caguamas/{caguama}/close', [CaguamaController::class, 'close'])->name('caguamas.close');

    // Creación rápida de receta (desde modal de pedido)
    Route::post('/michelada-recipes/quick', [MicheladaRecipeController::class, 'quickStore'])->name('michelada-recipes.quick');

    // Admin
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/tables', [GameTableController::class, 'index'])->name('tables.index');
        Route::post('/tables', [GameTableController::class, 'store'])->name('tables.store');
        Route::put('/tables/{table}', [GameTableController::class, 'update'])->name('tables.update');
        Route::delete('/tables/{table}', [GameTableController::class, 'destroy'])->name('tables.destroy');

        // Categorías
        Route::post('/categories', [ProductCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [ProductCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [ProductCategoryController::class, 'destroy'])->name('categories.destroy');

        // Productos
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        // Receta
        Route::get('/products/{product}/recipe', [ProductController::class, 'showRecipe'])->name('products.recipe.show');
        Route::post('/products/{product}/recipe', [ProductController::class, 'saveRecipe'])->name('products.recipe.save');

        // Modificadores
        Route::post('/products/{product}/modifiers', [ProductController::class, 'saveModifiers'])->name('products.modifiers.save');

        // Recetas de michelada
        Route::post('/michelada-recipes', [MicheladaRecipeController::class, 'store'])->name('michelada-recipes.store');
        Route::put('/michelada-recipes/{recipe}', [MicheladaRecipeController::class, 'update'])->name('michelada-recipes.update');
        Route::delete('/michelada-recipes/{recipe}', [MicheladaRecipeController::class, 'destroy'])->name('michelada-recipes.destroy');

        // Inventario
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/{product}/adjust', [InventoryController::class, 'adjust'])->name('inventory.adjust');
        Route::get('/inventory/{product}/movements', [InventoryController::class, 'movements'])->name('inventory.movements');

        // Excel
        Route::get('/excel', [ExcelController::class, 'index'])->name('excel.index');
        Route::post('/excel/import-products', [ExcelController::class, 'importProducts'])->name('excel.import-products');
        Route::get('/excel/export-sales', [ExcelController::class, 'exportSales'])->name('excel.export-sales');
        Route::get('/excel/export-inventory', [ExcelController::class, 'exportInventory'])->name('excel.export-inventory');

        // Pantallas
        Route::get('/screens', [ScreenController::class, 'index'])->name('screens.index');
        Route::post('/screens', [ScreenController::class, 'store'])->name('screens.store');
        Route::put('/screens/{screen}', [ScreenController::class, 'update'])->name('screens.update');
        Route::delete('/screens/{screen}', [ScreenController::class, 'destroy'])->name('screens.destroy');
        Route::post('/screens/{screen}/activate', [ScreenController::class, 'activate'])->name('screens.activate');
        Route::post('/screens/{screen}/deactivate', [ScreenController::class, 'deactivate'])->name('screens.deactivate');

        // Usuarios
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Configuración
        Route::get('/config', [ConfigController::class, 'index'])->name('config.index');
        Route::post('/config', [ConfigController::class, 'update'])->name('config.update');

        // Sucursales
        Route::get('/branches', [AdminBranchController::class, 'index'])->name('branches.index');
        Route::post('/branches', [AdminBranchController::class, 'store'])->name('branches.store');
        Route::put('/branches/{branch}', [AdminBranchController::class, 'update'])->name('branches.update');
        Route::delete('/branches/{branch}', [AdminBranchController::class, 'destroy'])->name('branches.destroy');
        Route::post('/branches/{branch}/assign-users', [AdminBranchController::class, 'assignUsers'])->name('branches.assign-users');

        // Aditamentos de caguama
        Route::get('/add-ins', [AddInController::class, 'index'])->name('add-ins.index');
        Route::post('/add-ins', [AddInController::class, 'store'])->name('add-ins.store');
        Route::put('/add-ins/{addIn}', [AddInController::class, 'update'])->name('add-ins.update');
        Route::delete('/add-ins/{addIn}', [AddInController::class, 'destroy'])->name('add-ins.destroy');

        // Mapa visual — editor admin
        Route::get('/floor-plan', [AdminFloorPlanController::class, 'edit'])->name('floor-plan.edit');
        Route::post('/floor-plan/positions', [AdminFloorPlanController::class, 'savePositions'])->name('floor-plan.positions');
        Route::post('/floor-plan/config', [AdminFloorPlanController::class, 'saveConfig'])->name('floor-plan.config');
    });
});

require __DIR__.'/auth.php';
