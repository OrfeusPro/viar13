<?php

namespace App\Filament\Bread;

use Illuminate\Database\Query\Builder;
use stdClass;

class BreadBrowseFileSort
{
    /** Standard client browse captions; custom renderers retain their existing fallback. */
    public function apply(Builder $query, string $column, stdClass $row, string $direction): bool
    {
        if (! in_array($row->type, ['file', 'media_picker'], true)) { return false; }
        $details = json_decode($row->details ?: '{}', true) ?: [];
        if (! is_array($details) || isset($details['view'])) { return false; }
        $driver = $query->getConnection()->getDriverName();
        if (! in_array($driver, ['sqlite', 'mysql', 'mariadb'], true)) { return false; }
        $sqlite = $driver === 'sqlite';
        $value = $query->getGrammar()->wrap($column);
        $document = "CASE WHEN JSON_VALID($value) THEN $value ELSE 'null' END";
        $kind = "JSON_TYPE($document)";
        $array = $sqlite ? "'array'" : "'ARRAY'";
        $null = $sqlite ? "'null'" : "'NULL'";
        $bindings = [];
        if ($row->type === 'file') {
            if ($sqlite) {
                $item = "CASE WHEN viar_sort_files.type = 'object' THEN viar_sort_files.value ELSE '{}' END";
                $caption = "JSON_EXTRACT($item, '$.original_name')";
                $path = "JSON_EXTRACT($item, '$.download_link')";
                $joined = "(SELECT GROUP_CONCAT($caption, ' ') FROM JSON_EACH($document) AS viar_sort_files WHERE $path IS NOT NULL AND $path <> '')";
            } else {
                $item = "JSON_EXTRACT($document, CONCAT('$[', viar_sort_files.position - 1, ']'))";
                $caption = "JSON_UNQUOTE(CONVERT(JSON_EXTRACT($item, '$.original_name') USING utf8mb4))";
                $path = "JSON_UNQUOTE(JSON_EXTRACT($item, '$.download_link'))";
                $joined = "(SELECT GROUP_CONCAT($caption ORDER BY viar_sort_files.position SEPARATOR ' ') FROM JSON_TABLE($document, '$[*]' COLUMNS (position FOR ORDINALITY)) AS viar_sort_files WHERE JSON_TYPE(JSON_EXTRACT($item, '$.original_name')) IN ('STRING', 'INTEGER', 'DOUBLE') AND $path IS NOT NULL AND $path <> '')";
            }
            $sort = "CASE WHEN $value IS NULL OR $value = '' THEN '' WHEN $kind = $array THEN COALESCE($joined, '') WHEN $kind = $null THEN 'Download' ELSE '' END";
        } else {
            $length = ($sqlite ? 'JSON_ARRAY_LENGTH' : 'JSON_LENGTH')."($document)";
            $parts = [];
            if (empty($details['show_as_images'])) {
                for ($index = 0; $index < 3; $index++) {
                    $item = "JSON_EXTRACT($document, '$[$index]')";
                    $caption = $sqlite ? $item : "JSON_UNQUOTE(CONVERT($item USING utf8mb4))";
                    $itemType = $sqlite ? "JSON_TYPE($document, '$[$index]')" : "JSON_TYPE($item)";
                    $scalar = $sqlite ? "('text', 'integer', 'real')" : "('STRING', 'INTEGER', 'DOUBLE')";
                    $parts[] = "CASE WHEN $itemType IN $scalar THEN $caption ELSE '' END";
                }
            }
            [$prefix, $suffix] = explode(':count', __('admin_bread.files_more', ['count' => ':count']), 2);
            $count = $sqlite ? "(? || ($length - 3) || ?)" : "CONCAT(?, ($length - 3), ?)";
            $parts[] = "CASE WHEN $length > 3 THEN $count ELSE '' END";
            $joined = $sqlite ? implode(" || ' ' || ", $parts) : "CONCAT_WS(' ', ".implode(', ', $parts).')';
            $sort = "CASE WHEN $value IS NULL OR $value = '' OR ($kind = $array AND $length = 0) THEN ? WHEN $kind = $array THEN ($joined) ELSE ".(empty($details['show_as_images']) ? $value : "''").' END';
            $bindings = [trans_choice('admin_bread.files', 0), $prefix, $suffix];
        }
        if ($sqlite) {
            // SQLite LOWER only folds ASCII; use a connection-local Unicode comparator.
            $query->getConnection()->getPdo()->sqliteCreateCollation('viar_bread_caption',
                fn ($left, $right) => strcmp(mb_strtolower(trim($left)), mb_strtolower(trim($right))));
            $sort = "($sort) COLLATE viar_bread_caption";
        } else {
            $sort = "LOWER(TRIM(CONVERT(($sort) USING utf8mb4))) COLLATE utf8mb4_bin";
        }
        $query->reorder()->orderByRaw($sort.' '.($direction === 'asc' ? 'asc' : 'desc'), $bindings);
        return true;
    }
}
