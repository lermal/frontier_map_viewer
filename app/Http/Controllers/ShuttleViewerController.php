<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;

class ShuttleViewerController extends Controller
{
    public function loadShuttle(Request $request, $shuttleName)
    {
        Log::info('Запрос шаттла: ' . $shuttleName);

        // Нормализуем путь, используя DIRECTORY_SEPARATOR
        $basePath = public_path('images' . DIRECTORY_SEPARATOR . 'renders');
        $path = $basePath . DIRECTORY_SEPARATOR . $shuttleName . '-0.png';

        Log::info('Базовая директория существует: ' . (is_dir($basePath) ? 'да' : 'нет'));
        Log::info('Содержимое директории: ' . implode(', ', scandir($basePath)));
        Log::info('Проверяемый путь: ' . $path);
        Log::info('Файл существует: ' . (file_exists($path) ? 'да' : 'нет'));

        // Проверка существования файла
        if (!file_exists($path)) {
            // Попробуем другие варианты расширения файла
            $alternativePaths = [
                $basePath . DIRECTORY_SEPARATOR . $shuttleName . '.PNG',
                $basePath . DIRECTORY_SEPARATOR . $shuttleName . '.jpg',
                $basePath . DIRECTORY_SEPARATOR . $shuttleName . '.jpeg',
                $basePath . DIRECTORY_SEPARATOR . $shuttleName . '.JPG',
                $basePath . DIRECTORY_SEPARATOR . $shuttleName . '.webp'
            ];

            $found = false;
            foreach ($alternativePaths as $altPath) {
                Log::info('Проверка альтернативного пути: ' . $altPath);
                Log::info('Существует: ' . (file_exists($altPath) ? 'да' : 'нет'));
                if (file_exists($altPath)) {
                    $path = $altPath;
                    $found = true;
                    Log::info('Найден файл шаттла: ' . $path);
                    Log::info('Время последней модификации: ' . date('Y-m-d H:i:s', filemtime($path)));
                    break;
                }
            }

            if (!$found) {
                Log::error('Шаттл не найден: ' . $shuttleName);
                return response()->json(['error' => 'Шаттл не найден'], 404);
            }
        }

        // Получаем размеры изображения
        $image = Image::read($path);
        $width = $image->width();
        $height = $image->height();
        $blockSize = 256;

        // Проверяем и создаем директорию для блоков в storage
        $blocksDir = storage_path('app/public/blocks');
        Log::info("Путь к директории блоков: " . $blocksDir);
        Log::info("Директория существует: " . (file_exists($blocksDir) ? 'да' : 'нет'));

        // Очищаем старые блоки для этого шаттла
        if (file_exists($blocksDir)) {
            $pattern = $blocksDir . DIRECTORY_SEPARATOR . $shuttleName . '_block_*';
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

                $blockName = sprintf("%s_block_%d_%d_%d.png", $shuttleName, $blockIndex, $x, $y);
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

        return response()->json([
            'blocks' => $blocks,
            'width' => $width,
            'height' => $height
        ]);
    }

    private function getImageBlocks($imagePath)
    {
        // Здесь вы можете использовать библиотеку для обработки изображений, например, Intervention Image
        // Пример разбивки изображения на блоки 256x256 пикселей
        $blocks = [];
        $image = Image::read($imagePath);
        $width = $image->width();
        $height = $image->height();
        $blockSize = 256; // Размер блока

        for ($y = 0; $y < $height; $y += $blockSize) {
            for ($x = 0; $x < $width; $x += $blockSize) {
                $block = $image->crop($blockSize, $blockSize, $x, $y);
                $blockPath = "shuttles/blocks/{$x}_{$y}.png";
                $block->save(storage_path("app/{$blockPath}"));
                $blocks[] = asset("storage/{$blockPath}");
            }
        }

        return $blocks;
    }
}
