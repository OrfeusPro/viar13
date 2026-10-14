<?php

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Filament\Bread\BreadTextPreviewSearch;
use Illuminate\Support\Facades\DB;

// SELECT-only regression against the configured MySQL/MariaDB connection.
$values = [1 => str_repeat('Я', 200).'HiddenTail', 2 => '<b>Visible</b> 100%_ '.str_repeat('Ю', 200), 3 => 'short'];
$source = null;
foreach ($values as $id => $value) {
    $row = DB::query()->selectRaw('? AS id, ? AS caption', [$id, $value]);
    $source = $source ? $source->unionAll($row) : $row;
}
$cases = ['HiddenTail' => [], str_repeat('Я', 200).' ...' => [1], '100%_' => [2], '<b>Visible</b>' => [2], 'short' => [3], 'Ю ...' => [2]];
foreach (['text', 'text_area', 'number'] as $type) {
    foreach ($cases as $term => $expected) {
        $query = DB::query()->fromSub(clone $source, 'fixture');
        $query->where(fn ($builder) => app(BreadTextPreviewSearch::class)->apply($builder, '`fixture`.`caption`', $type, [], false, $term, 'like'));
        $actual = array_map('intval', $query->orderBy('id')->pluck('id')->all());
        if ($actual !== $expected) { throw new RuntimeException('Caption search mismatch: '.$type); }
    }
}
echo 'PASS: 18 SELECT-only Unicode/ellipsis/literal caption cases'.PHP_EOL;

$richValues = [
    '<p secret="HiddenAttribute">'.str_repeat('Я', 200).'HiddenTail</p>',
    '<p><!--HiddenComment--><strong>Visible</strong><br><b class="KeepAttribute">Bold</b><i>Italic</i><u>Under</u>&amp; 100%_</p>',
    '<div title="a > b">Quotes</div>',
    "<div title='a > b'>Single quotes</div>",
    '<B class="kept">UPPER</B><big>Other tag</big>',
    "<!-- multiline\ncomment --><p>Text</p>",
    '<?php echo "hidden"; ?>Visible',
    '<!DOCTYPE html><p>Declaration</p>',
    null,
    '',
];
foreach ($richValues as $value) {
    $expected = \App\Filament\Bread\BreadCaption::preview('rich_text_box', $value);
    $query = DB::query()->fromSub(DB::query()->selectRaw('? AS caption', [$value]), 'fixture');
    $query->where(fn ($builder) => app(BreadTextPreviewSearch::class)->apply($builder, '`fixture`.`caption`', 'rich_text_box', [], false, $expected, '='));
    if ($query->count() !== ($value === null ? 0 : 1)) { throw new RuntimeException('Rich caption differs from PHP preview'); }
}
echo 'PASS: 10 SELECT-only rich captions compared with PHP preview'.PHP_EOL;
