<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectIngestKey
{
    /**
     * Verify that the request carries a valid project ingest key.
     *
     * The middleware keeps authentication intentionally simple for the MVP: a project-scoped
     * shared secret in a header. That makes the sender integration easy to copy into many apps,
     * while still allowing a future move to HMAC signatures or more granular auth.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ingestHeader = (string) config('notifyhub.security.ingest_header', 'X-Project-Key');
        $ingestKey = $request->header($ingestHeader);

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

    /**
     * Return a consistent JSON error for unauthorized ingestion attempts.
     */
    protected function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Invalid or missing project ingest key.',
        ], Response::HTTP_UNAUTHORIZED);
    }
}
