<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Services\ApiKeys\ApiRateLimiter as RateLimiterService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRateLimiter
{
    public function __construct(private readonly RateLimiterService $limiter) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var ApiKey|null $apiKey */
        $apiKey = $request->attributes->get('api_key');

        if (! $apiKey) {
            return $next($request);
        }

        if (! $this->limiter->attempt($apiKey)) {
            return response()->json(
                ['message' => 'Too many requests.'],
                Response::HTTP_TOO_MANY_REQUESTS,
                ['Retry-After' => 60, 'X-RateLimit-Limit' => $apiKey->rate_limit_per_minute, 'X-RateLimit-Remaining' => 0],
            );
        }

        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', (string) $apiKey->rate_limit_per_minute);
        $response->headers->set('X-RateLimit-Remaining', (string) $this->limiter->remaining($apiKey));

        return $response;
    }
}
