<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class TrackingController extends Controller
{
    public function __construct(
        private readonly TrackingService $trackingService
    ) {}

    /**
     * Track email open via 1x1 pixel.
     */
    public function open(Request $request, Email $email): Response
    {
        $this->trackingService->record(
            $email,
            'opened',
            [],
            $request->ip(),
            $request->userAgent()
        );

        // Return a 1x1 transparent GIF
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($pixel, 200, [
            'Content-Type' => 'image/gif',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * Track link click and redirect.
     */
    public function click(Request $request, Email $email): SymfonyResponse
    {
        $url = base64_decode($request->query('url', ''));

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid redirect URL.');
        }

        $this->trackingService->record(
            $email,
            'clicked',
            ['url' => $url],
            $request->ip(),
            $request->userAgent()
        );

        return redirect()->away($url);
    }
}
