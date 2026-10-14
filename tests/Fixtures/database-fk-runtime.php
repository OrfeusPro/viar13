<?php

// Manual local-only verification against the disposable server created for this audit.
// Does not use application database credentials or connect to the application database.
use App\Services\Admin\DatabaseTableEditingService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

$root = dirname(__DIR__, 2);
$expectedDirectory = realpath($root.'/storage/app/audit/fk-runtime-20261013');
if ($expectedDirectory === false) { throw new RuntimeException('Disposable datadir missing'); }
$pdo = new PDO('mysql:host=127.0.0.1;port=13317;charset=utf8mb4', 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$actualDirectory = realpath($pdo->query('select @@datadir')->fetchColumn());
if ($actualDirectory === false || strcasecmp($expectedDirectory, $actualDirectory) !== 0) {
    throw new RuntimeException('Refusing to mutate a server with another datadir');
}
$database = 'adm_fk_runtime_20261013';
if ((int) $pdo->query("select count(*) from information_schema.SCHEMATA where SCHEMA_NAME = '$database'")->fetchColumn() !== 0) {
    throw new RuntimeException('Fixture schema already exists; refusing to overwrite');
}
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.connections.fk_runtime' => ['driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 13317,
    'database' => $database, 'username' => 'root', 'password' => '', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => true]]);
DB::setDefaultConnection('fk_runtime');
$checks = 0;
$check = function (bool $ok, string $message) use (&$checks): void {
    if (! $ok) { throw new RuntimeException($message); }
    $checks++;
};
$rejects = function (callable $operation, string $message) use ($check): void {
    try { $operation(); } catch (Illuminate\Database\QueryException $e) {
        $check(($e->errorInfo[0] ?? null) === '23000', $message.' wrong SQLSTATE'); return;
    }
    throw new RuntimeException($message.' was accepted');
};
$service = app(DatabaseTableEditingService::class);
$rename = function (string $table, array $names) use ($service, $check): void {
    $snapshot = $service->snapshot($table); $hash = $service->fingerprint($snapshot);
    $input = $service->definition($table, $snapshot);
    foreach ($input['columns'] as &$column) { $column['name'] = $names[$column['name']] ?? $column['name']; }
    unset($column);
    $plan = $service->preview($table, $input, $hash);
    $check(count($plan['sql']) === count($names), 'Native rename statement count');
    $check($plan['dropIndexes'] === [] && $plan['addIndexes'] === [] && $plan['changed'] === [], 'Unexpected FK/index/property rewrite');
    foreach ($plan['sql'] as $sql) { $check(str_contains($sql, ' rename column '), 'Expected native rename'); }
    $service->apply($table, $input, $hash);
    $check(DB::selectOne('select @@foreign_key_checks as enabled')->enabled == 1, 'Foreign key checks disabled');
    foreach ($names as $old => $new) {
        $check(! Schema::hasColumn($table, $old) && Schema::hasColumn($table, $new), 'Rename not applied');
    }
    try { $service->preview($table, $input, $hash); } catch (ValidationException) { $check(true, 'Stale schema guard'); return; }
    throw new RuntimeException('Stale snapshot accepted');
};

