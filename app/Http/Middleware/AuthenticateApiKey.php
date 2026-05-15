<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json(['error' => 'Missing API key.'], 401);
        }

        // API keys are stored as bcrypt hashes; we prefix with first 8 chars for lookup
        $prefix = substr($token, 0, 8);

        $apiKey = ApiKey::where('key_prefix', $prefix)->first();

        if (! $apiKey || ! \Hash::check($token, $apiKey->key_hash)) {
            return response()->json(['error' => 'Invalid API key.'], 401);
        }

        if ($apiKey->isExpired()) {
            return response()->json(['error' => 'API key has expired.'], 401);
        }

        $apiKey->update(['last_used_at' => now()]);

        $request->merge(['_workspace' => $apiKey->workspace]);
        $request->attributes->set('workspace', $apiKey->workspace);
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
