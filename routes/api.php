<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AppParamsController;
use App\Http\Controllers\ChangelogController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PermissionsActionsTypesController;
use App\Http\Controllers\AnnouncementsController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\TicketsController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// AUTH ROUTES
// Route::post('login', 'Auth\LoginController@login');
// Route::post('logout', 'Auth\LoginController@logout');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail']);
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
Route::get('/check-user', [AuthController::class, 'checkUser']);

Route::get('/app_params', [AppParamsController::class, 'getAppParams']);
Route::get('/retrieve_app_params', [AppParamsController::class, 'retrieveAppParams']);
Route::get('/changelog', [ChangelogController::class, 'getChangelog']);
Route::get('/changelogs', [ChangelogController::class, 'getChangelogs']);


Route::middleware(['jwt.verify'])->group(function () {
    // PERMISSIONS
    Route::get('/user_permissions', [PermissionsController::class, 'getUserPermissions']);
    Route::get('/permissions', [PermissionsController::class, 'getPermissions']);
    Route::middleware(['check.permission:permissions,view'])->group(function () {
        Route::get('/permissions/filter', [PermissionsController::class, 'filterPermissions']);
        Route::get('/retrievePermissionsData', [PermissionsController::class, 'retrievePermissionsData']);
        Route::get('/permissions/{id}', [PermissionsController::class, 'findPermissions']);
    });
    Route::post('/permissions/create', [PermissionsController::class, 'createPermissions'])->middleware('check.permission:permissions,create');
    Route::put('/permissions/{id}', [PermissionsController::class, 'updatePermissions'])->middleware('check.permission:permissions,edit');
    Route::patch('/permissions/{id}', [PermissionsController::class, 'patchPermissions'])->middleware('check.permission:permissions,edit');
    Route::delete('/permissions', [PermissionsController::class, 'deletePermissions'])->middleware('check.permission:permissions,delete');

    // PERMISSIONS ACTIONS TYPES
    Route::get('/permissions_actions_types/filter', [PermissionsActionsTypesController::class, 'filterPermissionsActionsTypes']);
    Route::get('/permissions_actions_types', [PermissionsActionsTypesController::class, 'getPermissionsActionsTypes']);
    Route::get('/permissions_actions_types/{id}', [PermissionsActionsTypesController::class, 'findPermissionsActionsTypes']);
    Route::post('/permissions_actions_types/create', [PermissionsActionsTypesController::class, 'createPermissionsActionsTypes']);
    Route::put('/permissions_actions_types/{id}', [PermissionsActionsTypesController::class, 'updatePermissionsActionsTypes']);
    Route::patch('/permissions_actions_types/{id}', [PermissionsActionsTypesController::class, 'patchPermissionsActionsTypes']);
    Route::delete('/permissions_actions_types', [PermissionsActionsTypesController::class, 'deletePermissionsActionsTypes']);

    // ANNOUNCEMENTS
    Route::get('/announcements', [AnnouncementsController::class, 'getActiveAnnouncements']);
    Route::get('/announcements/filter', [AnnouncementsController::class, 'filterAnnouncements']);
    Route::get('announcements', [AnnouncementsController::class, 'getAnnouncements']);
    Route::post('/announcements/create', [AnnouncementsController::class, 'createAnnouncements']);
    Route::put('/announcements/{id}', [AnnouncementsController::class, 'updateAnnouncements']);
    Route::delete('/announcements', [AnnouncementsController::class, 'deleteAnnouncements']);

    // USER
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/users/filter', [UserController::class, 'filterUsers']);
    Route::get('/users', [UserController::class, 'getUsers']);
    Route::get('/users/{id}', [UserController::class, 'findUser']);
    Route::put('/users/{id}', [UserController::class, 'updateUser']);
    Route::patch('/users/{id}', [UserController::class, 'patchUser']);
    Route::delete('/users', [UserController::class, 'deleteUser']);

    // ROLES
    Route::get('/roles/filter', [RoleController::class, 'filterRoles']);
    Route::get('/roles', [RoleController::class, 'getRoles']);
    Route::get('/roles/{id}', [RoleController::class, 'findRoles']);
    Route::post('/roles/create', [RoleController::class, 'createRoles']);
    Route::put('/roles/{id}', [RoleController::class, 'updateRoles']);
    Route::patch('/roles/{id}', [RoleController::class, 'patchRoles']);
    Route::delete('/roles', [RoleController::class, 'deleteRoles']);

    //TICKETS
    Route::get('/tickets/filter', [TicketsController::class, 'filterTickets']);
    Route::get('/tickets', [TicketsController::class, 'getTickets']);
    Route::get('/tickets/{id}', [TicketsController::class, 'findTickets']);
    Route::post('/tickets/create', [TicketsController::class, 'createTickets']);
    Route::put('/tickets/{id}', [TicketsController::class, 'updateTickets']);
    Route::delete('/tickets', [TicketsController::class, 'deleteTickets']);
    Route::post('/tickets/send-email/{id}', [TicketsController::class, 'sendEmail']);
    Route::get('/tickets/{id}/file/{fileName}', [TicketsController::class, 'downloadTicketFile']);


});

