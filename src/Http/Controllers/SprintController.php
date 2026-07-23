<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Controllers;

use Eather009\LaravelSprintBoard\Contracts\UserResolver;
use Eather009\LaravelSprintBoard\Http\Requests\StoreSprintRequest;
use Eather009\LaravelSprintBoard\Http\Requests\SyncMembersRequest;
use Eather009\LaravelSprintBoard\Http\Requests\UpdateRetrospectiveRequest;
use Eather009\LaravelSprintBoard\Http\Requests\UpdateSprintRequest;
use Eather009\LaravelSprintBoard\Http\Resources\SprintMemberResource;
use Eather009\LaravelSprintBoard\Http\Resources\SprintResource;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Services\SprintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SprintController extends Controller
{
    public function index(UserResolver $resolver, SprintService $service): AnonymousResourceCollection
    {
        return SprintResource::collection($service->listFor($this->actor($resolver)));
    }

    public function store(StoreSprintRequest $request, UserResolver $resolver, SprintService $service): JsonResponse
    {
        $data = $request->validated();
        $members = $data['members'] ?? [];
        unset($data['members']);

        $sprint = $service->create($this->actor($resolver), $data, $members);

        return (new SprintResource($sprint))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Sprint $sprint, UserResolver $resolver, SprintService $service): SprintResource
    {
        $sprint = $service->findForView($this->actor($resolver), $sprint->getKey());

        return new SprintResource($sprint);
    }

    public function update(UpdateSprintRequest $request, Sprint $sprint, UserResolver $resolver, SprintService $service): SprintResource
    {
        $actor = $this->actor($resolver);
        $service->findForView($actor, $sprint->getKey());
        $sprint = $service->update($actor, $sprint, $request->validated());

        return new SprintResource($sprint);
    }

    public function destroy(Sprint $sprint, UserResolver $resolver, SprintService $service): JsonResponse
    {
        $actor = $this->actor($resolver);
        $service->findForView($actor, $sprint->getKey());
        $service->delete($actor, $sprint);

        return response()->json(['message' => 'Deleted.'], 200);
    }

    public function members(Sprint $sprint, UserResolver $resolver, SprintService $service): AnonymousResourceCollection
    {
        $sprint = $service->findForView($this->actor($resolver), $sprint->getKey());

        return SprintMemberResource::collection($sprint->members);
    }

    public function syncMembers(SyncMembersRequest $request, Sprint $sprint, UserResolver $resolver, SprintService $service): SprintResource
    {
        $actor = $this->actor($resolver);
        $service->findForView($actor, $sprint->getKey());
        $sprint = $service->syncMembers($actor, $sprint, $request->validated('members'));

        return new SprintResource($sprint);
    }

    public function retrospective(Sprint $sprint, UserResolver $resolver, SprintService $service): JsonResponse
    {
        $sprint = $service->findForView($this->actor($resolver), $sprint->getKey());

        return response()->json(['data' => ['retrospective' => $sprint->retrospective]]);
    }

    public function updateRetrospective(
        UpdateRetrospectiveRequest $request,
        Sprint $sprint,
        UserResolver $resolver,
        SprintService $service
    ): SprintResource {
        $actor = $this->actor($resolver);
        $service->findForView($actor, $sprint->getKey());
        $sprint = $service->update($actor, $sprint, [
            'retrospective' => $request->validated('retrospective'),
        ]);

        return new SprintResource($sprint);
    }
}
