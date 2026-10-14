<?php
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app->setLocale('en');
$cases = [
    ['file', [], [1=>'[{"download_link":"a.pdf","original_name":"Zulu.pdf"}]', 2=>'[{"download_link":"z.pdf","original_name":"Alpha"},{"download_link":"b.pdf","original_name":"z.pdf"}]',
        3=>'[{"download_link":"y.pdf","original_name":"alpha"},{"download_link":"c.pdf","original_name":"a.pdf"}]',4=>'single.pdf',5=>'[]',6=>null,
        7=>'[{"download_link":"e.pdf","original_name":"ё.pdf"}]',8=>'[{"download_link":"f.pdf","original_name":"Ё.pdf"}]'], [6,5,3,2,4,1,8,7], [8,7,1,4,2,3,6,5]],
    ['media_picker', [], [1=>'["z.jpg"]',2=>'[]',3=>'["a.jpg","b.jpg","c.jpg","z-hidden.jpg"]',4=>'["a.jpg","b.jpg","c.jpg","a-hidden.jpg"]',5=>'a single.jpg',6=>null], [6,2,5,4,3,1], [1,4,3,5,6,2]],
    ['media_picker', ['show_as_images'=>true], [1=>'["z.jpg"]',2=>'[]',3=>'["a.jpg","b.jpg","c.jpg","z-hidden.jpg"]',4=>'["a.jpg","b.jpg","c.jpg","a-hidden.jpg"]',5=>'a single.jpg',6=>null], [5,1,6,2,4,3], [4,3,6,2,5,1]],
];
$sort = $app->make(App\Filament\Bread\BreadBrowseFileSort::class);
foreach ($cases as [$type, $details, $values, $asc, $desc]) {
    foreach (['asc'=>$asc,'desc'=>$desc] as $direction=>$expected) {
        $source = null;
        foreach ($values as $id=>$value) {
            $row = Illuminate\Support\Facades\DB::query()->selectRaw('? AS id, ? AS value', [$id,$value]);
            $source = $source ? $source->unionAll($row) : $row;
        }
        $query = Illuminate\Support\Facades\DB::query()->fromSub($source,'fixture');
        $sort->apply($query, 'fixture.value', (object) ['type'=>$type,'details'=>json_encode($details)],$direction);
        $actual = $query->orderByDesc('id')->pluck('id')->map(fn($id)=>(int)$id)->all();
        if ($actual !== $expected) { throw new RuntimeException(json_encode([$type,$details,$direction,$expected,$actual])); }
    }
}

echo '6 SELECT-only file/media sort orders PASS'.PHP_EOL;
