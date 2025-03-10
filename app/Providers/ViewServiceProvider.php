<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;

class ViewServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        View::composer('layouts.navigation', function ($view) {
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

            $uniqueCategories = collect($shuttleData)->pluck('category')->unique()->values();
            $uniqueClasses = collect($shuttleData)->pluck('class')->flatten()->unique()->values();
            $uniqueEngines = collect($shuttleData)->pluck('engine')->flatten()->unique()->values();

            $view->with([
                'uniqueCategories' => $uniqueCategories,
                'uniqueClasses' => $uniqueClasses,
                'uniqueEngines' => $uniqueEngines,
                'groupedShuttles' => $groupedShuttles
            ]);
        });
    }
}
