<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShuttleViewerController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\PoiImageController;
use Illuminate\Support\Facades\Storage;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/shuttle/{shuttleName}', [ShuttleViewerController::class, 'loadShuttle']);

Route::get('/shuttles/get', [AppController::class, 'getShuttlesJson']);

Route::get('/poi/{id}', function ($id) {
    $poiData = json_decode(Storage::get('poi/poi_data.json'), true);

    $poi = collect($poiData)->firstWhere('id', $id);

    if (!$poi) {
        return response()->json(['error' => 'POI not found'], 404);
    }

    // Здесь можно добавить загрузку дополнительных данных из YAML файла
    // $yamlContent = Storage::get($poi['file_path']);
    // $poiDetails = Yaml::parse($yamlContent);

    return response()->json([
        'id' => $poi['id'],
        'name' => $poi['name'],
        'file_path' => $poi['file_path'],
        'description' => 'Description will be loaded from YAML file' // Временно
    ]);
});

// Маршрут для обработки изображений POI
Route::get('/poi/{poiName}/image', [PoiImageController::class, 'processPoi']);
