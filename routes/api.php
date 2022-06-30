<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->group( function (){
    Route::get('getPublishers',[\App\Http\Controllers\BookController::class,'getPublishers']);
    Route::get('getAuthors',[\App\Http\Controllers\BookController::class,'getAuthors']);
    Route::get('/searchOrder',[\App\Http\Controllers\BookController::class,'searchOrder']);
    Route::get('/search',[\App\Http\Controllers\BookController::class,'search']);
    Route::get('/searchBuyIdOrTitle',[\App\Http\Controllers\BookController::class,'searchBuyIdOrTitle']);
    Route::post('/addPublisher',[\App\Http\Controllers\BookController::class,'addPublisher']);
    Route::post('/addAuthors',[\App\Http\Controllers\BookController::class,'addAuthors']);
    Route::post('/addBook',[\App\Http\Controllers\BookController::class,'addBook']);
    Route::post('/addOrder',[\App\Http\Controllers\BookController::class,'addOrder']);
    Route::get('/export',[\App\Http\Controllers\BookController::class,'export']);
    Route::get('/Ordersexport',[\App\Http\Controllers\BookController::class,'Ordersexport']);

});
Route::post('/login',[\App\Http\Controllers\AuthController::class,'login']);
Route::post('/signup',[\App\Http\Controllers\AuthController::class,'signup']);





