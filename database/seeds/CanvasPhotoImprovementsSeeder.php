<?php

use App\Models\CanvasPhotoImprove;
use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Translation;

class CanvasPhotoImprovementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultLocale = config('voyager.multilingual.default', 'en');
        $items = [
            [
                'code' => 'base',
                'price' => 0,
                'sort' => 1,
                'is_default' => 1,
                'translations' => [
                    'ru' => 'Базовый',
                    'en' => 'Basic',
                    'lv' => 'Bāzes',
                    'lt' => 'Pagrindinis',
                    'pl' => 'Podstawowe',
                    'de' => 'Basis',
                    'ee' => 'Põhihind',
                    'et' => 'Põhihind',
                ],
                'hints' => [
                    'ru' => 'Базовый 0 EUR',
                    'en' => 'Basic 0 EUR',
                    'lv' => 'Bāzes 0 EUR',
                    'lt' => 'Pagrindinis 0 EUR',
                    'pl' => 'Podstawowe 0 EUR',
                    'de' => 'Basis 0 EUR',
                    'ee' => 'Põhihind 0 EUR',
                    'et' => 'Põhihind 0 EUR',
                ],
            ],
            [
                'code' => 'standart',
                'price' => 7,
                'sort' => 2,
                'translations' => [
                    'ru' => 'Необходимый',
                    'en' => 'Optimal',
                    'lv' => 'Nepieciešams',
                    'lt' => 'Optimalus',
                    'pl' => 'Wymagane',
                    'de' => 'Benötigt',
                    'ee' => 'Vajalik',
                    'et' => 'Vajalik',
                ],
                'hints' => [
                    'ru' => 'Необходимый 7 EUR',
                    'en' => 'Optimal 7 EUR',
                    'lv' => 'Nepieciešams 7 EUR',
                    'lt' => 'Optimalus 7 EUR',
                    'pl' => 'Wymagane 7 EUR',
                    'de' => 'Benötigt 7 EUR',
                    'ee' => 'Vajalik 7 EUR',
                    'et' => 'Vajalik 7 EUR',
                ],
            ],
            [
                'code' => 'premium',
                'price' => 12,
                'sort' => 3,
                'translations' => [
                    'ru' => 'Премиальный',
                    'en' => 'Premium',
                    'lv' => 'Premium',
                    'lt' => 'Premija',
                    'pl' => 'Premia',
                    'de' => 'Prämie',
                    'ee' => 'Lisatasu',
                    'et' => 'Lisatasu',
                ],
                'hints' => [
                    'ru' => 'Премиальный 12 EUR',
                    'en' => 'Premium 12 EUR',
                    'lv' => 'Premium 12 EUR',
                    'lt' => 'Premija 12 EUR',
                    'pl' => 'Premia 12 EUR',
                    'de' => 'Prämie 12 EUR',
                    'ee' => 'Lisatasu 12 EUR',
                    'et' => 'Lisatasu 12 EUR',
                ],
            ],
        ];

        foreach ($items as $item) {
            $defaultName = $item['translations'][$defaultLocale] ?? $item['translations']['en'] ?? null;
            $defaultHint = $item['hints'][$defaultLocale] ?? $item['hints']['en'] ?? null;

            $record = CanvasPhotoImprove::updateOrCreate(
                ['code' => $item['code']],
                [
                    'price' => $item['price'],
                    'sort' => $item['sort'],
                    'is_active' => 1,
                    'is_default' => !empty($item['is_default']),
                    'name' => $defaultName,
                    'hint' => $defaultHint,
                ]
            );

            foreach ($item['translations'] as $locale => $value) {
                $this->trans($locale, $this->arr(['canvas_photo_improvements', 'name'], $record->id), $value);
            }

            if (!empty($item['hints'])) {
                foreach ($item['hints'] as $locale => $value) {
                    $this->trans($locale, $this->arr(['canvas_photo_improvements', 'hint'], $record->id), $value);
                }
            }
        }
    }

    private function arr($par, $id)
    {
        return [
            'table_name'  => $par[0],
            'column_name' => $par[1],
            'foreign_key' => $id,
        ];
    }

    private function trans($lang, $keys, $value)
    {
        $_t = Translation::firstOrNew(array_merge($keys, [
            'locale' => $lang,
        ]));

        if (!$_t->exists || $_t->value !== $value) {
            $_t->fill(array_merge(
                $keys,
                ['value' => $value]
            ))->save();
        }
    }
}
