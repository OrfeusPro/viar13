<?php

namespace App\Services\Invoice;

use InvalidArgumentException;

class InvoiceAmountInWords
{
    private const MAX_CENTS = 99999999999;

    public function formatCents($amountInCents, $locale)
    {
        $amountInCents = (int) $amountInCents;

        if ($amountInCents < 0 || $amountInCents > self::MAX_CENTS) {
            throw new InvalidArgumentException('Invoice amount must be between 0.00 and 999999999.99 EUR.');
        }

        $locale = $this->normalizeLocale($locale);
        $euros = intdiv($amountInCents, 100);
        $cents = $amountInCents % 100;
        $euroWords = $this->integerToWords($euros, $locale);
        $centWords = $this->integerToWords($cents, $locale);

        if ($locale === 'de') {
            $euroWords = $euros === 1 ? 'ein' : $euroWords;
            $centWords = $cents === 1 ? 'ein' : $centWords;
        }

        return $this->uppercaseFirst($euroWords)
            . ' ' . $this->euroForm($euros, $locale)
            . ' ' . $this->andWord($locale)
            . ' ' . $centWords
            . ' ' . $this->centForm($cents, $locale);
    }

    private function normalizeLocale($locale)
    {
        $locale = strtolower(str_replace('-', '_', (string) $locale));
        $locale = explode('_', $locale)[0];

        if ($locale === 'ee') {
            return 'et';
        }

        return in_array($locale, ['lv', 'lt', 'pl', 'ru', 'de', 'en', 'et'], true) ? $locale : 'en';
    }

    private function integerToWords($number, $locale)
    {
        if ($locale === 'ru' || $locale === 'pl') {
            return $this->slavicNumber($number, $locale);
        }

        if ($locale === 'de') {
            return $this->germanNumber($number);
        }

        if ($locale === 'en') {
            return $this->englishNumber($number);
        }

        return $this->balticNumber($number, $locale);
    }

    private function englishNumber($number)
    {
        $ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

        if ($number === 0) {
            return 'zero';
        }
        if ($number < 20) {
            return $ones[$number];
        }
        if ($number < 100) {
            return $tens[intdiv($number, 10)] . ($number % 10 ? '-' . $ones[$number % 10] : '');
        }
        if ($number < 1000) {
            return $ones[intdiv($number, 100)] . ' hundred' . ($number % 100 ? ' ' . $this->englishNumber($number % 100) : '');
        }

        foreach ([[1000000000, 'billion'], [1000000, 'million'], [1000, 'thousand']] as $scale) {
            if ($number >= $scale[0]) {
                return $this->englishNumber(intdiv($number, $scale[0])) . ' ' . $scale[1]
                    . ($number % $scale[0] ? ' ' . $this->englishNumber($number % $scale[0]) : '');
            }
        }

        return '';
    }

    private function germanNumber($number)
    {
        $ones = ['', 'eins', 'zwei', 'drei', 'vier', 'fünf', 'sechs', 'sieben', 'acht', 'neun', 'zehn', 'elf', 'zwölf', 'dreizehn', 'vierzehn', 'fünfzehn', 'sechzehn', 'siebzehn', 'achtzehn', 'neunzehn'];
        $tens = ['', '', 'zwanzig', 'dreißig', 'vierzig', 'fünfzig', 'sechzig', 'siebzig', 'achtzig', 'neunzig'];

        if ($number === 0) {
            return 'null';
        }
        if ($number < 20) {
            return $ones[$number];
        }
        if ($number < 100) {
            $unit = $number % 10;

            return ($unit ? ($unit === 1 ? 'ein' : $ones[$unit]) . 'und' : '') . $tens[intdiv($number, 10)];
        }
        if ($number < 1000) {
            $hundreds = intdiv($number, 100);

            return ($hundreds === 1 ? 'ein' : $ones[$hundreds]) . 'hundert'
                . ($number % 100 ? $this->germanNumber($number % 100) : '');
        }
        if ($number < 1000000) {
            $thousands = intdiv($number, 1000);

            return ($thousands === 1 ? 'ein' : $this->germanNumber($thousands)) . 'tausend'
                . ($number % 1000 ? $this->germanNumber($number % 1000) : '');
        }

        foreach ([[1000000000, 'Milliarde', 'Milliarden'], [1000000, 'Million', 'Millionen']] as $scale) {
            if ($number >= $scale[0]) {
                $count = intdiv($number, $scale[0]);

                return ($count === 1 ? 'eine' : $this->germanNumber($count)) . ' '
                    . ($count === 1 ? $scale[1] : $scale[2])
                    . ($number % $scale[0] ? ' ' . $this->germanNumber($number % $scale[0]) : '');
            }
        }

        return '';
    }

