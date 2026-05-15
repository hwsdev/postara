<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        $contacts = Contact::where('workspace_id', $workspace->id)
            ->latest()
            ->paginate(100);

        return response()->json([
            'data' => $contacts->items(),
            'meta' => [
                'total' => $contacts->total(),
                'per_page' => $contacts->perPage(),
                'current_page' => $contacts->currentPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        $validated = $request->validate([
            'email'         => ['required', 'email'],
            'name'          => ['sometimes', 'string', 'max:255'],
            'custom_fields' => ['sometimes', 'array'],
            'tags'          => ['sometimes', 'array'],
            'subscribed'    => ['sometimes', 'boolean'],
        ]);

        $contact = Contact::updateOrCreate(
            ['workspace_id' => $workspace->id, 'email' => $validated['email']],
            $validated
        );

        return response()->json($contact, $contact->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, Contact $contact): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        if ($contact->workspace_id !== $workspace->id) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $contact->delete();

        return response()->json(null, 204);
    }
}
