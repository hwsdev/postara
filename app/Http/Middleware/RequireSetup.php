<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireSetup
{
    /** Routes that are always accessible regardless of setup state. */
    private const ALWAYS_ALLOWED = [
        'setup*',
        'livewire*',
        'up',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Skip for API routes — they fail gracefully if not configured
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Skip for the setup routes themselves
        foreach (self::ALWAYS_ALLOWED as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        if (! Setting::isSetupComplete()) {
            return redirect()->route('setup');
        }

        return $next($request);
    }
}
