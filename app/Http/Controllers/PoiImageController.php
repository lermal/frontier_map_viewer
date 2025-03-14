<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PoiImageController extends ImageProcessingController
{
    public function processPoi(Request $request, $poiId)
    {
        Log::info('Запрос рендера по ID: ' . $poiId);

        // Пробуем получить результат из кэша
        $cacheKey = 'poi_render_' . $poiId;
        $cachedResult = Cache::get($cacheKey);

        if ($cachedResult) {
            Log::info('Возвращаем результат из кэша для: ' . $poiId);
            return response()->json($cachedResult);
        }

        // Декодируем ID из URL
        $poiId = urldecode($poiId);

        // Проверяем наличие файла в директории renders
        $imagePath = public_path("images/renders/{$poiId}-0.png");

        Log::info('Проверяем путь: ' . $imagePath);

        if (!file_exists($imagePath)) {
            Log::error('Рендер не найден: ' . $poiId);
            return response()->json(['error' => 'Рендер не найден'], 404);
        }

        Log::info('Найден файл: ' . $imagePath);

        // Создаем директорию для блоков, если её нет
        $outputDir = public_path('storage/blocks');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Очищаем старые блоки для этого рендера
        $safePoiId = str_replace(['\\', '/', ' ', ':'], '_', $poiId);
        $pattern = $outputDir . DIRECTORY_SEPARATOR . $safePoiId . '_block_*';
        array_map('unlink', glob($pattern) ?: []);

        // Обрабатываем изображение
        $result = $this->processImage(
            $imagePath,
            $outputDir,
            $safePoiId
        );

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 500);
        }

        // Кэшируем результат на 24 часа
        Cache::put($cacheKey, $result['data'], now()->addHours(12));

        return response()->json($result['data']);
    }

    private function getExistingBlocksData($blockFiles, $imagePath)
    {
        // Получаем размеры оригинального изображения
        list($width, $height) = getimagesize($imagePath);

        $blocks = [];
        foreach ($blockFiles as $blockFile) {
            if (preg_match('/_block_(\d+)_(\d+)\.png$/', $blockFile, $matches)) {
                list($blockWidth, $blockHeight) = getimagesize($blockFile);
                $blocks[] = [
                    'url' => str_replace(public_path(), '', $blockFile),
                    'x' => (int)$matches[1],
                    'y' => (int)$matches[2],
                    'width' => $blockWidth,
                    'height' => $blockHeight
                ];
            }
        }

        return [
            'success' => true,
            'data' => [
                'width' => $width,
                'height' => $height,
                'blocks' => $blocks
            ]
        ];
    }
}
