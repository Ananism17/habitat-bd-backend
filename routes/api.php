<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\Admin\RoleController;
use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Admin\PermissionController;
use App\Http\Controllers\Api\AlbumController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\MailController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\SliderImageController;

// Route::get('admin-list',[AdminController::class,'index']);
Route::group(['middleware' => ['api', 'throttle:60,1'], 'prefix' => 'v1/auth'], function ($router) {
    Route::post('login',  [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('register', [AuthController::class, 'store']);
    // Route::post('/send-otp', [AuthController::class ,'sendingOtp']);
    Route::post('verify-token', [AuthController::class, 'verifyToken']);
    Route::get('profile', [AuthController::class, 'me']);
    Route::post('profile/update-photo', [UserController::class, 'userChangePhoto']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    // Route::post('change-password', function(){
    //     return "Ahsan Ullah";
    // });
    // Route::post('profile/update', [UserController::class,'userProfileUpdate']);

});

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {
    Route::get('admin-list', [AdminController::class, 'index']);
    Route::get('admins/{id}', [AdminController::class, 'show']);
    Route::post('admins/update', [AdminController::class, 'update']);
    Route::post('admin/store', [AdminController::class, 'store']);
    Route::get('admin/{query}', [AdminController::class, 'searchByPhoneNameEmail']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {
    Route::get('permissions', [PermissionController::class, 'index']);
    Route::post('permissions/create', [PermissionController::class, 'create']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('roles/create', [RoleController::class, 'create']);
    Route::get('roles/{id}', [RoleController::class, 'show']);
    Route::post('roles/update', [RoleController::class, 'update']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'jwt.verify', 'throttle:60,1']], function () {

    Route::controller(AlbumController::class)->group(function () {
        Route::get('albums', 'index');
        Route::get('albums/{id}', 'show');
        Route::post('albums', 'store');
        Route::post('albums/update', 'update');
        Route::post('albums/delete', 'destroy');
        Route::post('albums/image/delete', 'destroyImage');
    });

    Route::controller(PageController::class)->group(function () {
        Route::get('pages', 'index');
        Route::get('pages/{id}', 'show');
        Route::post('pages', 'store');
        Route::post('pages/update', 'update');
        Route::post('pages/delete', 'destroy');

        //page-contents
        Route::get('page-contents', 'contentIndex');
        Route::get('page-contents/{id}', 'showContent');
        Route::post('page-contents', 'createContent');
        Route::post('page-contents/update', 'updateContent');
        Route::post('page-contents/delete', 'destroyContent');
    });

    Route::controller(ContentController::class)->group(function () {
        Route::get('contents', 'index');
        Route::get('contents/{id}', 'show');
        Route::post('contents', 'store');
        Route::post('contents/update', 'update');
        Route::post('contents/delete', 'destroy');
        Route::post('contents/update-description', 'updateDescription');
    });
    
    Route::controller(SliderImageController::class)->group(function () {
        Route::get('slider', 'index');
        Route::get('slider/{id}', 'show');
        Route::post('slider', 'store');
        Route::post('slider/update', 'update');
        Route::post('slider/delete', 'destroy');
    });

});

Route::group(['prefix' => 'v1/public/', 'middleware' => ['api', 'throttle:60,1']], function () {
    //pages
    Route::get('pages', [PageController::class, 'indexPublic']);
    Route::get('pages/{slug}', [PageController::class, 'showPublic']);

    //albums
    Route::get('albums', [AlbumController::class, 'indexPublic']);
    Route::get('albums/{slug}', [AlbumController::class, 'showPublic']);

    //contents
    Route::get('contents', [ContentController::class, 'indexPublic']);
    Route::get('contents/{slug}', [ContentController::class, 'showPublic']);
    
    //slider
    Route::get('slider', [SliderImageController::class, 'indexPublic']);
    
    //mail
    Route::post('contact', [MailController::class, 'index']);
});