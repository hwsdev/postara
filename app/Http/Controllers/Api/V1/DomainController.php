<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Services\DkimService;
use App\Services\DomainVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function __construct(
        private readonly DkimService $dkimService,
        private readonly DomainVerifier $domainVerifier
    ) {}

    public function index(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        return response()->json(
            Domain::where('workspace_id', $workspace->id)->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        $request->validate([
            'domain' => ['required', 'string', 'regex:/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'],
        ]);

        $domain = Domain::create([
            'workspace_id' => $workspace->id,
            'domain' => strtolower($request->domain),
            'status' => 'pending',
        ]);

        $domain = $this->dkimService->generateKeypair($domain);

        return response()->json([
            'id' => $domain->id,
            'domain' => $domain->domain,
            'status' => $domain->status,
            'dns_records' => [
                'spf' => [
                    'type' => 'TXT',
                    'host' => $domain->domain,
                    'value' => $domain->getSpfRecord(),
                ],
                'dkim' => [
                    'type' => 'TXT',
                    'host' => "{$domain->dkim_selector}._domainkey.{$domain->domain}",
                    'value' => $domain->getDkimRecord(),
                ],
                'dmarc' => [
                    'type' => 'TXT',
                    'host' => "_dmarc.{$domain->domain}",
                    'value' => $domain->getDmarcRecord(),
                ],
            ],
        ], 201);
    }

    public function verify(Request $request, Domain $domain): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        if ($domain->workspace_id !== $workspace->id) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $results = $this->domainVerifier->verify($domain);

        return response()->json([
            'domain' => $domain->domain,
            'status' => $domain->fresh()->status,
            'checks' => $results,
        ]);
    }

    public function destroy(Request $request, Domain $domain): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        if ($domain->workspace_id !== $workspace->id) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $domain->delete();

        return response()->json(null, 204);
    }
}
