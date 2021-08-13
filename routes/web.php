<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\FeedController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/',function(){
    return redirect('discover');
});
Route::get('/discover', [NewsController::class, 'discover'])->name('discover');
Route::get('/a/{uuid}', [NewsController::class, 'read'])->name('read');


Route::get('/feed/add', [FeedController::class, 'add'])->name('feed_add');
Route::post('/feed/verify', [FeedController::class, 'verify'])->name('feed_verify');
Route::post('/feed/created', [FeedController::class, 'created'])->name('feed_created');