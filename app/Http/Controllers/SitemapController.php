<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SitemapController extends Controller
{
    private $baseUrl = 'https://shipyard.frontierstation14.com';

    public function index()
    {
        $content = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        // Добавляем главную страницу
        $content .= $this->addUrl($this->baseUrl, '1.0', 'daily');

        // Добавляем страницу POI
        $content .= $this->addUrl($this->baseUrl . '/poi', '0.8', 'daily');

        // Добавляем все POI
        $pois = $this->getPois();
        if (!empty($pois)) {
            foreach ($pois as $poi) {
                $content .= $this->addUrl(
                    $this->baseUrl . '/poi?id=' . urlencode($poi['id']),
                    '0.7',
                    'weekly'
                );
            }
        } else {
            Log::error('POI data is empty or could not be loaded');
        }

        // Добавляем все шаттлы
        $shuttleData = $this->getShuttles();
        // Преобразуем новую структуру данных в плоский массив
        $allShuttles = [];
        foreach ($shuttleData as $groupName => $shuttles) {
            $allShuttles = array_merge($allShuttles, $shuttles);
        }
        
        foreach ($allShuttles as $shuttle) {
            $content .= $this->addUrl(
                $this->baseUrl . '/?shuttle=' . urlencode($shuttle['id']),
                '0.7',
                'weekly'
            );
        }

        $content .= '</urlset>';

        return response($content, 200)
            ->header('Content-Type', 'text/xml');
    }

    private function addUrl($url, $priority, $changefreq)
    {
        return sprintf(
            "\t<url>\n\t\t<loc>%s</loc>\n\t\t<priority>%s</priority>\n\t\t<changefreq>%s</changefreq>\n\t</url>\n",
            $url,
            $priority,
            $changefreq
        );
    }

    private function getPois()
    {
        try {
            // Читаем из правильного пути storage/app/poi/poi_data.json
            $path = storage_path('app/poi/poi_data.json');
            if (file_exists($path)) {
                $data = json_decode(file_get_contents($path), true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    Log::info('POI data loaded successfully from: ' . $path);
                    return $data;
                }
                Log::error('JSON decode error: ' . json_last_error_msg());
            }

            Log::error('POI data file not found at: ' . $path);
            return [];
        } catch (\Exception $e) {
            Log::error('Error loading POI data: ' . $e->getMessage());
            return [];
        }
    }

    private function getShuttles()
    {
        $path = storage_path('app/shuttles/shipyard_data.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true) ?? [];
        }
        return [];
    }
}
