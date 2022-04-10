<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Models\Product;
use App\Events\ActionEvent;
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

Route::get('/', [UploadController::class, 'index']);
Route::post('/upload', [UploadController::class, 'store']);
