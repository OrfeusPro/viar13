<?php

namespace App\Filament\Bread;

use Illuminate\Database\Query\Builder;

class BreadBrowseFileSearch
{
    /** Client-side search reads captions, not JSON keys or invisible image paths. */
    public function apply(Builder $query, string $expression, string $type, array $details, string $search, string $operator): void
    {
        if (isset($details['view'])) { return; }
        $driver = $query->getConnection()->getDriverName();
        if (! in_array($driver, ['sqlite', 'mysql', 'mariadb'], true)) { return; }
        $document = "CASE WHEN JSON_VALID($expression) THEN $expression ELSE 'null' END";
        $sqlite = $driver === 'sqlite';
        $jsonType = "JSON_TYPE($document)";
        $arrayType = $sqlite ? "'array'" : "'ARRAY'";
        $length = ($sqlite ? 'JSON_ARRAY_LENGTH' : 'JSON_LENGTH')."($document)";
        $pattern = $operator === '=' ? $search : app(BreadSmartSearch::class)->containsPattern($search);
        $comparison = $operator === '=' ? '= ?' : "LIKE ? ESCAPE '!'";

        if ($type === 'file') {
            if ($sqlite) {
                $source = "JSON_EACH($document) AS viar_file_values";
                $item = "CASE WHEN viar_file_values.type = 'object' THEN viar_file_values.value ELSE '{}' END";
                $caption = "JSON_EXTRACT($item, '$.original_name')";
                $path = "JSON_EXTRACT($item, '$.download_link')";
                $scalar = "JSON_TYPE($item, '$.original_name') IN ('text', 'integer', 'real') AND JSON_TYPE($item, '$.download_link') = 'text'";
            } else {
                // Ordinality + JSON_EXTRACT also works in correlated MariaDB EXISTS queries.
                // JSON_TABLE value columns passed constant fixtures but missed real review rows.
                $source = "JSON_TABLE($document, '$[*]' COLUMNS (position FOR ORDINALITY)) AS viar_file_values";
                $item = "JSON_EXTRACT($document, CONCAT('$[', viar_file_values.position - 1, ']'))";
                $caption = "JSON_UNQUOTE(CONVERT(JSON_EXTRACT($item, '$.original_name') USING utf8mb4))";
                $path = "JSON_UNQUOTE(CONVERT(JSON_EXTRACT($item, '$.download_link') USING utf8mb4))";
                $scalar = "JSON_TYPE(JSON_EXTRACT($item, '$.original_name')) IN ('STRING', 'INTEGER', 'DOUBLE') AND JSON_TYPE(JSON_EXTRACT($item, '$.download_link')) = 'STRING'";
            }
            $query->orWhereRaw("($jsonType = $arrayType AND EXISTS (SELECT 1 FROM $source WHERE $scalar AND $path <> '' AND $caption $comparison))", [$pattern]);
            // Legacy scalar paths display the literal Download caption.
            if ($this->matches('Download', $search, $operator)) {
                $nullType = $sqlite ? "'null'" : "'NULL'";
                $query->orWhereRaw("($expression IS NOT NULL AND $expression <> '' AND $jsonType = $nullType)");
            }
            return;
        }

        if (empty($details['show_as_images'])) {
            for ($index = 0; $index < 3; $index++) {
                $path = "JSON_EXTRACT($document, '$[$index]')";
                $scalarType = $sqlite ? "'text'" : "'STRING'";
                $itemType = $sqlite ? "JSON_TYPE($document, '$[$index]')" : "JSON_TYPE($path)";
                $caption = $sqlite ? $path : "JSON_UNQUOTE(CONVERT($path USING utf8mb4))";
                $query->orWhereRaw("($jsonType = $arrayType AND $itemType = $scalarType AND $caption $comparison)", [$pattern]);
            }
            $query->orWhereRaw("($expression IS NOT NULL AND $expression <> '' AND $jsonType <> $arrayType AND $expression $comparison)", [$pattern]);
        }
        if ($this->matches(trans_choice('admin_bread.files', 0), $search, $operator)) {
            $query->orWhereRaw("($expression IS NULL OR $expression = '' OR ($jsonType = $arrayType AND $length = 0))");
        }
        // Count is unbounded; compare its localized caption in SQL instead of scanning records in PHP.
        $template = __('admin_bread.files_more', ['count' => ':count']);
        [$prefix, $suffix] = explode(':count', $template, 2);
        $caption = $sqlite ? "(? || ($length - 3) || ?)" : "CONCAT(?, ($length - 3), ?)";
        $query->orWhereRaw("($jsonType = $arrayType AND $length > 3 AND $caption $comparison)", [$prefix, $suffix, $pattern]);
    }

    private function matches(string $caption, string $search, string $operator): bool
    {
        $caption = mb_strtolower($caption);
        $search = mb_strtolower($search);
        return $operator === '=' ? $caption === $search : str_contains($caption, $search);
    }
}
