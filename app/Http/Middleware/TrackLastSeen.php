<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $userId = auth()->id();
            // Only update DB once per minute using cache to avoid hammering the DB
            if (!Cache::has("last_seen_{$userId}")) {
                auth()->user()->updateQuietly(['last_seen_at' => now()]);
                Cache::put("last_seen_{$userId}", true, 60);
            }
        }

        return $next($request);
    }
}
