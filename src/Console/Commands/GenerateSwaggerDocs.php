<?php

namespace Coupone\DiscountManager\Console\Commands;

use Illuminate\Console\Command;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

class GenerateSwaggerDocs extends Command
{
    protected $signature = 'discount-manager:generate-docs';
    protected $description = 'Generate Swagger documentation for the Discount Manager API';

    public function handle()
    {
        $this->info('Generating Swagger documentation...');

        $openapi = Generator::scan([
            base_path('src/Http/Controllers'),
            base_path('src/Models'),
            base_path('src/DTOs'),
        ]);

        $json = $openapi->toJson();
        $yaml = $openapi->toYaml();

        $docsPath = storage_path('api-docs');
        if (!file_exists($docsPath)) {
            mkdir($docsPath, 0755, true);
        }

        file_put_contents($docsPath . '/api-docs.json', $json);
        file_put_contents($docsPath . '/api-docs.yaml', $yaml);

        $this->info('Documentation generated successfully!');
        $this->info('JSON: ' . $docsPath . '/api-docs.json');
        $this->info('YAML: ' . $docsPath . '/api-docs.yaml');
    }
} 