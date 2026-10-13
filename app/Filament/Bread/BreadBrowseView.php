<?php

namespace App\Filament\Bread;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use stdClass;

class BreadBrowseView
{
    public function render(stdClass $row, stdClass $type, LengthAwarePaginator $records, ?Model $record): HtmlString
    {
        $details = json_decode($row->details ?: '{}');
        $name = $details->view ?? null;
        $unavailable = fn () => new HtmlString('<span role="status" data-custom-view-unavailable>Шаблон колонки недоступен.</span>');
        if (! $record || ! is_string($name) || strlen($name) > 255
            || ! preg_match('/^(?:[a-zA-Z0-9_-]+::)?[a-zA-Z0-9_-]+(?:\.[a-zA-Z0-9_-]+)*$/D', $name)) { return $unavailable(); }
        $originalFactory = View::getFacadeRoot();
        try {
            if (! View::exists($name)) { return $unavailable(); }
            // A failing nested render flushes Blade's component stacks. Keep the
            // surrounding Filament page on its original factory even on errors.
            $factory = clone $originalFactory;
            $factory->flushState();
            $factory->share('__env', $factory);
            View::swap($factory);
            $viewRow = clone $row;
            $viewRow->details = $details;
            $viewType = clone $type;
            $viewType->details = json_decode($type->details ?? '{}');
            $record = clone $record;
            $browseValue = $record->getAttribute($row->field.'_browse');
            if ($browseValue) { $record->setAttribute($row->field, $browseValue); }
            $content = $record->getAttribute($row->field);
            return new HtmlString(View::make($name, [
                'row' => $viewRow, 'dataType' => $viewType, 'dataTypeContent' => $records,
                'data' => $record, 'content' => $content, 'action' => 'browse', 'view' => 'browse', 'options' => $details,
            ])->render());
        } catch (\Throwable $error) {
            // Template errors can contain record values: log context and class only.
            Log::warning('BREAD custom browse view unavailable', ['view' => $name, 'table' => $type->name, 'field' => $row->field, 'exception' => $error::class]);
            return $unavailable();
        } finally {
            View::swap($originalFactory);
        }
    }
}
