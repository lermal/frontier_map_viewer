<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PoiController extends Controller
{
    public function index()
    {
        $pois = $this->getPois();

        // Группируем POI по первой букве имени
        $groupedPoi = collect($pois)->groupBy(function ($item) {
            return substr($item['name'], 0, 1);
        })->sortKeys();

        return view('poi.index', [
            'pois' => $pois,
            'groupedPoi' => $groupedPoi
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
