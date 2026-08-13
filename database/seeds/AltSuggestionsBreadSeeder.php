<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class AltSuggestionsBreadSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $dataType = $this->seedDataType();
        $this->seedDataRows($dataType);
        $this->seedPermissions();
        $this->seedMenuItem();
    }

    /**
     * @return \TCG\Voyager\Models\DataType
     */
    private function seedDataType()
    {
        $dataType = DataType::firstOrNew([
            'slug' => 'alt-suggestions',
        ]);

        $dataType->fill([
            'name' => 'image_alt_suggestions',
            'display_name_singular' => 'Alt-подсказка',
            'display_name_plural' => 'Alt-подсказки',
            'icon' => 'voyager-photo',
            'model_name' => 'App\\Models\\ImageAltSuggestion',
            'policy_name' => null,
            'controller' => '\\App\\Http\\Controllers\\Voyager\\AltSuggestionController',
            'description' => 'Очередь модерации для сгенерированных alt и title изображений.',
            'generate_permissions' => 0,
            'server_side' => 0,
            'details' => [],
        ])->save();

        return $dataType;
    }

    /**
     * @param \TCG\Voyager\Models\DataType $dataType
     * @return void
     */
    private function seedDataRows(DataType $dataType)
    {
        $rows = [
            [
                'field' => 'id',
                'type' => 'hidden',
                'display_name' => 'ID',
                'order' => 0,
                'browse' => 0,
            ],
            [
                'field' => 'image_path',
                'type' => 'image',
                'display_name' => 'Изображение',
                'order' => 1,
            ],
            [
                'field' => 'page_url',
                'type' => 'text',
                'display_name' => 'URL страницы',
                'order' => 2,
            ],
            [
                'field' => 'imageable_type',
                'type' => 'text',
                'display_name' => 'Модель',
                'order' => 3,
            ],
            [
                'field' => 'imageable_id',
                'type' => 'number',
                'display_name' => 'ID модели',
                'order' => 4,
            ],
            [
                'field' => 'field',
                'type' => 'text',
                'display_name' => 'Поле',
                'order' => 5,
            ],
            [
                'field' => 'locale',
                'type' => 'text',
                'display_name' => 'Язык',
                'order' => 6,
            ],
            [
                'field' => 'current_alt',
                'type' => 'text_area',
                'display_name' => 'Текущий alt',
                'order' => 7,
            ],
            [
                'field' => 'current_title',
                'type' => 'text_area',
                'display_name' => 'Текущий title',
                'order' => 8,
            ],
            [
                'field' => 'suggested_alt',
                'type' => 'text_area',
                'display_name' => 'Предложенный alt',
                'order' => 9,
            ],
            [
                'field' => 'suggested_title',
                'type' => 'text_area',
                'display_name' => 'Предложенный title',
                'order' => 10,
            ],
            [
                'field' => 'status',
                'type' => 'select_dropdown',
                'display_name' => 'Статус',
                'order' => 11,
                'details' => [
                    'default' => 'new',
                    'options' => [
                        'new' => 'Новая',
                        'generated' => 'Сгенерирована',
                        'pending' => 'Ожидает проверки',
                        'approved' => 'Одобрена',
                        'applied' => 'Применена',
                        'rejected' => 'Отклонена',
                        'failed' => 'Ошибка',
                    ],
                ],
            ],
            [
                'field' => 'generated_at',
                'type' => 'timestamp',
                'display_name' => 'Сгенерировано',
                'order' => 12,
            ],
            [
                'field' => 'tokens_used',
                'type' => 'number',
                'display_name' => 'Токены',
                'order' => 13,
            ],
            [
                'field' => 'model',
                'type' => 'text',
                'display_name' => 'Модель OpenAI',
                'order' => 14,
            ],
            [
                'field' => 'approved_alt',
                'type' => 'text_area',
                'display_name' => 'Одобренный alt',
                'order' => 15,
                'browse' => 0,
            ],
            [
                'field' => 'approved_title',
                'type' => 'text_area',
                'display_name' => 'Одобренный title',
                'order' => 16,
                'browse' => 0,
            ],
            [
                'field' => 'error',
                'type' => 'text_area',
                'display_name' => 'Ошибка',
                'order' => 17,
                'browse' => 0,
            ],
            [
                'field' => 'prompt_context',
                'type' => 'text_area',
                'display_name' => 'Контекст промпта',
                'order' => 18,
                'browse' => 0,
            ],
            [
                'field' => 'image_hash',
                'type' => 'hidden',
                'display_name' => 'Хеш изображения',
                'order' => 19,
                'browse' => 0,
            ],
            [
                'field' => 'reviewed_at',
                'type' => 'timestamp',
                'display_name' => 'Проверено',
                'order' => 20,
                'browse' => 0,
            ],
            [
                'field' => 'applied_at',
                'type' => 'timestamp',
                'display_name' => 'Применено',
                'order' => 21,
                'browse' => 0,
            ],
            [
                'field' => 'reviewed_by',
                'type' => 'hidden',
                'display_name' => 'Проверил',
                'order' => 22,
                'browse' => 0,
            ],
            [
                'field' => 'created_at',
                'type' => 'timestamp',
                'display_name' => 'Создано',
                'order' => 23,
                'browse' => 0,
            ],
            [
                'field' => 'updated_at',
                'type' => 'timestamp',
                'display_name' => 'Обновлено',
                'order' => 24,
                'browse' => 0,
            ],
        ];

        $safeFields = [];

        foreach ($rows as $row) {
            $safeFields[] = $row['field'];

            $dataRow = DataRow::firstOrNew([
                'data_type_id' => $dataType->id,
                'field' => $row['field'],
            ]);

            $dataRow->fill([
                'type' => $row['type'],
                'display_name' => $row['display_name'],
                'required' => 0,
                'browse' => (int) ($row['browse'] ?? 1),
                'read' => 0,
                'edit' => 0,
                'add' => 0,
                'delete' => 0,
                'details' => $row['details'] ?? (object) [],
                'order' => $row['order'],
            ])->save();
        }

        DataRow::query()
            ->where('data_type_id', $dataType->id)
            ->whereNotIn('field', $safeFields)
            ->update([
                'browse' => 0,
                'read' => 0,
                'edit' => 0,
                'add' => 0,
                'delete' => 0,
            ]);
    }

    /**
     * @return void
     */
    private function seedPermissions()
    {
        $keys = [
            ['key' => 'browse_alt_suggestions', 'table_name' => 'alt_suggestions'],
            ['key' => 'edit_alt_suggestions', 'table_name' => 'alt_suggestions'],
            ['key' => 'browse_image_alt_suggestions', 'table_name' => 'image_alt_suggestions'],
            ['key' => 'edit_image_alt_suggestions', 'table_name' => 'image_alt_suggestions'],
        ];

        $permissionIds = [];

        foreach ($keys as $permission) {
            $permissionIds[] = Permission::firstOrCreate($permission)->id;
        }

        $adminRole = Role::where('name', 'admin')->first();

        if ($adminRole) {
            $adminRole->permissions()->syncWithoutDetaching($permissionIds);
        }
    }

    /**
     * @return void
     */
    private function seedMenuItem()
    {
        $menu = Menu::where('name', 'admin')->firstOrFail();

        $menuItem = MenuItem::firstOrNew([
            'menu_id' => $menu->id,
            'url' => '/admin/alt-suggestions',
        ]);

        $order = $menuItem->exists
            ? $menuItem->order
            : ((int) MenuItem::where('menu_id', $menu->id)->whereNull('parent_id')->max('order') + 1);

        $menuItem->fill([
            'title' => 'Alt-подсказки',
            'url' => '/admin/alt-suggestions',
            'route' => null,
            'target' => '_self',
            'icon_class' => 'voyager-photo',
            'color' => null,
            'parent_id' => null,
            'parameters' => [
                'badge' => [
                    'source' => 'image_alt_suggestions',
                    'status' => 'pending',
                ],
            ],
            'order' => $order,
        ])->save();
    }
}
