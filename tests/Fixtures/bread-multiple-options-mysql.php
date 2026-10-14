<?php
// Local read-only SQL verification: all records come from SELECT literals, no table writes.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo Illuminate\Support\Facades\DB::selectOne('SELECT VERSION() AS version')->version.PHP_EOL;
$service = app(App\Filament\Bread\BreadMultipleOptionSearch::class);
$cases = [
    ['["art",2]', 'Canvas art', true], ['{"art":"22"}', 'Canvas art', false],
    ['[22]', 'Metal print', false], ['{"2":"2"}', 'Metal print', true],
    ['{bad json', 'Canvas art', false], ['null', 'Canvas art', false],
    ['"art"', 'Canvas art', false], ['{"nested":["art"]}', 'Canvas art', false],
    ['[["art"]]', 'Canvas art', false], ['["a\\"b"]', 'Quoted option', true],
];
foreach ($cases as [$json, $search, $expected]) {
    $query = Illuminate\Support\Facades\DB::query()->fromSub(Illuminate\Support\Facades\DB::query()->selectRaw('? AS choices', [$json]), 'fixture')->whereRaw('1=0');
    $service->apply($query, 'fixture.choices', [], ['art' => 'Canvas art', 2 => 'Metal print', 22 => 'Large print', 'a"b' => 'Quoted option'], $search, '=');
    if ($query->exists() !== $expected) { throw new RuntimeException('Unexpected match: '.$json); }
}
foreach ([['[22]', false], [null, true], ['', false]] as [$translated, $expected]) {
    $query = Illuminate\Support\Facades\DB::query()->fromSub(Illuminate\Support\Facades\DB::query()->selectRaw('? AS choices', ['["art"]']), 'fixture')->whereRaw('1=0');
    $service->apply($query, 'COALESCE(?, fixture.choices)', [$translated], ['art' => 'Canvas art'], 'Canvas art', '=');
    if ($query->exists() !== $expected) { throw new RuntimeException('Translation binding/fallback mismatch'); }
}
echo 'PASS '.(count($cases) + 3).' read-only MySQL cases'.PHP_EOL;
foreach ([['["tag"]', '<b>', true], ['["tag"]', '</b>', true], ['["entity"]', 'amp;', true],
    ['["entity"]', 'Rock & Roll', false], ['["unknown"]', '<b>', false]] as [$json, $search, $expected]) {
    $query = Illuminate\Support\Facades\DB::query()->fromSub(Illuminate\Support\Facades\DB::query()->selectRaw('? AS choices', [$json]), 'fixture')->whereRaw('1=0');
    $service->apply($query, 'fixture.choices', [], ['tag' => '<b>Active</b>', 'entity' => 'Rock &amp; Roll'], $search, 'like');
    if ($query->exists() !== $expected) { throw new RuntimeException('Escaped multiple caption literal mismatch'); }
}
echo 'PASS 5 read-only literal multiple caption cases'.PHP_EOL;
$emptyCases = [null, '', 'null', '[]', '{}', '["unknown"]', '["art"]', '{bad json', 'false', '0', '0.0', '""', '"0"', '" "'];
$emptyCount = 0;
foreach (['multiple_checkbox' => [0, 1, 2, 3, 4, 7], 'select_multiple' => [0, 1, 2, 3, 7, 8, 9, 10, 11, 12]] as $type => $expectedIndices) {
    foreach ($emptyCases as $index => $json) {
        $query = Illuminate\Support\Facades\DB::query()->fromSub(Illuminate\Support\Facades\DB::query()->selectRaw('? AS choices', [$json]), 'fixture')->whereRaw('1=0');
        // A repeated bound COALESCE expression also verifies translation-style bindings.
        $service->applyEmpty($query, 'COALESCE(?, fixture.choices)', [null], $type, App\Filament\Bread\BreadCaption::none(), '=');
        if ($query->exists() !== in_array($index, $expectedIndices, true)) { throw new RuntimeException('Empty caption mismatch: '.$type.' '.$index); }
        $emptyCount++;
    }
}
echo 'PASS '.$emptyCount.' read-only empty caption cases'.PHP_EOL;
foreach ([['{"two":{"note":"one"}}', 'Mountain', true], ['{"one":{"note":"two"}}', 'Mountain', false], ['["two"]', 'Mountain', false], ['{bad json', 'Mountain', false]] as [$json, $search, $expected]) {
    $query = Illuminate\Support\Facades\DB::query()->fromSub(Illuminate\Support\Facades\DB::query()->selectRaw('? AS choices', [$json]), 'fixture')->whereRaw('1=0');
    $service->apply($query, 'COALESCE(?, fixture.choices)', [null], ['one' => 'Ocean', 'two' => 'Mountain'], $search, '=', true);
    if ($query->exists() !== $expected) { throw new RuntimeException('Pivot object key mismatch'); }
}
echo 'PASS 4 read-only pivot key cases'.PHP_EOL;

