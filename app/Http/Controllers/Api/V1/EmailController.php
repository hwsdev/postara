<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SendEmailRequest;
use App\Models\Email;
use App\Models\Template;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailController extends Controller
{
    public function __construct(
        private readonly EmailService $emailService
    ) {}

    /**
     * POST /v1/emails
     * Send a transactional email.
     */
    public function send(SendEmailRequest $request): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');
        $data = $request->validated();

        // Resolve template if provided
        if (! empty($data['template_id'])) {
            $template = Template::where('workspace_id', $workspace->id)
                ->where('slug', $data['template_id'])
                ->orWhere('id', $data['template_id'])
                ->firstOrFail();

            $data['html'] = $this->emailService->renderTemplate($template, $data['variables'] ?? []);
            $data['subject'] = $data['subject'] ?? $template->subject;
            $data['template_id'] = $template->id;
        }

        try {
            $email = $this->emailService->send($workspace, $data);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'id' => 'em_'.$email->id,
            'status' => $email->status,
        ], 201);
    }

    /**
     * GET /v1/emails/{id}
     * Retrieve a sent email.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        // Strip em_ prefix if present
        $id = ltrim($id, 'em_');

        $email = Email::where('workspace_id', $workspace->id)
            ->findOrFail($id);

        return response()->json($this->formatEmail($email));
    }

    /**
     * GET /v1/emails
     * List emails with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $workspace = $request->attributes->get('workspace');

        $emails = Email::where('workspace_id', $workspace->id)
            ->latest()
            ->paginate(50);

        return response()->json([
            'data' => $emails->map(fn ($e) => $this->formatEmail($e)),
            'meta' => [
                'total' => $emails->total(),
                'per_page' => $emails->perPage(),
                'current_page' => $emails->currentPage(),
                'last_page' => $emails->lastPage(),
            ],
        ]);
    }

    private function formatEmail(Email $email): array
    {
        return [
            'id' => 'em_'.$email->id,
            'from' => $email->from,
            'to' => $email->to,
            'subject' => $email->subject,
            'status' => $email->status,
            'tags' => $email->tags,
            'created_at' => $email->created_at->toIso8601String(),
        ];
    }
}
