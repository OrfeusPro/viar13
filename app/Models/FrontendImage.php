<?php

namespace App\Models;

use App\Models\Concerns\HasAltSuggestions;
use App\Services\AltGeneration\FrontendImageRegistry;
use Illuminate\Database\Eloquent\Model;

/** One image file shared by its frontend template placements. */
class FrontendImage extends Model
{
    use HasAltSuggestions;

    public $incrementing = false;
    protected $guarded = [];
    protected $casts = ['context' => 'array'];

    public static function identity(string $placement, string $path): int
    {
        // 52 bits are exact in PHP integers, JavaScript numbers and unsigned BIGINT.
        // Keep the placement argument for existing Blade calls; it is context, not identity.
        return (int) hexdec(substr(hash('sha256', 'frontend-file' . "\0" . $path), 0, 13));
    }

    public static function attributesFor(string $placement, ?string $path, ?string $alt = null, ?string $title = null): string
    {
        return app(FrontendImageRegistry::class)->attributesFor($placement, $path, $alt, $title);
    }
}
