<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageController extends Controller
{
    public function show(Request $request, string $filename): BinaryFileResponse
    {
        abort_if(basename($filename) !== $filename, 404);

        $candidates = [];
        if ($request->attributes->get('supportsWebp')) {
            $candidates[] = public_path('images/'.pathinfo($filename, PATHINFO_FILENAME).'.webp');
        }
        $candidates[] = public_path('images/'.$filename);

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return response()->file($path);
            }
        }

        abort(404);
    }
}
