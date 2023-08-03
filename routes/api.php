<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\ProjectsController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::middleware('web')->get('/sanctum/csrf-cookie', function (Request $request) {
    return response()->json(['csrf_token' => csrf_token()]);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    # Users
    Route::apiResource('users',UsersController::class)->middleware('permission:user');
    Route::post('user/activate/{id}',[UsersController::class,'activate'])->middleware('permission:user-edit');
    Route::post('user/deactivate/{id}',[UsersController::class,'deactivate'])->middleware('permission:user-edit');

    # Customers
    Route::apiResource('customers',CustomersController::class)->middleware('permission:customer');

    # Projects
    Route::apiResource('projects',ProjectsController::class)->middleware('permission:project');

    # Invoices
    Route::apiResource('invoices',InvoicesController::class)->middleware('permission:invoice');

    # Payments
    Route::apiResource('payments',PaymentsController::class)->middleware('permission:payment');

    # Roles
    Route::get('roles/permissions',[RolesController::class,'allPermissions'])->middleware('permission:role-create');
    Route::apiResource('roles',RolesController::class)->middleware('permission:role');;


});


Route::prefix('auth')->group(function () {
	Route::post('login', [AuthController::class,'login'])->name('auth.login');
    Route::post('register', [AuthController::class, 'register']);
	Route::post('forgot-password', [AuthController::class,'sendResetLinkEmail'])->name('auth.forgotPassword');
	Route::post('reset-password', [AuthController::class,'resetPassword'])->name('auth.resetPassword');
	Route::post('logout', [AuthController::class,'logout'])->middleware('auth:sanctum')->name('auth.logout');
	Route::post('change-password', [AuthController::class,'changePassword'])->middleware('auth:sanctum')->name('auth.changePassword');

});


