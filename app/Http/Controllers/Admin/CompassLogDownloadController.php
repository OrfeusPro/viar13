<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Pages\Compass;
use App\Http\Controllers\Controller;
use App\Services\Admin\CompassLogReader;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CompassLogDownloadController extends Controller
{
    public function __invoke(Request $request, CompassLogReader $reader): BinaryFileResponse
    {
        abort_unless(Compass::canAccess(), 403);
        $values = $request->validate(['file' => ['required', 'string', 'max:255']]);
        return response()->download($reader->checkedPath($values['file']), $values['file'], [
            'Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
