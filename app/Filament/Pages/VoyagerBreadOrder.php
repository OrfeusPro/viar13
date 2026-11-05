<?php

namespace App\Filament\Pages;

use App\Filament\Bread\BreadRegistry;
use App\Services\Admin\BreadOrderingService;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Locked;

class VoyagerBreadOrder extends VoyagerBread
{
    protected static ?string $slug = 'bread/{type}/order';
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.voyager-bread-order';

    #[Locked]
    public string $orderSnapshot = '';

    public static function getNavigationItems(): array { return []; }

    public function mount(string $type): void
    {
        parent::mount($type);
        $this->authorizeOrdering();
        $this->orderSnapshot = app(BreadOrderingService::class)->snapshot($this->orderedRecords(), $this->orderConfiguration());
    }

    public function hydrate(): void { parent::hydrate(); $this->authorizeOrdering(); }

    private function authorizeOrdering(): void
    {
        $bread = $this->bread();
        abort_unless($bread && app(BreadRegistry::class)->permitted($bread, 'browse') && app(BreadRegistry::class)->permitted($bread, 'edit'), 403);
        abort_unless($this->orderConfiguration(), 404);
    }

    public function getTitle(): string | Htmlable { return 'Порядок записей: '.parent::getTitle(); }

    public function orderConfiguration(): ?array { return app(BreadOrderingService::class)->configuration($this->bread()); }

    public function orderedRecords()
    {
        $configuration = $this->orderConfiguration();
        abort_unless($configuration, 404);
        $query = app(BreadOrderingService::class)->query($this->bread(), $this->filterType);
        return $query->orderBy($configuration['column'], $configuration['direction'])->orderBy($query->getModel()->getKeyName())->get();
    }

    public function moveRecord(int $id, int $position): void
    {
        $this->authorizeOrdering();
        $result = app(BreadOrderingService::class)->move($this->bread(), $this->filterType, $this->orderSnapshot, $id, $position);
        $this->orderSnapshot = $result['snapshot'];
        $this->resetErrorBag('ordering');
        Notification::make()->success()->title('Порядок сохранён')->send();
    }
}
