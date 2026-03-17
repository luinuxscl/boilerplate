<?php

namespace App\Http\Middleware;

use App\Services\ApiKeys\ApiKeyManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function __construct(private readonly ApiKeyManager $manager) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken() ?? $request->header('X-Api-Key');

        if (! $plain) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        $apiKey = $this->manager->findByPlain($plain);

        if (! $apiKey) {
            return response()->json(['message' => 'Invalid API key.'], Response::HTTP_UNAUTHORIZED);
        }

        if ($apiKey->isExpired()) {
            return response()->json(['message' => 'API key has expired.'], Response::HTTP_UNAUTHORIZED);
        }

        $this->manager->touchLastUsed($apiKey);

        $request->attributes->set('api_key', $apiKey);
        $request->setUserResolver(fn () => $apiKey->user);

        return $next($request);
    }
}
