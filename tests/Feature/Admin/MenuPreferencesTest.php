<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\MenuPreferences;
use App\Models\User;
use App\Services\Admin\MenuPreferences as Preferences;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationItem;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class MenuPreferencesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->text('settings')->nullable();
            $table->unsignedInteger('role_id')->nullable();
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
        });
        Schema::create('permission_role', function (Blueprint $table): void {
            $table->integer('role_id');
            $table->integer('permission_id');
        });
        Schema::create('user_roles', function (Blueprint $table): void {
            $table->integer('user_id');
            $table->integer('role_id');
        });
        DB::table('roles')->insert(['id' => 1]);
        DB::table('permissions')->insert(['id' => 1, 'key' => 'browse_admin']);
        DB::table('permission_role')->insert(['role_id' => 1, 'permission_id' => 1]);
        DB::table('users')->insert([
            ['id' => 1, 'role_id' => 1, 'settings' => '{"locale":"ru","another":{"keep":true}}'],
            ['id' => 2, 'role_id' => 1, 'settings' => '{"locale":"uk"}'],
            ['id' => 3, 'role_id' => null, 'settings' => null],
        ]);
        $panel = Filament::getPanel('admin');
        Filament::setCurrentPanel($panel);
        $panel->navigation(fn (NavigationBuilder $builder) => $builder->items([
            NavigationItem::make('Заказы')->key('orders')->url('/filament/orders')->icon('heroicon-o-shopping-bag'),
            NavigationItem::make('Клиенты')->key('clients')->url('/filament/clients')->icon('heroicon-o-users'),
            NavigationItem::make('Без доступа')->key('private')->url('/filament/private')->visible(false),
        ]));
        $this->actingAs(User::findOrFail(1), 'filament');
    }

    public function test_selection_survives_reload_and_show_all_preserves_it_for_current_user_only(): void
    {
        $orders = 'extra-'.md5('orders');
        $clients = 'extra-'.md5('clients');
        $component = Livewire::test(MenuPreferences::class)
            ->assertSet('showAll', true)->assertSet('selected', [$orders, $clients])
            ->set('selected', [$orders])->call('save')->assertHasNoErrors()
            ->assertSet('showAll', false)->assertDispatched('close-modal');
        $this->assertSame(['show_all' => false, 'hidden' => [$clients]], app(Preferences::class)->read(User::findOrFail(1)));
        $settings = User::findOrFail(1)->settings;
        $this->assertSame('ru', $settings['locale']);
        $this->assertSame(['keep' => true], $settings['another']);
        $this->assertSame(['locale' => 'uk'], User::findOrFail(2)->settings);
        Livewire::test(MenuPreferences::class)->assertSet('selected', [$orders])->assertSet('showAll', false)
            ->assertViewHas('tree', fn ($tree) => array_column($tree, 'id') === [$orders]);
        $component->set('showAll', true)->assertViewHas('tree', fn ($tree) => count($tree) === 2);
        $this->assertSame([$clients], app(Preferences::class)->read(User::findOrFail(1))['hidden']);
        $component->set('showAll', false)->assertViewHas('tree', fn ($tree) => count($tree) === 1);
        $this->actingAs(User::findOrFail(2), 'filament');
        Livewire::test(MenuPreferences::class)->assertSet('showAll', true)->assertSet('selected', [$orders, $clients]);
    }

    public function test_empty_selection_reset_and_reopening_discard_unsaved_changes(): void
    {
        $component = Livewire::test(MenuPreferences::class)->set('selected', [])->call('save')
            ->assertViewHas('tree', [])->assertSee('Все пункты скрыты.');
        $component->call('resetSelection')->assertSet('selected', ['extra-'.md5('orders'), 'extra-'.md5('clients')]);
        $component->call('openSettings')->assertSet('selected', []);
        $component->call('resetSelection')->call('save')->assertHasNoErrors()
            ->assertViewHas('tree', fn ($tree) => count($tree) === 2);
    }

    public function test_forged_or_unauthorized_ids_are_rejected_without_writes(): void
    {
        Livewire::test(MenuPreferences::class)->set('selected', ['extra-'.md5('private')])->call('save')->assertHasErrors('selected.0');
        $this->assertArrayNotHasKey('filament_menu', User::findOrFail(1)->settings);
        $this->actingAs(User::findOrFail(3), 'filament');
        Livewire::test(MenuPreferences::class)->assertForbidden();
        auth('filament')->logout();
        Livewire::test(MenuPreferences::class)->assertForbidden();
    }

    public function test_save_button_calls_livewire_action_and_access_is_rechecked_before_saving(): void
    {
        $component = Livewire::test(MenuPreferences::class);
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$component->html());
        $buttons = (new \DOMXPath($dom))->query('//button[@*[name()="wire:click" and .="save"]]');
        $this->assertCount(1, $buttons);
        $this->assertSame('button', $buttons->item(0)->getAttribute('type'));

        DB::table('permission_role')->delete();
        $component->call('save')->assertForbidden();
        $this->assertArrayNotHasKey('filament_menu', User::findOrFail(1)->settings);
    }

    public function test_saving_mode_merges_with_fresh_settings_instead_of_stale_user_attributes(): void
    {
        $user = User::findOrFail(1);
        DB::table('users')->where('id', 1)->update(['settings' => '{"locale":"en","new_key":42,"filament_menu":{"hidden":["old"]}}']);
        app(Preferences::class)->save($user, ['show_all' => true]);
        $this->assertSame(['locale' => 'en', 'new_key' => 42, 'filament_menu' => ['hidden' => ['old'], 'show_all' => true]], $user->fresh()->settings);
    }

    public function test_filter_removes_empty_nested_groups_and_keeps_order_of_remaining_items(): void
    {
        $leaf = fn ($id) => ['id' => $id, 'label' => $id, 'children' => []];
        $tree = [['id' => 'group', 'label' => 'Группа', 'children' => [
            ['id' => 'nested', 'label' => 'Подгруппа', 'children' => [$leaf('one')]], $leaf('two'),
        ]], $leaf('three')];
        $this->assertSame(['two'], array_column(Preferences::filter($tree, ['one'])[0]['children'], 'id'));
        $this->assertSame(['three'], array_column(Preferences::filter($tree, ['one', 'two']), 'id'));
        $this->assertSame(['one' => 'Подгруппа / one', 'two' => 'two'], Preferences::groups($tree)[0]['items']);
    }
}
