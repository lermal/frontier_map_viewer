<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;

abstract class ImageProcessingController extends Controller
{
    protected $blockSize = 256;

    protected function processImage($imagePath, $outputDir, $name)
    {
        try {
            // Проверяем и создаем директорию
            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0755, true);
            }

            // Загружаем изображение
            $image = Image::read($imagePath);
            $width = $image->width();
            $height = $image->height();

            // Очищаем старые блоки
            $pattern = $outputDir . DIRECTORY_SEPARATOR . $name . '_block_*';
            array_map('unlink', glob($pattern));

            // Создаем блоки
            $blocks = [];
            $blockIndex = 0;

            for ($y = 0; $y < $height; $y += $this->blockSize) {
                for ($x = 0; $x < $width; $x += $this->blockSize) {
                    $blockWidth = min($this->blockSize, $width - $x);
                    $blockHeight = min($this->blockSize, $height - $y);

                    $blockName = sprintf("%s_block_%d_%d_%d.png", $name, $blockIndex, $x, $y);
                    $blockPath = $outputDir . DIRECTORY_SEPARATOR . $blockName;

                    // Создаем блок
                    $blockImage = clone $image;
                    $blockImage->crop($blockWidth, $blockHeight, $x, $y);
                    $blockImage->save($blockPath, 'png', 80);

                    $blocks[] = [
                        'url' => asset("images/blocks/{$blockName}"),
                        'x' => $x,
                        'y' => $y,
                        'width' => $blockWidth,
                        'height' => $blockHeight,
                        'index' => $blockIndex
                    ];

                    $blockIndex++;
                }
            }

            return [
                'success' => true,
                'data' => [
                    'blocks' => $blocks,
                    'width' => $width,
                    'height' => $height
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Ошибка при обработке изображения: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
