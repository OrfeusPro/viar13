<?php

namespace App\Jobs;

use App\Models\SeoMetaSuggestion;
use App\Services\SeoMetaGeneration\SeoMetaModerationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;

class GenerateSeoMetaSuggestion implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 180;
    public int $uniqueFor = 3600;

    public function __construct(public int $suggestionId) {}

    public function uniqueId(): string
    {
        return (string) $this->suggestionId;
    }

    public function handle(SeoMetaModerationService $service): void
    {
        $suggestion = SeoMetaSuggestion::find($this->suggestionId);
        if ($suggestion) {
            $service->generate($suggestion);
        }
    }

    public function failed(?\Throwable $exception): void
    {
        SeoMetaSuggestion::whereKey($this->suggestionId)->update([
            'status' => 'failed', 'error' => 'Генерация не завершилась. Проверьте очередь и повторите попытку.',
        ]);
    }
}