$pdo->exec("create database `$database` character set utf8mb4 collate utf8mb4_unicode_ci");
try {
    DB::statement('create table parent_fixture (id bigint unsigned primary key, note varchar(50)) engine=InnoDB');
    DB::statement('create table child_fixture (id bigint unsigned primary key, parent_id bigint unsigned not null, note varchar(50), index parent_lookup(parent_id), constraint retained_fk foreign key(parent_id) references parent_fixture(id) on update cascade on delete restrict) engine=InnoDB');
    DB::table('parent_fixture')->insert(['id' => 1, 'note' => 'Parent']);
    DB::table('child_fixture')->insert(['id' => 2, 'parent_id' => 1, 'note' => 'Child']);
    $snapshot = $service->snapshot('child_fixture'); $input = $service->definition('child_fixture', $snapshot);
    foreach (['type', 'nullable', 'index', 'drop'] as $change) {
        $bad = $input;
        if ($change === 'type') { $bad['columns'][1]['type'] = 'smallint'; }
        if ($change === 'nullable') { $bad['columns'][1]['nullable'] = true; }
        if ($change === 'index') { $bad['columns'][1]['index'] = null; }
        if ($change === 'drop') { array_splice($bad['columns'], 1, 1); }
        try { $service->preview('child_fixture', $bad, $service->fingerprint($snapshot)); }
        catch (ValidationException) { $check(true, 'Dependency guard '.$change); continue; }
        throw new RuntimeException('Unsafe dependency change accepted: '.$change);
    }
    $rename('child_fixture', ['parent_id' => 'parent_ref']);
    $rename('parent_fixture', ['id' => 'parent_key']);
    $key = Schema::getForeignKeys('child_fixture')[0];
    $check($key['name'] === 'retained_fk' && $key['columns'] === ['parent_ref'] && $key['foreign_columns'] === ['parent_key'], 'Inbound/outbound FK references lost');
    $check($key['on_update'] === 'cascade' && $key['on_delete'] === 'restrict', 'FK actions lost');
    $check(collect(Schema::getIndexes('child_fixture'))->contains(fn ($index) => $index['name'] === 'parent_lookup' && $index['columns'] === ['parent_ref']), 'Index lost');
    $check(DB::table('child_fixture')->first()->note === 'Child' && DB::table('parent_fixture')->first()->note === 'Parent', 'Rows lost');
    $rejects(fn () => DB::table('child_fixture')->insert(['id' => 3, 'parent_ref' => 999]), 'Invalid child');
    $rejects(fn () => DB::table('parent_fixture')->where('parent_key', 1)->delete(), 'Restricted parent deletion');
    DB::table('parent_fixture')->where('parent_key', 1)->update(['parent_key' => 10]);
    $check(DB::table('child_fixture')->value('parent_ref') === 10, 'Update cascade broken');
    echo "PASS inbound/outbound FK: native ALTER, data, index, actions, enforcement, guards\n";

    DB::statement('create table self_fixture (id bigint unsigned primary key, parent_id bigint unsigned null, index self_lookup(parent_id), constraint self_fk foreign key(parent_id) references self_fixture(id) on update cascade on delete set null) engine=InnoDB');
    DB::table('self_fixture')->insert(['id' => 1, 'parent_id' => null]);
    DB::table('self_fixture')->insert(['id' => 2, 'parent_id' => 1]);
    $rename('self_fixture', ['id' => 'node_key', 'parent_id' => 'parent_ref']);
    $key = Schema::getForeignKeys('self_fixture')[0];
    $check($key['columns'] === ['parent_ref'] && $key['foreign_columns'] === ['node_key'] && $key['name'] === 'self_fk', 'Self FK lost');
    $rejects(fn () => DB::table('self_fixture')->insert(['node_key' => 3, 'parent_ref' => 999]), 'Invalid self reference');
    DB::table('self_fixture')->where('node_key', 1)->delete();
    $check(DB::table('self_fixture')->where('node_key', 2)->value('parent_ref') === null, 'Self SET NULL broken');
    echo "PASS self FK: two renames, enforcement, SET NULL\n";

    DB::statement('create table compound_parent (id int not null, code varchar(20) not null, primary key(id,code)) engine=InnoDB');
    DB::statement('create table compound_child (id int primary key, parent_id int not null, parent_code varchar(20) not null, index compound_lookup(parent_id,parent_code), constraint compound_fk foreign key(parent_id,parent_code) references compound_parent(id,code) on update cascade on delete cascade) engine=InnoDB');
    DB::table('compound_parent')->insert(['id' => 1, 'code' => 'A']);
    DB::table('compound_child')->insert(['id' => 2, 'parent_id' => 1, 'parent_code' => 'A']);
    $rename('compound_child', ['parent_id' => 'parent_ref', 'parent_code' => 'code_ref']);
    $rename('compound_parent', ['id' => 'parent_key', 'code' => 'code_key']);
    $key = Schema::getForeignKeys('compound_child')[0];
    $check($key['name'] === 'compound_fk' && $key['columns'] === ['parent_ref', 'code_ref'] && $key['foreign_columns'] === ['parent_key', 'code_key'], 'Composite FK lost');
    $check(collect(Schema::getIndexes('compound_child'))->contains(fn ($index) => $index['name'] === 'compound_lookup' && $index['columns'] === ['parent_ref', 'code_ref']), 'Composite index lost');
    $check(DB::table('compound_child')->first()->code_ref === 'A', 'Composite rows lost');
    $rejects(fn () => DB::table('compound_child')->insert(['id' => 3, 'parent_ref' => 1, 'code_ref' => 'B']), 'Invalid composite reference');
    DB::table('compound_parent')->update(['code_key' => 'C']);
    $check(DB::table('compound_child')->value('code_ref') === 'C', 'Composite update cascade broken');
    DB::table('compound_parent')->delete();
    $check(DB::table('compound_child')->count() === 0, 'Composite delete cascade broken');
    echo "PASS composite FK: both sides renamed, indexes, data, cascades\n";
    echo 'PASS '.$checks.' checks; MariaDB '.$pdo->query('select version()')->fetchColumn()."; disposable schema only\n";
} finally {
    DB::disconnect('fk_runtime');
    $pdo->exec("drop database `$database`");
    echo "Disposable schema removed\n";
}
