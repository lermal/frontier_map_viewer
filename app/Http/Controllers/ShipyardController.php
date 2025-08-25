<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Support\Facades\Log;
class ShipyardController extends Controller
{
    public function convertYmlToJson()
    {
        // Указываем полный путь к директории с YAML-файлами
        $directoryPath = storage_path('app/shuttles/Shipyard');
        $result = [];

        // Рекурсивный обход всех файлов в директории
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directoryPath));

        foreach ($iterator as $file) {
            // Проверяем, является ли файл YAML
            if (pathinfo($file, PATHINFO_EXTENSION) === 'yml') {
                $content = file_get_contents($file);
                $data = Yaml::parse($content, Yaml::PARSE_CUSTOM_TAGS);

                // Проверяем, является ли $data массивом
                if (is_array($data)) {
                    foreach ($data as $item) {
                        // Проверяем, является ли тип "vessel"
                        if (isset($item['type']) && $item['type'] === 'vessel') {
                            $result[] = [
                                'id' => $item['id'] ?? null,
                                'name' => $item['name'] ?? null,
                                'description' => $item['description'] ?? null,
                                'category' => $item['category'] ?? null,
                                'price' => $item['price'] ?? null,
                                'group' => $item['group'] ?? null,
                                'class' => $item['class'] ?? [],
                            ];
                        }
                    }
                } else {
                    $this->info("Содержимое файла не является массивом: " . $file);
                }
            }
        }

        // Сохраняем результат в JSON-файл
        $jsonFilePath = storage_path('app/shuttles/shipyard_data.json');
        file_put_contents($jsonFilePath, json_encode($result, JSON_PRETTY_PRINT));

        return response()->json($result);
    }
}
