<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\AdminChats;
use App\Models\OrderPainterComment;
use App\Models\Orders;
use App\Models\OrderUserComments;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrdersDisplayParityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->json('settings')->nullable();
            $table->string('pdf_locale')->nullable();
            $table->integer('client_status')->nullable();
        });
        Schema::create('user_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('a_order_from', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
        });
        DB::table('user_types')->insert([
            ['id' => 1, 'name' => 'Новый'], ['id' => 2, 'name' => 'Бывалый'],
        ]);
        view()->addNamespace('admin-tests', base_path('tests/Fixtures/views'));
    }

    public function test_visible_number_column_is_searchable_without_hidden_columns_and_respects_scope(): void
    {
        DB::table('orders')->insert([
            ['id' => 18380, 'status' => 'watching'],
            ['id' => 18381, 'status' => 'watching'],
            ['id' => 28380, 'status' => 'completed'],
        ]);
        Livewire::test(OrderNumberSearchTable::class)
            ->searchTable('18380')->assertCountTableRecords(1)
            ->assertCanSeeTableRecords([Orders::findOrFail(18380)])
            ->assertCanNotSeeTableRecords([Orders::findOrFail(18381)])
            ->searchTable('8380')->assertCountTableRecords(1)
            ->searchTable('28380')->assertCountTableRecords(0)
            ->searchTable('no-such-number')->assertCountTableRecords(0)
            ->searchTable('')->assertCountTableRecords(2);
    }

    public function test_visible_number_column_sorts_by_order_id_like_voyager(): void
    {
        DB::table('orders')->insert([
            ['id' => 18380, 'status' => 'watching'],
            ['id' => 18382, 'status' => 'watching'],
            ['id' => 18381, 'status' => 'watching'],
        ]);

        Livewire::test(OrderNumberSearchTable::class)
            ->sortTable('number_controls', 'asc')
            ->assertCanSeeTableRecords(Orders::query()->orderBy('id')->get(), inOrder: true)
            ->sortTable('number_controls', 'desc')
            ->assertCanSeeTableRecords(Orders::query()->orderByDesc('id')->get(), inOrder: true);
    }

    public function test_production_columns_remain_in_the_same_eight_column_order(): void
    {
        $component = Livewire::test(OrderNumberSearchTable::class)->instance();
        $table = OrdersTable::configure(Table::make($component));
        $names = collect($table->getColumns())
            ->filter(fn ($column): bool => ! $column->isHidden() && ! $column->isToggledHiddenByDefault())
            ->map(fn ($column): string => $column->getLabel())->values()->all();
        $this->assertSame(['Номер', 'Оплата', 'Пользователь', 'Получатель', 'Товар', 'Комментарии', 'Художник', 'Заказ'], $names);
    }

    public function test_orders_row_is_top_aligned_without_affecting_other_filament_tables(): void
    {
        $css = file_get_contents(public_path('css/filament-order-chats.css'));

        $this->assertStringContainsString(
            '.fi-ta-table tbody:has(.adm-fil-order-lifecycle) > tr > td { vertical-align:top; }',
            $css,
        );
        $this->assertStringNotContainsString('.fi-ta-table tbody > tr > td { vertical-align:top; }', $css);
    }

    public function test_recipient_payment_and_delivery_indicators_keep_legacy_vertical_size(): void
    {
        $order = (new Orders)->forceFill([
            'id' => 18451,
            'payment' => 'paypalOnetimePayment',
            'delivery' => json_encode([
                'email' => 'client@example.test', 'first_name' => 'Client',
                'last_name' => 'Test', 'phone' => '+37120000000', 'country' => 'LV',
                'address' => 'Riga', 'postal_index' => '-', 'sposob' => 'pickup_at_viar_workshop',
            ]),
        ]);
        $order->setRelation('user', null);

        $html = view('filament.tables.columns.order-recipient', ['getRecord' => fn () => $order])->render();
        $this->assertStringContainsString('class="adm-fil-recipient-indicators"', $html);
        $this->assertStringContainsString('alt="PayPal"', $html);
        $this->assertStringContainsString('alt="Забрать в мастерской VIAR"', $html);
        $this->assertSame(2, substr_count($html, 'display: block; max-width: 60px; height: auto;'));
        $this->assertStringNotContainsString('max-height: 38px', $html);
    }

    public function test_comments_column_keeps_the_complete_voyager_previews(): void
    {
        $order = (new Orders)->forceFill([
            'id' => 18451,
            'client_messages_count' => 5,
            'unread_client_messages_count' => 0,
            'admin_messages_count' => 5,
            'painter_messages_count' => 0,
            'unread_painter_messages_count' => 0,
        ]);
        $messages = fn (string $prefix, string $class) => collect(range(1, 5))
            ->map(fn (int $number) => (new $class)->forceFill([
                'comment' => $prefix.' '.$number,
                'created_at' => Carbon::parse("2026-09-01 10:0{$number}:00"),
            ]));

        $adminMessages = $messages('ADMIN PREVIEW', AdminChats::class);
        $adminMessages->each(fn (AdminChats $message) => $message->setRelation('user', null));
        $order->setRelation('order_user_comments', $messages('CLIENT PREVIEW', OrderUserComments::class));
        $order->setRelation('order_painter_comments', $messages('PAINTER PREVIEW', OrderPainterComment::class));
        $order->setRelation('adminChats', $adminMessages);
        $order->setRelation('saConversations', collect());

        $html = view('filament.tables.columns.order-comments', ['getRecord' => fn () => $order])->render();

        foreach (['CLIENT', 'PAINTER', 'ADMIN'] as $prefix) {
            $this->assertStringContainsString("{$prefix} PREVIEW 1", $html);
            $this->assertStringContainsString("{$prefix} PREVIEW 5", $html);
        }
    }

    #[DataProvider('locales')]
    public function test_voyager_locale_is_read_from_settings_without_a_locale_column(mixed $settings, ?string $expected): void
    {
        $id = DB::table('users')->insertGetId(['settings' => $settings === null ? null : json_encode($settings)]);
        $user = User::findOrFail($id);
        $original = $user->getAttributes();
        $this->assertSame($expected, $user->locale);
        $this->assertSame($expected, $user->preferredLocale());
        $this->assertSame($original, $user->getAttributes());
    }

    public static function locales(): array
    {
        return [
            'Russian' => [['locale' => 'ru'], 'ru'],
            'Latvian' => [['locale' => 'lv'], 'lv'],
            'unset' => [null, null],
            'no locale' => [['other' => 'keep'], null],
            'malformed locale' => [['locale' => ['ru']], null],
        ];
    }

    public function test_null_pdf_locale_and_client_status_match_legacy_without_writing_defaults(): void
    {
        [$user, $html] = $this->renderUser(['settings' => json_encode(['locale' => 'ru'])]);
        $this->assertStringContainsString('Язык пользователя: ru', $html);
        $this->assertSame('ru', $this->selected($html, 'Язык счета пользователя заказа №18380'));
        $this->assertSame('1', $this->selected($html, 'Статус клиента заказа №18380'));
        $this->assertNull($user->fresh()->pdf_locale);
        $this->assertNull($user->fresh()->client_status);
    }

    public function test_explicit_pdf_locale_and_client_status_override_display_defaults(): void
    {
        [, $html] = $this->renderUser([
            'settings' => json_encode(['locale' => 'ru']), 'pdf_locale' => 'pl', 'client_status' => 2,
        ]);
        $this->assertStringContainsString('Язык пользователя: ru', $html);
        $this->assertSame('pl', $this->selected($html, 'Язык счета пользователя заказа №18380'));
        $this->assertSame('2', $this->selected($html, 'Статус клиента заказа №18380'));
    }

    private function renderUser(array $attributes): array
    {
        $id = DB::table('users')->insertGetId($attributes);
        $user = User::findOrFail($id);
        $user->setAttribute('orders_count', 1);
        $order = (new Orders)->forceFill(['id' => 18380, 'user_id' => $id]);
        $order->setRelation('user', $user)->setRelation('manager', null);

        return [$user, view('filament.tables.columns.order-user', ['getRecord' => fn () => $order])->render()];
    }

    private function selected(string $html, string $label): string
    {
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$html);
            $xpath = new \DOMXPath($document);
            $options = $xpath->query('//select[@aria-label="'.$label.'"]/option[@selected]');
            $this->assertCount(1, $options);

            return $options->item(0)->getAttribute('value');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }
}

class OrderNumberSearchTable extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;

    public function table(Table $table): Table
    {
        // Reuse the production column's search configuration; isolate unrelated relation queries and controls.
        $configured = OrdersTable::configure(Table::make($this));
        $number = $configured->getColumn('number_controls')->view('admin-tests::order-id-column');

        return $table->query(Orders::query()->where('status', 'watching'))->columns([$number]);
    }

    public function render()
    {
        return view('admin-tests::client-chat-table');
    }
}
