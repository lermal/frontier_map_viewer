<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class PoiRenderController extends Controller
{
    const BLOCK_SIZE = 256;

    public function getBlock(Request $request, $poiId, $x, $y)
    {
        // Получаем имя файла из ID (может содержать путь)
        $imagePath = public_path("images/renders/" . str_replace(':', '/', $poiId) . ".png");

        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        $image = Image::make($imagePath);

        // Вычисляем размеры блока
        $blockWidth = min(self::BLOCK_SIZE, $image->width() - $x);
        $blockHeight = min(self::BLOCK_SIZE, $image->height() - $y);

        // Если координаты за пределами изображения
        if ($blockWidth <= 0 || $blockHeight <= 0) {
            return response()->json(['error' => 'Invalid block coordinates'], 400);
        }

        // Вырезаем блок
        $block = $image->crop($blockWidth, $blockHeight, $x, $y);

        // Отключаем сглаживание
        $block->getCore()->setImageInterpolateMethod(\Imagick::INTERPOLATE_NEAREST_NEIGHBOR);

        return $block->response('png');
    }

    public function getMetadata($poiId)
    {
        // Получаем имя файла из ID (может содержать путь)
        $imagePath = public_path("images/renders/" . str_replace(':', '/', $poiId) . ".png");

        if (!file_exists($imagePath)) {
            return response()->json(['error' => 'Image not found'], 404);
        }

        $image = Image::make($imagePath);

        return response()->json([
            'width' => $image->width(),
            'height' => $image->height(),
            'blockSize' => self::BLOCK_SIZE,
            'blocksX' => ceil($image->width() / self::BLOCK_SIZE),
            'blocksY' => ceil($image->height() / self::BLOCK_SIZE)
        ]);
    }
}
