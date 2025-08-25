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

            // Преобразуем новую структуру данных в ожидаемый формат
            $groupedShuttles = [];
            foreach ($shuttleData as $groupName => $shuttles) {
                $groupedShuttles[$groupName] = ['default' => $shuttles];
            }

            // Сортируем группы в нужном порядке
            uksort($groupedShuttles, function($a, $b) use ($groupOrder) {
                $orderA = $groupOrder[$a] ?? 999;
                $orderB = $groupOrder[$b] ?? 999;
                return $orderA - $orderB;
            });

            // Создаем плоский массив всех шаттлов для получения уникальных значений
            $allShuttles = [];
            foreach ($shuttleData as $shuttles) {
                $allShuttles = array_merge($allShuttles, $shuttles);
            }

            $uniqueCategories = collect($allShuttles)->pluck('category')->unique()->values();
            $uniqueClasses = collect($allShuttles)->pluck('class')->flatten()->unique()->values();
            $uniqueEngines = collect($allShuttles)->pluck('engine')->flatten()->unique()->values();

            $view->with([
                'uniqueCategories' => $uniqueCategories,
                'uniqueClasses' => $uniqueClasses,
                'uniqueEngines' => $uniqueEngines,
                'groupedShuttles' => $groupedShuttles
            ]);
        });
    }
}
