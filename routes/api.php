
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\BenefitController;
use App\Http\Controllers\Api\NewsletterController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\AdminTicketController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth (público)
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register-customer', [AuthController::class, 'registerCustomer']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->middleware('throttle:3,1');
    Route::post('/google', [AuthController::class, 'googleAuth']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/validate', [AuthController::class, 'validateToken']);
        Route::post('/refresh', [AuthController::class, 'refreshToken']);
    });
});

/*
|--------------------------------------------------------------------------
| Público (sin auth)
|--------------------------------------------------------------------------
*/
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Home (público)
|--------------------------------------------------------------------------
*/
Route::get('/home/heroes', [HomeController::class, 'heroes']);
Route::get('/home/banners-pub', [HomeController::class, 'banners']);
Route::get('/home/section/{slug}', [HomeController::class, 'categorySection']);
Route::get('/brands', [BrandController::class, 'index']);
Route::get('/benefits', [BenefitController::class, 'index']);
Route::post('/newsletter', [NewsletterController::class, 'subscribe']);

/*
|--------------------------------------------------------------------------
| Autenticado (cualquier rol)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Users
    Route::get('/users/me', [UserController::class, 'me']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/{id}', [NotificationController::class, 'show']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'read']);
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    /*
    |----------------------------------------------------------------------
    | Admin
    |----------------------------------------------------------------------
    */
    Route::middleware('role:administrator')->group(function () {
        // Users management
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/users/role/{role}', [UserController::class, 'byRole']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        // Stores management
        Route::get('/stores', [StoreController::class, 'index']);
        Route::get('/stores/{id}', [StoreController::class, 'show']);
        Route::put('/stores/{id}/status', [StoreController::class, 'updateStatus']);

        // Categories CRUD
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        // Products: aprobar/rechazar
        Route::put('/products/{id}/status', [ProductController::class, 'updateStatus']);

        // Suppliers CRUD
        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
        Route::post('/suppliers', [SupplierController::class, 'store']);
        Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
        Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);

        // Contracts CRUD
        Route::get('/contracts', [ContractController::class, 'index']);
        Route::get('/contracts/{id}', [ContractController::class, 'show']);
        Route::post('/contracts', [ContractController::class, 'store']);
        Route::put('/contracts/{id}', [ContractController::class, 'update']);
        Route::put('/contracts/{id}/status', [ContractController::class, 'updateStatus']);
        Route::post('/contracts/{id}/upload', [ContractController::class, 'upload']);
        Route::get('/contracts/{id}/download', [ContractController::class, 'download']);
        Route::delete('/contracts/{id}', [ContractController::class, 'destroy']);

        // Tickets — Admin (Mesa de Ayuda)
        Route::prefix('admin/tickets')->group(function () {
            Route::get('/', [AdminTicketController::class, 'index']);
            Route::get('/{id}', [AdminTicketController::class, 'show']);
            Route::post('/{id}/messages', [AdminTicketController::class, 'sendMessage']);
            Route::put('/{id}/status', [AdminTicketController::class, 'updateStatus']);
            Route::put('/{id}/assign', [AdminTicketController::class, 'assign']);
            Route::put('/{id}/priority', [AdminTicketController::class, 'updatePriority']);
            Route::put('/{id}/escalate', [AdminTicketController::class, 'escalate']);
        });
    });

    /*
    |----------------------------------------------------------------------
    | Seller
    |----------------------------------------------------------------------
    */
    Route::middleware('role:seller,administrator')->group(function () {
        // Store propio
        Route::post('/stores', [StoreController::class, 'store']);
        Route::put('/stores/{id}', [StoreController::class, 'update']);

        // Products CRUD
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::put('/products/{id}/stock', [ProductController::class, 'updateStock']);

        // Tickets — Vendedor (Mesa de Ayuda)
        Route::prefix('tickets')->group(function () {
            Route::get('/', [TicketController::class, 'index']);
            Route::post('/', [TicketController::class, 'store']);
            Route::get('/{id}', [TicketController::class, 'show']);
            Route::post('/{id}/messages', [TicketController::class, 'sendMessage']);
            Route::put('/{id}/close', [TicketController::class, 'close']);
            Route::post('/{id}/survey', [TicketController::class, 'submitSurvey']);
        });
    });
});
