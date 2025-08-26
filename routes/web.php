<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShuttleViewerController;
use App\Http\Controllers\ShipyardController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\PoiController;
use App\Http\Controllers\PoiRenderController;
use App\Http\Controllers\ShuttleImageController;
use App\Http\Controllers\PoiImageController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [AppController::class, 'index']);

Route::get('/shuttle/{shuttleName}', [ShuttleViewerController::class, 'loadShuttle']);
Route::get('/shipyard/json', [ShipyardController::class, 'convertYmlToJson']);
Route::get('sitemap.xml', [SitemapController::class, 'index']);
Route::get('/poi', [App\Http\Controllers\PoiController::class, 'index'])->name('poi.index');
Route::get('/poi/{poiId}', [App\Http\Controllers\PoiController::class, 'loadPoi']);
Route::get('/poi/render/{poiId}/block/{x}/{y}', [PoiRenderController::class, 'getBlock']);
Route::get('/poi/render/{poiId}/metadata', [PoiRenderController::class, 'getMetadata']);

// Маршруты для обработки изображений
Route::get('/api/shuttle/{shuttleName}', [ShuttleImageController::class, 'processShuttle']);
Route::get('/api/poi/{poiName}', [PoiImageController::class, 'processPoi']);
