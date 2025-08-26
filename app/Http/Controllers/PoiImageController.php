<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Laravel\Facades\Image;

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
        $poiId = strtolower(urldecode($poiId));

        // Проверяем наличие файла в директории renders
        $imagePath = public_path("images/renders/{$poiId}/{$poiId}-0.png");

        Log::info('Проверяем путь: ' . $imagePath);

        if (!file_exists($imagePath)) {
            Log::error('Рендер не найден: ' . $poiId);
            return response()->json(['error' => 'Рендер не найден'], 404);
        }

        Log::info('Найден файл: ' . $imagePath);

        // Получаем размеры изображения
        $image = Image::read($imagePath);
        $width = $image->width();
        $height = $image->height();
        $blockSize = 256;

        // Проверяем и создаем директорию для блоков в storage
        $blocksDir = storage_path('app/public/blocks');
        Log::info("Путь к директории блоков: " . $blocksDir);
        Log::info("Директория существует: " . (file_exists($blocksDir) ? 'да' : 'нет'));

        // Очищаем старые блоки для этого POI
        $safePoiId = str_replace(['\\', '/', ' ', ':'], '_', $poiId);
        if (file_exists($blocksDir)) {
            $pattern = $blocksDir . DIRECTORY_SEPARATOR . $safePoiId . '_block_*';
            Log::info("Удаляем старые блоки по шаблону: " . $pattern);
            $deletedCount = 0;
            foreach (glob($pattern) as $oldBlock) {
                if (unlink($oldBlock)) {
                    $deletedCount++;
                }
            }
            Log::info("Удалено старых блоков: " . $deletedCount);
        }

        if (!file_exists($blocksDir)) {
            try {
                Log::info("Пытаемся создать директорию: " . $blocksDir);
                if (!mkdir($blocksDir, 0755, true)) {
                    Log::error("Не удалось создать директорию: " . $blocksDir);
                    return response()->json(['error' => 'Ошибка сервера при создании директории'], 500);
                }
            } catch (\Exception $e) {
                Log::error("Ошибка при создании директории: " . $e->getMessage());
                return response()->json(['error' => 'Ошибка сервера при обработке изображения'], 500);
            }
        }

        Log::info("Права на директорию: " . substr(sprintf('%o', fileperms($blocksDir)), -4));

        // Создаем массив блоков
        $blocks = [];
        $blockIndex = 0;

        for ($y = 0; $y < $height; $y += $blockSize) {
            for ($x = 0; $x < $width; $x += $blockSize) {
                $blockWidth = min($blockSize, $width - $x);
                $blockHeight = min($blockSize, $height - $y);

                $blockName = sprintf("%s_block_%d_%d_%d.png", $safePoiId, $blockIndex, $x, $y);
                $blockPath = "storage/blocks/{$blockName}";
                $fullBlockPath = storage_path("app/public/blocks/{$blockName}");

                Log::info("Попытка создания блока: " . $fullBlockPath);
                Log::info("Размеры блока: {$blockWidth}x{$blockHeight} на позиции {$x},{$y}");

                try {
                    // Создаем копию исходного изображения для этого блока
                    $blockImage = clone $image;

                    // Обрезаем изображение до нужного размера
                    $blockImage->crop($blockWidth, $blockHeight, $x, $y);

                    // Если блок уже существует, удаляем его
                    if (file_exists($fullBlockPath)) {
                        unlink($fullBlockPath);
                        Log::info("Удален существующий блок: " . $fullBlockPath);
                    }

                    // Сохраняем блок
                    $result = $blockImage->save($fullBlockPath, 'webp', 80);
                    Log::info("Результат сохранения: " . ($result ? 'успешно' : 'ошибка'));

                    // Проверяем, создался ли файл и его время создания
                    if (file_exists($fullBlockPath)) {
                        Log::info("Файл успешно создан: " . $fullBlockPath);
                        Log::info("Размер файла: " . filesize($fullBlockPath) . " байт");
                        Log::info("Время создания блока: " . date('Y-m-d H:i:s', filemtime($fullBlockPath)));
                    } else {
                        Log::error("Файл не был создан после сохранения: " . $fullBlockPath);
                    }

                    $timestamp = time();
                    $blocks[] = [
                        'url' => asset($blockPath) . '?v=' . $timestamp,
                        'x' => $x,
                        'y' => $y,
                        'width' => $blockWidth,
                        'height' => $blockHeight
                    ];

                    $blockIndex++;
                } catch (\Exception $e) {
                    Log::error("Ошибка при создании блока {$blockName}: " . $e->getMessage());
                    Log::error("Trace: " . $e->getTraceAsString());
                }
            }
        }

        Log::info("Всего блоков в массиве: " . count($blocks));
        Log::info("Содержимое директории после обработки: " . implode(", ", scandir($blocksDir)));

        $result = [
            'blocks' => $blocks,
            'width' => $width,
            'height' => $height
        ];

        // Кэшируем результат на 24 часа
        Cache::put($cacheKey, $result, now()->addHours(24));

        return response()->json($result);
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
