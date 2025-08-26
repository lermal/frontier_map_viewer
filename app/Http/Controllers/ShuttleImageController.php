<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShuttleImageController extends ImageProcessingController
{
    public function processShuttle(Request $request, $shuttleName)
    {
        Log::info('Запрос шаттла: ' . $shuttleName);

        // Проверяем различные возможные расширения файла
        $possibleExtensions = ['png', 'PNG', 'jpg', 'jpeg', 'JPG', 'webp'];
        $imagePath = null;

        foreach ($possibleExtensions as $ext) {
            $path = public_path("images/renders/{$shuttleName}.{$ext}");
            if (file_exists($path)) {
                $imagePath = $path;
                break;
            }
        }

        if (!$imagePath) {
            Log::error('Шаттл не найден: ' . $shuttleName);
            return response()->json(['error' => 'Шаттл не найден'], 404);
        }

        $outputDir = public_path('images/blocks');
        $result = $this->processImage($imagePath, $outputDir, $shuttleName);

        if (!$result['success']) {
            return response()->json(['error' => $result['error']], 500);
        }

        return response()->json($result['data']);
    }
}
