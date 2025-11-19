<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTodoRequest;
use App\Http\Requests\UpdateTodoRequest;
use App\Http\Resources\TodoResource;
use App\Models\Todo;
use App\Models\User;
use App\Services\TodoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TodoController extends Controller
{
    public function __construct(
        private readonly TodoService $service,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Todo::class);

        $filters = $request->only(['status', 'priority', 'due_before', 'due_after', 'search', 'per_page']);
        $todos = $this->service->paginate($this->resolveUser($request), $filters);

        return TodoResource::collection($todos)
            ->additional([
                'filters' => array_filter($filters, fn ($value) => $value !== null && $value !== ''),
            ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(Response::HTTP_NOT_FOUND);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTodoRequest $request)
    {
        $this->authorize('create', Todo::class);

        $todo = $this->service->create($this->resolveUser($request), $request->validated());

        return (new TodoResource($todo))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $todo)
    {
        $foundTodo = $this->service->findByUuid($todo);
        $this->authorize('view', $foundTodo);

        return new TodoResource($foundTodo);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $todo)
    {
        abort(Response::HTTP_NOT_FOUND);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTodoRequest $request, string $todo)
    {
        $existing = $this->service->findByUuid($todo);
        $this->authorize('update', $existing);

        $updated = $this->service->update($existing, $request->validated());

        return new TodoResource($updated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $todo)
    {
        $existing = $this->service->findByUuid($todo);
        $this->authorize('delete', $existing);

        $this->service->delete($existing);

        return response()->noContent();
    }

    private function resolveUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthenticated.');
        }

        return $user;
    }
}
