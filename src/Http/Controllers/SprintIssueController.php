<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Controllers;

use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use Eather009\LaravelSprintBoard\Contracts\UserResolver;
use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Http\Requests\LinkIssueRequest;
use Eather009\LaravelSprintBoard\Http\Requests\UpdateCompletionRequest;
use Eather009\LaravelSprintBoard\Http\Resources\SprintIssueResource;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Eather009\LaravelSprintBoard\Services\SprintCompletionService;
use Eather009\LaravelSprintBoard\Services\SprintCompletionSyncService;
use Eather009\LaravelSprintBoard\Services\SprintDashboardService;
use Eather009\LaravelSprintBoard\Services\SprintHydrateService;
use Eather009\LaravelSprintBoard\Services\SprintIssueService;
use Eather009\LaravelSprintBoard\Services\SprintService;
use Eather009\LaravelSprintBoard\Trackers\NullIssueTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use LogicException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class SprintIssueController extends Controller
{
    public function index(
        Sprint $sprint,
        UserResolver $resolver,
        SprintService $sprints,
        SprintHydrateService $hydrate,
    ): JsonResponse {
        $actor = $this->actor($resolver);
        $sprint = $sprints->findForView($actor, $sprint->getKey());
        $meta = [];

        try {
            $meta = $hydrate->hydrate($actor, $sprint);
        } catch (Throwable) {
            $meta = [];
        }

        return response()->json([
            'data' => $hydrate->mergeIssues($sprint, $meta),
        ]);
    }

    public function store(
        LinkIssueRequest $request,
        Sprint $sprint,
        UserResolver $resolver,
        SprintService $sprints,
        SprintIssueService $issues,
    ): JsonResponse {
        $actor = $this->actor($resolver);
        $sprints->findForView($actor, $sprint->getKey());
        $issue = $issues->link($actor, $sprint, $request->validated());

        return (new SprintIssueResource($issue))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(
        Sprint $sprint,
        SprintIssue $issue,
        UserResolver $resolver,
        SprintService $sprints,
        SprintIssueService $issues,
    ): JsonResponse {
        $actor = $this->actor($resolver);
        $sprints->findForView($actor, $sprint->getKey());
        $issues->unlink($actor, $sprint, $issue);

        return response()->json(['message' => 'Unlinked.']);
    }

    public function completion(
        UpdateCompletionRequest $request,
        Sprint $sprint,
        SprintIssue $issue,
        UserResolver $resolver,
        SprintService $sprints,
        SprintCompletionService $completion,
    ): SprintIssueResource {
        $actor = $this->actor($resolver);
        $sprints->findForView($actor, $sprint->getKey());
        $status = IssueCompletionStatus::from($request->validated('completion_status'));
        $issue = $completion->update(
            $actor,
            $sprint,
            $issue,
            $status,
            $request->validated('completion_note')
        );

        return new SprintIssueResource($issue);
    }

    public function refresh(
        Sprint $sprint,
        UserResolver $resolver,
        SprintService $sprints,
        SprintCompletionSyncService $sync,
    ): JsonResponse {
        $actor = $this->actor($resolver);
        $sprints->findForView($actor, $sprint->getKey());
        $result = $sync->refreshAndSync($actor, $sprint);

        return response()->json(['data' => $result]);
    }

    public function prioritySync(
        Sprint $sprint,
        UserResolver $resolver,
        SprintService $sprints,
        IssueTracker $tracker,
    ): JsonResponse {
        $actor = $this->actor($resolver);
        $sprints->findForView($actor, $sprint->getKey());

        if ($tracker instanceof NullIssueTracker) {
            return response()->json(['message' => 'Priority sync is not supported for this tracker.'], 501);
        }

        try {
            foreach ($sprint->issues as $issue) {
                if ($issue->priority_id === null) {
                    continue;
                }
                $tracker->updatePriority($actor->id(), (string) $issue->external_issue_id, (int) $issue->priority_id);
            }
        } catch (LogicException) {
            return response()->json(['message' => 'Priority sync is not implemented for this tracker.'], 501);
        }

        return response()->json(['message' => 'Priority sync requested.']);
    }

    public function exportCsv(
        Sprint $sprint,
        UserResolver $resolver,
        SprintService $sprints,
    ): StreamedResponse {
        $sprint = $sprints->findForView($this->actor($resolver), $sprint->getKey());

        return response()->streamDownload(function () use ($sprint): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'tracker', 'external_project_id', 'external_issue_id', 'added_by', 'priority_id', 'completion_status']);
            foreach ($sprint->issues as $issue) {
                fputcsv($out, [
                    $issue->id,
                    $issue->tracker,
                    $issue->external_project_id,
                    $issue->external_issue_id,
                    $issue->added_by,
                    $issue->priority_id,
                    $issue->completion_status?->value ?? $issue->completion_status,
                ]);
            }
            fclose($out);
        }, 'sprint-'.$sprint->id.'-issues.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function exportSummary(
        Sprint $sprint,
        UserResolver $resolver,
        SprintService $sprints,
        SprintDashboardService $dashboard,
    ): Response {
        $actor = $this->actor($resolver);
        $sprint = $sprints->findForView($actor, $sprint->getKey());
        $payload = $dashboard->build($actor, $sprint);
        $progress = $payload['widgets']['progress'];

        $text = "Sprint: {$sprint->name}\n"
            .'Status: '.($sprint->status?->value ?? $sprint->status)."\n"
            ."Issues: {$progress['completed']}/{$progress['total']} completed\n";

        return response($text, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
