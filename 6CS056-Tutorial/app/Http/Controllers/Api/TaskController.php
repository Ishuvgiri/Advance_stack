<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tasks = $user->isAdmin()
            ? Task::latest()->get()
            : $user->tasks()->latest()->get();

        return TaskResource::collection($tasks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        if ($denial = $this->denialResponse('manage-all-tasks')) {
            return $denial;
        }

        $validated = $request->validated();

        $createdTask = $request->user()->tasks()->create($validated);

        return (new TaskResource($createdTask))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        if ($denial = $this->denialResponse($task)) {
            return $denial;
        }

        return new TaskResource($task);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        if ($denial = $this->denialResponse($task)) {
            return $denial;
        }

        $task->update(
            $request->validated()
        );

        return new TaskResource(
            $task->fresh()
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        if ($denial = $this->denialResponse($task)) {
            return $denial;
        }

        $task->delete();

        return response()->json(null, 204);
    }

    private function denialResponse(Task $task): ?JsonResponse
    {
        $response = Gate::inspect('manage-task', $task);

        if ($response->denied()) {
            return response()->json(['message' => $response->message()], 403);
        }

        return null;
    }
}
