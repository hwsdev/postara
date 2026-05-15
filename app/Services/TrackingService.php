<?php

namespace App\Services;

use App\Models\Email;
use App\Models\EmailEvent;
use Illuminate\Support\Facades\URL;

class TrackingService
{
    /**
     * Inject tracking pixel and rewrite links in HTML.
     */
    public function instrument(Email $email, string $html): string
    {
        $html = $this->injectTrackingPixel($email, $html);
        $html = $this->rewriteLinks($email, $html);

        return $html;
    }

    /**
     * Inject a 1x1 transparent tracking pixel before </body>.
     */
    private function injectTrackingPixel(Email $email, string $html): string
    {
        $pixelUrl = URL::signedRoute('track.open', ['email' => $email->id]);
        $pixel = '<img src="'.e($pixelUrl).'" width="1" height="1" alt="" style="display:none;" />';

        return str_replace('</body>', $pixel.'</body>', $html);
    }

    /**
     * Rewrite all <a href> links to go through click tracking.
     */
    private function rewriteLinks(Email $email, string $html): string
    {
        return preg_replace_callback(
            '/<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>/i',
            function ($matches) use ($email) {
                $originalUrl = $matches[1];

                // Skip unsubscribe links and anchors
                if (str_starts_with($originalUrl, '#') || str_contains($originalUrl, 'unsubscribe')) {
                    return $matches[0];
                }

                $trackUrl = URL::signedRoute('track.click', [
                    'email' => $email->id,
                    'url' => base64_encode($originalUrl),
                ]);

                return str_replace($originalUrl, $trackUrl, $matches[0]);
            },
            $html
        );
    }

    /**
     * Record an email event.
     */
    public function record(Email $email, string $type, array $data = [], ?string $ip = null, ?string $userAgent = null): EmailEvent
    {
        $event = EmailEvent::create([
            'email_id' => $email->id,
            'type' => $type,
            'data' => $data,
            'ip' => $ip,
            'user_agent' => $userAgent,
        ]);

        // Update email status for terminal events
        if (in_array($type, ['delivered', 'bounced', 'complained'])) {
            $email->update(['status' => $type]);
        }

        return $event;
    }
}
