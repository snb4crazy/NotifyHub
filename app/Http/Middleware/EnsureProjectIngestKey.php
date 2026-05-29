<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectIngestKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $ingestKey = $request->header('X-Project-Key');

        if (! is_string($ingestKey) || $ingestKey === '') {
            return $this->unauthorizedResponse();
        }

        $project = Project::query()
            ->where('ingest_key', $ingestKey)
            ->first();

        if (! $project) {
            return $this->unauthorizedResponse();
        }

        $request->attributes->set('ingestProject', $project);

        return $next($request);
    }

    protected function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Invalid or missing project ingest key.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}