$sort = app(App\Filament\Bread\BreadOptionSort::class);
foreach ([
    ['select_dropdown', ['options' => ['a' => 'Zulu', 'z' => 'alpha', 'q' => 'ALPHA']], ['a', 'z', 'q', 'unknown'], [4, 3, 2, 1]],
    ['radio_btn', ['options' => ['a' => '10', 'z' => '2', 'q' => '2.0']], ['a', 'z', 'q', 'unknown'], [4, 3, 2, 1]],
    ['checkbox', ['on' => 'Да', 'off' => 'Нет'], ['0', '1', null, '2', 'false', ''], [5, 4, 2, 6, 3, 1]],
] as [$type, $details, $values, $expected]) {
    $source = null;
    foreach ($values as $index => $value) {
        $literal = Illuminate\Support\Facades\DB::query()->selectRaw('? AS id, ? AS value', [$index + 1, $value]);
        if ($source === null) { $source = $literal; } else { $source->unionAll($literal); }
    }
    foreach (['asc' => $expected, 'desc' => null] as $direction => $ids) {
        $query = Illuminate\Support\Facades\DB::query()->fromSub($source, 'fixture');
        $sort->apply($query, 'fixture.value', (object) ['type' => $type, 'details' => json_encode($details)], $direction);
        $actual = $query->orderByDesc('fixture.id')->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($ids !== null && $actual !== $ids) { throw new RuntimeException('Caption sorting mismatch: '.$type); }
        if ($ids === null) {
            $groups = $type === 'checkbox' ? [[6, 3, 1], [5, 4, 2]] : [[1], [3, 2], [4]];
            if ($actual !== array_merge(...$groups)) { throw new RuntimeException('Descending caption sorting mismatch: '.$type); }
        }
    }
}
echo 'PASS 6 read-only option sort cases'.PHP_EOL;

$checkboxSearch = app(App\Filament\Bread\BreadCheckboxSearch::class);
$checkboxCases = 0;
foreach (['0', '1', null, '2', 'false', '', '00', ' ', '0.0', '-1'] as $value) {
    foreach (['On' => (bool) $value, 'Off' => ! $value, 'Unknown' => false] as $caption => $expected) {
        $query = Illuminate\Support\Facades\DB::query()->fromSub(Illuminate\Support\Facades\DB::query()->selectRaw('? AS value', [$value]), 'fixture')->whereRaw('1=0');
        $checkboxSearch->apply($query, 'fixture.value', [], ['on' => 'On', 'off' => 'Off'], $caption, '=');
        if ($query->exists() !== $expected) { throw new RuntimeException('Checkbox caption truthiness mismatch'); }
        $checkboxCases++;
    }
}
foreach ([[null, true], ['', false], ['0', false], [' ', true]] as [$translation, $expected]) {
    $query = Illuminate\Support\Facades\DB::query()->fromSub(Illuminate\Support\Facades\DB::query()->selectRaw('? AS value', ['2']), 'fixture')->whereRaw('1=0');
    $checkboxSearch->apply($query, 'COALESCE(?, fixture.value)', [$translation], ['on' => 'On', 'off' => 'Off'], 'On', '=');
    if ($query->exists() !== $expected) { throw new RuntimeException('Checkbox expression binding mismatch'); }
    $checkboxCases++;
}
$source = Illuminate\Support\Facades\DB::query()->selectRaw('? AS id, ? AS value', [1, ''])
    ->unionAll(Illuminate\Support\Facades\DB::query()->selectRaw('? AS id, ? AS value', [2, ' ']));
$query = Illuminate\Support\Facades\DB::query()->fromSub($source, 'fixture');
$sort->apply($query, 'fixture.value', (object) ['type' => 'checkbox', 'details' => json_encode(['on' => 'A', 'off' => 'Z'])], 'asc');
if ($query->pluck('id')->map(fn ($id) => (int) $id)->all() !== [2, 1]) { throw new RuntimeException('Space checkbox sort truthiness mismatch'); }
echo 'PASS '.$checkboxCases.' read-only checkbox search cases + space sort'.PHP_EOL;

$smartSearch = app(App\Filament\Bread\BreadSmartSearch::class);
$literalCases = [
    ['10% Sale', '%', true], ['10X Sale', '%', false], ['file_name', '_', true], ['filename', '_', false],
    ['Wow!', '!', true], ['Wow', '!', false], ['path\\name', '\\', true], ['pathname', '\\', false],
    ['a!%_b', '!%_', true], ['a!XXb', '!%_', false],
];
foreach ($literalCases as [$value, $term, $expected]) {
    $query = Illuminate\Support\Facades\DB::query()->fromSub(Illuminate\Support\Facades\DB::query()->selectRaw('? AS value', [$value]), 'fixture')
        ->whereRaw("fixture.value LIKE ? ESCAPE '!'", [$smartSearch->containsPattern($term)]);
    if ($query->exists() !== $expected) { throw new RuntimeException('Literal LIKE mismatch'); }
}
echo 'PASS '.count($literalCases).' read-only literal LIKE cases'.PHP_EOL;
