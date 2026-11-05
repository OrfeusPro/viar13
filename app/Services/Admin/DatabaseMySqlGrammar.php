<?php

namespace App\Services\Admin;

use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Support\Fluent;
use Illuminate\Database\Schema\Blueprint;

/** Voyager types which do not have a native Blueprint method. */
class DatabaseMySqlGrammar extends MySqlGrammar
{
    protected function getType(Fluent $column)
    {
        if ($column->databaseNumericType !== null) {
            if (! preg_match('/^(?:tinyint|smallint|mediumint|int|integer|bigint|decimal|numeric|float|double)(?:\(\d+(?:,\s*\d+)?\))?(?: unsigned)? zerofill$/D', $column->databaseNumericType)) {
                throw new \InvalidArgumentException('Invalid preserved numeric declaration.');
            }
            return $column->databaseNumericType;
        }
        return parent::getType($column);
    }

    protected function modifyUnsigned(Blueprint $blueprint, Fluent $column)
    {
        return $column->databaseNumericType !== null ? null : parent::modifyUnsigned($blueprint, $column);
    }

    protected function typeYear(Fluent $column): string
    {
        return 'year';
    }

    protected function typeFloat(Fluent $column): string
    {
        return 'float';
    }

    protected function typeVoyagerNative(Fluent $column): string
    {
        return match ($column->voyagerType) {
            'tinytext' => 'tinytext',
            'tinyblob' => 'tinyblob',
            'blob' => 'blob',
            'mediumblob' => 'mediumblob',
            'longblob' => 'longblob',
            'bit' => 'bit('.max(1, min(64, (int) $column->length)).')',
            default => throw new \InvalidArgumentException('Unsupported Voyager SQL type.'),
        };
    }
}
