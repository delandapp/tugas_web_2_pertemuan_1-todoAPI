<?php

namespace App\Repositories;

use App\Models\Todo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TodoRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $filtersForQuery = array_diff_key(
            $filters,
            array_flip(['per_page'])
        );

        return Todo::query()
            ->filter($filtersForQuery)
            ->orderByDesc('created_at')
            ->paginate(perPage: $perPage)
            ->appends($filters);
    }

    public function all(array $filters = []): Collection
    {
        $filtersForQuery = array_diff_key(
            $filters,
            array_flip(['per_page'])
        );

        return Todo::query()
            ->filter($filtersForQuery)
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $attributes): Todo
    {
        return Todo::create($attributes);
    }

    public function findByUuid(string $uuid): Todo
    {
        $todo = Todo::query()->where('uuid', $uuid)->first();

        if (! $todo) {
            throw new ModelNotFoundException("Todo not found for uuid [$uuid].");
        }

        return $todo;
    }

    public function update(Todo $todo, array $attributes): Todo
    {
        $todo->fill($attributes);
        $todo->save();

        return $todo->refresh();
    }

    public function delete(Todo $todo): void
    {
        $todo->delete();
    }
}
