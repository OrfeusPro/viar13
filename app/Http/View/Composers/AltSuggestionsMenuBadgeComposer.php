<?php

namespace App\Http\View\Composers;

use App\Models\ImageAltSuggestion;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

/**
 * Shares the pending alt suggestion count with the Voyager sidebar.
 */
class AltSuggestionsMenuBadgeComposer
{
    /**
     * @param \Illuminate\View\View $view
     * @return void
     */
    public function compose(View $view): void
    {
        $count = 0;

        try {
            if (Schema::hasTable('image_alt_suggestions')) {
                $count = ImageAltSuggestion::query()
                    ->where('status', 'pending')
                    ->count();
            }
        } catch (Throwable $exception) {
            $count = 0;
        }

        $view->with('altSuggestionsPendingCount', $count);
    }
}
