<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PoiController extends Controller
{
    public function index()
    {
        $pois = $this->getPois();

        // Группируем POI по первой букве имени, как в шаттлах
        $groupedPoi = collect($pois)->groupBy(function ($item) {
            return strtoupper(substr($item['name'], 0, 1));
        })->sortKeys();

        // Получаем уникальные категории для фильтров (если нужно в будущем)
        $uniqueCategories = collect($pois)->pluck('category')->unique()->filter()->sort()->values();
        $uniqueTypes = collect($pois)->pluck('type')->unique()->filter()->sort()->values();

        return view('poi.index', [
            'pois' => $pois,
            'groupedPoi' => $groupedPoi,
            'uniqueCategories' => $uniqueCategories,
            'uniqueTypes' => $uniqueTypes
        ]);
    }

    public function loadPoi(Request $request, $poiId)
    {
        // Декодируем ID из URL
        $poiId = strtolower(urldecode($poiId));
        
        // Проверяем наличие файла рендера
        $imagePath = public_path("images/renders/{$poiId}/{$poiId}-0.png");
        
        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'POI не найден'], 404);
        }

        // Получаем информацию о POI из данных
        $pois = $this->getPois();
        $poi = collect($pois)->firstWhere('id', $poiId);
        
        if (!$poi) {
            return response()->json(['error' => 'Информация о POI не найдена'], 404);
        }

        return response()->json([
            'success' => true,
            'poi' => $poi,
            'imagePath' => "/images/renders/{$poiId}/{$poiId}-0.png"
        ]);
    }

    private function getPois()
    {
        $path = storage_path('app/poi/poi_data.json');
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }
        return [];
    }
}
