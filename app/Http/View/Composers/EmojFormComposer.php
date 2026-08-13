<?php

namespace App\Http\View\Composers;

use App\Models\AllStyleFormStepItem;
use Illuminate\View\View;

class EmojFormComposer
{
    public function compose(View $view)
    {
        $loc = \App::getLocale();


        $view->with('bot_form_step0_items', AllStyleFormStepItem::withTranslation($loc, false)->where('step', 0)->orderBy('created_at', 'asc')->get())
             ->with('bot_form_step1_items', AllStyleFormStepItem::withTranslation($loc, false)->where('step', 1)->orderBy('created_at', 'asc')->get())
             ->with('bot_form_step2_items', AllStyleFormStepItem::withTranslation($loc, false)->where('step', 2)->orderBy('created_at', 'asc')->get())
             ->with('bot_form_step3_items', AllStyleFormStepItem::withTranslation($loc, false)->where('step', 3)->orderBy('created_at', 'asc')->get())
             ->with('bot_form_step0_default', AllStyleFormStepItem::where('name', '0_default')->where('step', 4)->orderBy('created_at', 'asc')->first())
             ->with('bot_form_step1_default', AllStyleFormStepItem::where('name', '1_default')->where('step', 4)->orderBy('created_at', 'asc')->first())
             ->with('bot_form_step2_default', AllStyleFormStepItem::where('name', '2_default')->where('step', 4)->orderBy('created_at', 'asc')->first())
             ->with('bot_form_step3_default', AllStyleFormStepItem::where('name', '3_default')->where('step', 4)->orderBy('created_at', 'asc')->first());
    }
}
