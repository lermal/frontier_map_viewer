<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppController extends Controller
{
    public function index()
    {
        $shuttleData = json_decode(Storage::get('shuttles/shipyard_data.json'), true);

        // Определяем порядок групп
        $groupOrder = [
            'Shipyard' => 1,
            'Medical' => 2,
            'McCargo' => 3,
            'Expedition' => 4,
            'Security' => 5,
            'Custom' => 6,
            'BlackMarket' => 7,
            'Sr' => 8,
            'Scrap' => 9,
            'Syndicate' => 10,
        ];

        // Группируем шаттлы
        $groupedShuttles = [];
        foreach ($shuttleData as $shuttle) {
            $group = $shuttle['group'] ?? '';
            if (!isset($groupedShuttles[$group])) {
                $groupedShuttles[$group] = ['default' => []];
            }
            $groupedShuttles[$group]['default'][] = $shuttle;
        }

        // Сортируем группы в нужном порядке
        uksort($groupedShuttles, function($a, $b) use ($groupOrder) {
            $orderA = $groupOrder[$a] ?? 999;
            $orderB = $groupOrder[$b] ?? 999;
            return $orderA - $orderB;
        });

        return view('layouts.shipyard', [
            'groupedShuttles' => $groupedShuttles
        ]);
    }

    public function getShuttlesJson()
    {
        $shuttleData = json_decode(Storage::get('shuttles/shipyard_data.json'), true);

        // Группируем шаттлы
        $groupedShuttles = [];
        foreach ($shuttleData as $shuttle) {
            $group = $shuttle['group'] ?? '';
            if (!isset($groupedShuttles[$group])) {
                $groupedShuttles[$group] = ['default' => []];
            }
            $groupedShuttles[$group]['default'][] = $shuttle;
        }

        return response()->json($groupedShuttles);
    }
}
