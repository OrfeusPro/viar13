<?php

namespace App\Models\Concerns;

use App\Models\ImageAltSuggestion;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Adds accessors for image alt/title suggestion moderation.
 */
trait HasAltSuggestions
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function altSuggestions(): MorphMany
    {
        return $this->morphMany(ImageAltSuggestion::class, 'imageable');
    }

    /**
     * @return int
     */
    public function pendingAltSuggestionsCount(): int
    {
        return (int) $this->altSuggestions()->where('status', 'pending')->count();
    }
}
