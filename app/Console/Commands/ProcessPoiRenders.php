<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\PoiImageController;
use Illuminate\Support\Facades\Log;

class ProcessPoiRenders extends Command
{
    protected $signature = 'poi:process-renders';
    protected $description = 'Process all POI renders into blocks';

    public function handle()
    {
        $this->info('Starting POI renders processing...');

        // Путь к директории с рендерами
        $rendersPath = public_path('images/renders');

        // Получаем все файлы с расширением -0.png
        $renders = glob($rendersPath . '/*-0.png');

        if (empty($renders)) {
            $this->error('No renders found in ' . $rendersPath);
            return;
        }

        $controller = new PoiImageController();

        foreach ($renders as $render) {
            $fileName = basename($render);
            // Убираем -0.png из имени файла
            $poiId = str_replace('-0.png', '', $fileName);

            $this->info("Processing render for POI: {$poiId}");

            try {
                // Создаем фейковый request
                $request = new \Illuminate\Http\Request();

                // Обрабатываем рендер
                $result = $controller->processPoi($request, $poiId);

                if ($result->getStatusCode() === 200) {
                    $this->info("Successfully processed {$poiId}");
                } else {
                    $this->error("Failed to process {$poiId}");
                }
            } catch (\Exception $e) {
                $this->error("Error processing {$poiId}: " . $e->getMessage());
                Log::error("Error processing POI render {$poiId}: " . $e->getMessage());
            }
        }

        $this->info('Finished processing POI renders');
    }
}