    private function slavicNumber($number, $locale)
    {
        if ($number === 0) {
            return $locale === 'ru' ? 'ноль' : 'zero';
        }

        $groups = [];
        $scales = $locale === 'ru'
            ? [[1000000000, ['миллиард', 'миллиарда', 'миллиардов'], false], [1000000, ['миллион', 'миллиона', 'миллионов'], false], [1000, ['тысяча', 'тысячи', 'тысяч'], true]]
            : [[1000000000, ['miliard', 'miliardy', 'miliardów'], false], [1000000, ['milion', 'miliony', 'milionów'], false], [1000, ['tysiąc', 'tysiące', 'tysięcy'], false]];

        foreach ($scales as $scale) {
            if ($number >= $scale[0]) {
                $count = intdiv($number, $scale[0]);
                $groups[] = $this->slavicUnderThousand($count, $locale, $scale[2]) . ' ' . $this->slavicForm($count, $scale[1]);
                $number %= $scale[0];
            }
        }

        if ($number > 0) {
            $groups[] = $this->slavicUnderThousand($number, $locale, false);
        }

        return implode(' ', $groups);
    }

    private function slavicUnderThousand($number, $locale, $feminine)
    {
        $data = $locale === 'ru'
            ? [
                'hundreds' => ['', 'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот', 'девятьсот'],
                'tens' => ['', '', 'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто'],
                'teens' => ['десять', 'одиннадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятнадцать', 'шестнадцать', 'семнадцать', 'восемнадцать', 'девятнадцать'],
                'ones' => ['', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
                'feminine' => ['', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять'],
            ]
            : [
                'hundreds' => ['', 'sto', 'dwieście', 'trzysta', 'czterysta', 'pięćset', 'sześćset', 'siedemset', 'osiemset', 'dziewięćset'],
                'tens' => ['', '', 'dwadzieścia', 'trzydzieści', 'czterdzieści', 'pięćdziesiąt', 'sześćdziesiąt', 'siedemdziesiąt', 'osiemdziesiąt', 'dziewięćdziesiąt'],
                'teens' => ['dziesięć', 'jedenaście', 'dwanaście', 'trzynaście', 'czternaście', 'piętnaście', 'szesnaście', 'siedemnaście', 'osiemnaście', 'dziewiętnaście'],
                'ones' => ['', 'jeden', 'dwa', 'trzy', 'cztery', 'pięć', 'sześć', 'siedem', 'osiem', 'dziewięć'],
                'feminine' => ['', 'jedna', 'dwie', 'trzy', 'cztery', 'pięć', 'sześć', 'siedem', 'osiem', 'dziewięć'],
            ];

        $parts = [];
        if ($number >= 100) {
            $parts[] = $data['hundreds'][intdiv($number, 100)];
            $number %= 100;
        }
        if ($number >= 10 && $number < 20) {
            $parts[] = $data['teens'][$number - 10];

            return implode(' ', $parts);
        }
        if ($number >= 20) {
            $parts[] = $data['tens'][intdiv($number, 10)];
            $number %= 10;
        }
        if ($number > 0) {
            $parts[] = $data[$feminine ? 'feminine' : 'ones'][$number];
        }

        return implode(' ', $parts);
    }

    private function slavicForm($number, array $forms)
    {
        $lastTwo = $number % 100;
        $last = $number % 10;

        if ($lastTwo >= 11 && $lastTwo <= 14) {
            return $forms[2];
        }
        if ($last === 1) {
            return $forms[0];
        }
        if ($last >= 2 && $last <= 4) {
            return $forms[1];
        }

        return $forms[2];
    }

    private function balticNumber($number, $locale)
    {
        if ($number === 0) {
            return ['lv' => 'nulle', 'lt' => 'nulis', 'et' => 'null'][$locale];
        }

        $groups = [];
        $scales = [
            1000000000 => ['lv' => ['miljards', 'miljardi'], 'lt' => ['milijardas', 'milijardai', 'milijardų'], 'et' => ['miljard']],
            1000000 => ['lv' => ['miljons', 'miljoni'], 'lt' => ['milijonas', 'milijonai', 'milijonų'], 'et' => ['miljon']],
            1000 => ['lv' => ['tūkstotis', 'tūkstoši'], 'lt' => ['tūkstantis', 'tūkstančiai', 'tūkstančių'], 'et' => ['tuhat']],
        ];

        foreach ($scales as $scale => $names) {
            if ($number < $scale) {
                continue;
            }

            $count = intdiv($number, $scale);
            $name = $this->balticScaleForm($count, $names[$locale], $locale);
            $groups[] = $this->balticNumber($count, $locale) . ' ' . $name;
            $number %= $scale;
        }

        if ($number > 0) {
            $groups[] = $locale === 'et'
                ? $this->estonianUnderThousand($number)
                : $this->latvianLithuanianUnderThousand($number, $locale);
        }

        return implode(' ', $groups);
    }

    private function latvianLithuanianUnderThousand($number, $locale)
    {
        $data = $locale === 'lv'
            ? [
                'ones' => ['', 'viens', 'divi', 'trīs', 'četri', 'pieci', 'seši', 'septiņi', 'astoņi', 'deviņi', 'desmit', 'vienpadsmit', 'divpadsmit', 'trīspadsmit', 'četrpadsmit', 'piecpadsmit', 'sešpadsmit', 'septiņpadsmit', 'astoņpadsmit', 'deviņpadsmit'],
                'tens' => ['', '', 'divdesmit', 'trīsdesmit', 'četrdesmit', 'piecdesmit', 'sešdesmit', 'septiņdesmit', 'astoņdesmit', 'deviņdesmit'],
                'hundred_one' => 'simts',
                'hundred_many' => 'simti',
            ]
            : [
                'ones' => ['', 'vienas', 'du', 'trys', 'keturi', 'penki', 'šeši', 'septyni', 'aštuoni', 'devyni', 'dešimt', 'vienuolika', 'dvylika', 'trylika', 'keturiolika', 'penkiolika', 'šešiolika', 'septyniolika', 'aštuoniolika', 'devyniolika'],
                'tens' => ['', '', 'dvidešimt', 'trisdešimt', 'keturiasdešimt', 'penkiasdešimt', 'šešiasdešimt', 'septyniasdešimt', 'aštuoniasdešimt', 'devyniasdešimt'],
                'hundred_one' => 'šimtas',
                'hundred_many' => 'šimtai',
            ];

        $parts = [];
        if ($number >= 100) {
            $hundreds = intdiv($number, 100);
            $parts[] = $data['ones'][$hundreds] . ' ' . ($hundreds === 1 ? $data['hundred_one'] : $data['hundred_many']);
            $number %= 100;
        }
        if ($number < 20) {
            if ($number > 0) {
                $parts[] = $data['ones'][$number];
            }

            return implode(' ', $parts);
        }

        $parts[] = $data['tens'][intdiv($number, 10)];
        if ($number % 10) {
            $parts[] = $data['ones'][$number % 10];
        }

        return implode(' ', $parts);
    }

    private function estonianUnderThousand($number)
    {
        $ones = ['', 'üks', 'kaks', 'kolm', 'neli', 'viis', 'kuus', 'seitse', 'kaheksa', 'üheksa'];
        $parts = [];

        if ($number >= 100) {
            $parts[] = $ones[intdiv($number, 100)] . 'sada';
            $number %= 100;
        }
        if ($number >= 10 && $number < 20) {
            $parts[] = $number === 10 ? 'kümme' : $ones[$number - 10] . 'teist';

            return implode(' ', $parts);
        }
        if ($number >= 20) {
            $parts[] = $ones[intdiv($number, 10)] . 'kümmend';
            $number %= 10;
        }
        if ($number > 0) {
            $parts[] = $ones[$number];
        }

        return implode(' ', $parts);
    }

    private function balticScaleForm($number, array $forms, $locale)
    {
        if ($locale === 'et') {
            return $forms[0];
        }

        $lastTwo = $number % 100;
        $last = $number % 10;
        if ($locale === 'lv') {
            return $last === 1 && $lastTwo !== 11 ? $forms[0] : $forms[1];
        }

        if ($last === 1 && $lastTwo !== 11) {
            return $forms[0];
        }
        if ($last >= 2 && $last <= 9 && !($lastTwo >= 12 && $lastTwo <= 19)) {
            return $forms[1];
        }

        return $forms[2];
    }

    private function euroForm($number, $locale)
    {
        if (in_array($locale, ['lv', 'pl', 'ru', 'de'], true)) {
            if ($locale === 'de') {
                return 'Euro';
            }

            return $locale === 'ru' ? 'евро' : 'euro';
        }
        if ($locale === 'en') {
            return $number === 1 ? 'euro' : 'euros';
        }
        if ($locale === 'et') {
            return $number === 1 ? 'euro' : 'eurot';
        }

        return $this->lithuanianForm($number, ['euras', 'eurai', 'eurų']);
    }

    private function centForm($number, $locale)
    {
        if ($locale === 'ru') {
            return $this->slavicForm($number, ['цент', 'цента', 'центов']);
        }
        if ($locale === 'pl') {
            return $this->slavicForm($number, ['cent', 'centy', 'centów']);
        }
        if ($locale === 'lv') {
            $lastTwo = $number % 100;

            return $number % 10 === 1 && $lastTwo !== 11 ? 'cents' : 'centi';
        }
        if ($locale === 'lt') {
            return $this->lithuanianForm($number, ['centas', 'centai', 'centų']);
        }
        if ($locale === 'et') {
            return $number === 1 ? 'sent' : 'senti';
        }
        if ($locale === 'de') {
            return 'Cent';
        }

        return $number === 1 ? 'cent' : 'cents';
    }

    private function lithuanianForm($number, array $forms)
    {
        $lastTwo = $number % 100;
        $last = $number % 10;

        if ($last === 1 && $lastTwo !== 11) {
            return $forms[0];
        }
        if ($last >= 2 && $last <= 9 && !($lastTwo >= 12 && $lastTwo <= 19)) {
            return $forms[1];
        }

        return $forms[2];
    }

    private function andWord($locale)
    {
        return [
            'lv' => 'un',
            'lt' => 'ir',
            'pl' => 'i',
            'ru' => 'и',
            'de' => 'und',
            'en' => 'and',
            'et' => 'ja',
        ][$locale];
    }

    private function uppercaseFirst($text)
    {
        if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
            return mb_strtoupper(mb_substr($text, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($text, 1, null, 'UTF-8');
        }

        return ucfirst($text);
    }
}
