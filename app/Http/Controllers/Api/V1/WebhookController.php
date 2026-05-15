<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Webhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    private const VALID_EVENTS = [
        'email.delivered',
        'email.opened',
        'email.clicked',
        'email.bounced',
        'email.complained',
    ];

    public function index(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        return response()->json(
            Webhook::where('workspace_id', $workspace->id)->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:255'],
            'url'    => ['required', 'url'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', 'in:'.implode(',', self::VALID_EVENTS)],
        ]);

        $webhook = Webhook::create([
            'workspace_id' => $workspace->id,
            'name' => $validated['name'],
            'url' => $validated['url'],
            'events' => $validated['events'],
            'secret' => Str::random(32),
            'active' => true,
        ]);

        return response()->json($webhook->makeVisible('secret'), 201);
    }

    public function update(Request $request, Webhook $webhook): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        if ($webhook->workspace_id !== $workspace->id) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $validated = $request->validate([
            'name'   => ['sometimes', 'string', 'max:255'],
            'url'    => ['sometimes', 'url'],
            'events' => ['sometimes', 'array', 'min:1'],
            'events.*' => ['string', 'in:'.implode(',', self::VALID_EVENTS)],
            'active' => ['sometimes', 'boolean'],
        ]);

        $webhook->update($validated);

        return response()->json($webhook);
    }

    public function destroy(Request $request, Webhook $webhook): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        if ($webhook->workspace_id !== $workspace->id) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $webhook->delete();

        return response()->json(null, 204);
    }

    public function rotateSecret(Request $request, Webhook $webhook): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        if ($webhook->workspace_id !== $workspace->id) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $webhook->update(['secret' => Str::random(32)]);

        return response()->json($webhook->makeVisible('secret'));
    }
}
