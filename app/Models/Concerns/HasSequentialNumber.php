<?php

namespace App\Models\Concerns;

trait HasSequentialNumber
{
    /**
     * Next human-readable number such as PR-2026-0001.
     * The UNIQUE index is the final guard against two requests racing.
     * $column and $prefix must be code constants, never user input.
     */
    protected static function nextNumber(string $column, string $prefix): string
    {
        $prefix = $prefix.'-'.now()->year.'-';

        $last = static::query()
            ->withoutGlobalScopes() // include soft-deleted rows
            ->where($column, 'like', $prefix.'%')
            ->orderByRaw("length({$column}) desc")
            ->orderByDesc($column)
            ->value($column);

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
