<?php

namespace Coupone\DiscountManager\Http\Controllers;

use Illuminate\Http\Response;

class SwaggerController extends ApiController
{
    public function index()
    {
        $view = view('l5-swagger::index');
        return new Response($view);
    }

    public function json()
    {
        $path = storage_path('api-docs/api-docs.json');
        if (!file_exists($path)) {
            return response()->json(['error' => 'Documentation not found'], 404);
        }
        return response()->json(json_decode(file_get_contents($path)));
    }

    public function yaml()
    {
        $path = storage_path('api-docs/api-docs.yaml');
        if (!file_exists($path)) {
            return response()->json(['error' => 'Documentation not found'], 404);
        }
        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/yaml',
        ]);
    }
} 