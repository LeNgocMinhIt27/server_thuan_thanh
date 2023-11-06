<?php

namespace App\Http\Controllers\Api\Translation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Facades\ResponseCache;
use Illuminate\Support\Facades\File;

class TranslationController extends Controller
{
    public function index($language, $file)
    {
        $path = resource_path("lang/{$language}/{$file}.php");

        if (File::exists($path)) {
            return response()->json(require $path);
        }

        return response()->json([]);
    }
}
