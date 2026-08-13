<?php

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\DataRow;
use TCG\Voyager\Models\DataType;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

class SeoMetaSuggestionsBreadSeeder extends Seeder
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

    private function seedDataType(): DataType
    {
        $dataType = DataType::firstOrNew([
            'slug' => 'seo-meta-suggestions',
        ]);

        $dataType->fill([
            'name' => 'seo_meta_suggestions',
            'display_name_singular' => 'SEO Meta-подсказка',
            'display_name_plural' => 'SEO Meta-подсказки',
            'icon' => 'voyager-search',
            'model_name' => 'App\\Models\\SeoMetaSuggestion',
            'policy_name' => null,
            'controller' => '\\App\\Http\\Controllers\\Voyager\\SeoMetaSuggestionController',
            'description' => 'Очередь модерации для сгенерированных Meta Title и Meta Description.',
            'generate_permissions' => 0,
            'server_side' => 0,
            'details' => [],
        ])->save();

        return $dataType;
    }

    private function seedDataRows(DataType $dataType): void
    {
        $rows = [
            ['field' => 'id', 'type' => 'hidden', 'display_name' => 'ID', 'order' => 0, 'browse' => 0],
            ['field' => 'metaable_type', 'type' => 'text', 'display_name' => 'Модель', 'order' => 1],
            ['field' => 'metaable_id', 'type' => 'number', 'display_name' => 'ID модели', 'order' => 2],
            ['field' => 'locale', 'type' => 'text', 'display_name' => 'Язык', 'order' => 3],
            ['field' => 'page_url', 'type' => 'text', 'display_name' => 'URL страницы', 'order' => 4],
            ['field' => 'entity_label', 'type' => 'text', 'display_name' => 'Тип сущности', 'order' => 5],
            ['field' => 'entity_title', 'type' => 'text', 'display_name' => 'Название сущности', 'order' => 6],
            ['field' => 'seo_keywords', 'type' => 'text_area', 'display_name' => 'Ключевые слова', 'order' => 7, 'browse' => 0],
            ['field' => 'title_field', 'type' => 'text', 'display_name' => 'Поле Meta Title', 'order' => 8, 'browse' => 0],
            ['field' => 'description_field', 'type' => 'text', 'display_name' => 'Поле Meta Description', 'order' => 9, 'browse' => 0],
            ['field' => 'current_meta_title', 'type' => 'text_area', 'display_name' => 'Текущий Meta Title', 'order' => 10],
            ['field' => 'current_meta_description', 'type' => 'text_area', 'display_name' => 'Текущий Meta Description', 'order' => 11],
            ['field' => 'suggested_meta_title', 'type' => 'text_area', 'display_name' => 'Предложенный Meta Title', 'order' => 12],
            ['field' => 'suggested_meta_description', 'type' => 'text_area', 'display_name' => 'Предложенный Meta Description', 'order' => 13],
            ['field' => 'approved_meta_title', 'type' => 'text_area', 'display_name' => 'Одобренный Meta Title', 'order' => 14, 'browse' => 0],
            ['field' => 'approved_meta_description', 'type' => 'text_area', 'display_name' => 'Одобренный Meta Description', 'order' => 15, 'browse' => 0],
            [
                'field' => 'status',
                'type' => 'select_dropdown',
                'display_name' => 'Статус',
                'order' => 16,
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
            ['field' => 'prompt_context', 'type' => 'text_area', 'display_name' => 'Контекст промпта', 'order' => 17, 'browse' => 0],
            ['field' => 'model', 'type' => 'text', 'display_name' => 'Модель OpenAI', 'order' => 18],
            ['field' => 'tokens_used', 'type' => 'number', 'display_name' => 'Токены', 'order' => 19, 'browse' => 0],
            ['field' => 'error', 'type' => 'text_area', 'display_name' => 'Ошибка', 'order' => 20, 'browse' => 0],
            ['field' => 'generated_at', 'type' => 'timestamp', 'display_name' => 'Сгенерировано', 'order' => 21],
            ['field' => 'reviewed_at', 'type' => 'timestamp', 'display_name' => 'Проверено', 'order' => 22, 'browse' => 0],
            ['field' => 'reviewed_by', 'type' => 'hidden', 'display_name' => 'Проверил', 'order' => 23, 'browse' => 0],
            ['field' => 'applied_at', 'type' => 'timestamp', 'display_name' => 'Применено', 'order' => 24, 'browse' => 0],
            ['field' => 'applied_by', 'type' => 'hidden', 'display_name' => 'Применил', 'order' => 25, 'browse' => 0],
            ['field' => 'created_at', 'type' => 'timestamp', 'display_name' => 'Создано', 'order' => 26, 'browse' => 0],
            ['field' => 'updated_at', 'type' => 'timestamp', 'display_name' => 'Обновлено', 'order' => 27, 'browse' => 0],
        ];

        foreach ($rows as $row) {
            $field = (string) $row['field'];
            $dataRow = DataRow::firstOrNew([
                'data_type_id' => $dataType->id,
                'field' => $field,
            ]);

            $dataRow->fill([
                'type' => $row['type'],
                'display_name' => $row['display_name'],
                'required' => 0,
                'browse' => array_key_exists('browse', $row) ? (int) $row['browse'] : 1,
                'read' => 1,
                'edit' => 1,
                'add' => 1,
                'delete' => 0,
                'details' => $row['details'] ?? [],
                'order' => $row['order'],
            ])->save();
        }
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'browse_seo_meta_suggestions',
            'read_seo_meta_suggestions',
            'edit_seo_meta_suggestions',
            'add_seo_meta_suggestions',
            'delete_seo_meta_suggestions',
        ];

        foreach ($permissions as $key) {
            Permission::firstOrCreate([
                'key' => $key,
                'table_name' => 'seo_meta_suggestions',
            ]);
        }

        $role = Role::where('name', 'admin')->first();

        if ($role) {
            $role->permissions()->syncWithoutDetaching(
                Permission::whereIn('key', $permissions)->pluck('id')->all()
            );
        }
    }

    private function seedMenuItem(): void
    {
        $menu = Menu::where('name', 'admin')->first();

        if (!$menu) {
            return;
        }

        $item = MenuItem::firstOrNew([
            'menu_id' => $menu->id,
            'url' => '/admin/seo-meta-suggestions',
        ]);

        $item->fill([
            'title' => 'SEO Meta',
            'target' => '_self',
            'icon_class' => 'voyager-search',
            'color' => null,
            'parent_id' => null,
            'order' => $this->nextMenuOrder($menu),
            'route' => null,
            'parameters' => null,
        ])->save();
    }

    private function nextMenuOrder(Menu $menu): int
    {
        return ((int) MenuItem::where('menu_id', $menu->id)->max('order')) + 1;
    }
}
