<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ShipyardController;

class ConvertYmlToJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yml:convert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert YAML files in Shipyard to JSON';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controller = new ShipyardController();
        $response = $controller->convertYmlToJson();

        // Выводим результат в консоль
        $this->info('YAML files converted to JSON successfully:');
        $this->line($response->getContent());
    }
}
