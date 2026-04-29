<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SortirAdmin
{
    /**
     * @param  array<string, Closure|non-empty-string>  $kolom
     */
    public static function terapkan(
        Builder $query,
        Request $request,
        array $kolom,
        string $bawaan,
        string $arahBawaan = 'asc'
    ): Builder {
        $sortirQuery = $request->query('sort');
        $sortir = is_string($sortirQuery) && array_key_exists($sortirQuery, $kolom)
            ? $sortirQuery
            : $bawaan;
        $arahQuery = $request->query('direction');
        $arah = is_string($arahQuery) && in_array($arahQuery, ['asc', 'desc'], true)
            ? $arahQuery
            : ($arahBawaan === 'desc' ? 'desc' : 'asc');
        $target = $kolom[$sortir] ?? $kolom[$bawaan];

        if ($target instanceof Closure) {
            $target($query, $arah);
        } else {
            $query->orderBy($target, $arah);
        }

        return $query;
    }
}
