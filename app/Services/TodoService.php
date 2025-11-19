<?php

namespace App\Services;

use App\Enums\TodoStatus;
use App\Models\Todo;
use App\Models\User;
use App\Repositories\TodoRepository;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class TodoService
{
    public function __construct(
        private readonly TodoRepository $repository,
    ) {
    }

    public function paginate(User $user, array $filters = []): LengthAwarePaginator
    {
        $perPage = $this->sanitizePerPage($filters['per_page'] ?? null);

        return $this->repository->paginate($user, $filters, $perPage);
    }

    public function getAll(User $user, array $filters = []): Collection
    {
        return $this->repository->all($user, $filters);
    }

    public function create(User $user, array $payload): Todo
    {
        $attributes = $this->prepareAttributes($payload);
        $attributes['user_id'] = $user->id;

        return $this->repository->create($attributes);
    }

    public function findByUuid(string $uuid): Todo
    {
        return $this->repository->findByUuid($uuid);
    }

    public function update(Todo $todo, array $payload): Todo
    {
        $attributes = $this->prepareAttributes($payload, $todo);

        return $this->repository->update($todo, $attributes);
    }

    public function delete(Todo $todo): void
    {
        $this->repository->delete($todo);
    }

    private function prepareAttributes(array $payload, ?Todo $existing = null): array
    {
        $attributes = $payload;

        if (! isset($attributes['status'])) {
            $attributes['status'] = $existing?->status?->value ?? TodoStatus::Pending->value;
        }

        if (! isset($attributes['priority'])) {
            $attributes['priority'] = $existing?->priority ?? 3;
        }

        if (! isset($attributes['uuid']) && ! $existing) {
            $attributes['uuid'] = (string) Str::uuid();
        }

        $attributes = $this->syncCompletedAt($attributes, $existing);

        return $attributes;
    }

    private function syncCompletedAt(array $attributes, ?Todo $existing = null): array
    {
        $status = $attributes['status'] ?? $existing?->status?->value;

        if (! $status) {
            return $attributes;
        }

        $isCompletedState = in_array($status, [TodoStatus::Completed->value, TodoStatus::Archived->value], true);

        if ($isCompletedState && empty($attributes['completed_at'])) {
            $attributes['completed_at'] = CarbonImmutable::now();
        }

        if (! $isCompletedState) {
            $attributes['completed_at'] = null;
        }

        return $attributes;
    }

    private function sanitizePerPage(mixed $perPage): int
    {
        $perPage = filter_var($perPage, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        return $perPage ? min($perPage, 100) : 15;
    }
}
