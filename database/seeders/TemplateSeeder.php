<?php

namespace Database\Seeders;

use App\Models\Template;
use App\Models\Workspace;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Seed into all existing workspaces
        $workspaces = Workspace::all();

        if ($workspaces->isEmpty()) {
            $this->command->warn('No workspaces found. Run setup wizard first.');
            return;
        }

        foreach ($workspaces as $workspace) {
            foreach ($this->templates() as $tpl) {
                Template::updateOrCreate(
                    [
                        'workspace_id' => $workspace->id,
                        'name'         => $tpl['name'],
                    ],
                    [
                        'subject'     => $tpl['subject'],
                        'html'        => $tpl['html'],
                        'type'        => $tpl['type'],
                        'design_json' => null,
                    ]
                );
            }

            $this->command->info("Seeded " . count($this->templates()) . " templates into workspace: {$workspace->name}");
        }
    }

    private function templates(): array
    {
        return [
            [
                'name'    => 'Welcome Email',
                'subject' => 'Welcome to {{ $appName }}!',
                'type'    => 'transactional',
                'html'    => $this->welcomeHtml(),
            ],
            [
                'name'    => 'Password Reset',
                'subject' => 'Reset your password',
                'type'    => 'transactional',
                'html'    => $this->passwordResetHtml(),
            ],
            [
                'name'    => 'Order Confirmation',
                'subject' => 'Your order #{{ $orderId }} is confirmed',
                'type'    => 'transactional',
                'html'    => $this->orderConfirmationHtml(),
            ],
            [
                'name'    => 'Newsletter',
                'subject' => '{{ $subject }}',
                'type'    => 'campaign',
                'html'    => $this->newsletterHtml(),
            ],
            [
                'name'    => 'Promotional Offer',
                'subject' => 'Exclusive offer just for you 🎁',
                'type'    => 'campaign',
                'html'    => $this->promoHtml(),
            ],
        ];
    }

    // ── Templates ──────────────────────────────────────────────────────

    private function welcomeHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Welcome</title>
<style>
  body{margin:0;padding:0;background:#f6f6f6;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border:1px solid #eee}
  .header{background:#0A0A0A;padding:28px 40px;text-align:center}
  .logo{color:#fff;font-size:20px;font-weight:700;letter-spacing:-0.02em;text-decoration:none}
  .body{padding:40px}
  .body h1{margin:0 0 8px;font-size:24px;font-weight:700;color:#0A0A0A;letter-spacing:-0.01em}
  .body p{margin:0 0 16px;font-size:15px;line-height:1.6;color:#454545}
  .btn{display:inline-block;background:#0A0A0A;color:#fff;text-decoration:none;padding:13px 28px;font-size:14px;font-weight:600;border-radius:4px;margin:8px 0}
  .divider{border:none;border-top:1px solid #eee;margin:28px 0}
  .footer{padding:24px 40px;border-top:1px solid #eee}
  .footer p{margin:0;font-size:12px;color:#c9c9c9;line-height:1.5}
  .footer a{color:#757575;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <span class="logo">{{ $appName }}</span>
  </div>
  <div class="body">
    <h1>Welcome, {{ $name }}! 👋</h1>
    <p>We're thrilled to have you on board. Your account is ready — here's everything you need to get started.</p>
    <p>
      <a href="{{ $dashboardUrl }}" class="btn">Go to dashboard</a>
    </p>
    <hr class="divider">
    <p style="font-size:13px;color:#757575">If you have any questions, just reply to this email — we're always happy to help.</p>
  </div>
  <div class="footer">
    <p>You received this email because you signed up for {{ $appName }}.<br>
    <a href="{{ $unsubscribeUrl }}">Unsubscribe</a> · <a href="{{ $privacyUrl }}">Privacy policy</a></p>
  </div>
</div>
</body>
</html>
HTML;
    }

    private function passwordResetHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset your password</title>
<style>
  body{margin:0;padding:0;background:#f6f6f6;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border:1px solid #eee}
  .header{background:#0A0A0A;padding:28px 40px}
  .logo{color:#fff;font-size:18px;font-weight:700;letter-spacing:-0.02em}
  .body{padding:40px}
  .body h1{margin:0 0 8px;font-size:22px;font-weight:700;color:#0A0A0A}
  .body p{margin:0 0 16px;font-size:15px;line-height:1.6;color:#454545}
  .btn{display:inline-block;background:#0A0A0A;color:#fff;text-decoration:none;padding:13px 28px;font-size:14px;font-weight:600;border-radius:4px;margin:8px 0}
  .warning{background:#fff8e1;border:1px solid #ffe082;border-radius:6px;padding:14px 18px;margin:20px 0}
  .warning p{margin:0;font-size:13px;color:#7c6200}
  .footer{padding:24px 40px;border-top:1px solid #eee}
  .footer p{margin:0;font-size:12px;color:#c9c9c9;line-height:1.5}
  .footer a{color:#757575;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <span class="logo">{{ $appName }}</span>
  </div>
  <div class="body">
    <h1>Reset your password</h1>
    <p>Hi {{ $name }}, we received a request to reset the password for your account.</p>
    <p>
      <a href="{{ $resetUrl }}" class="btn">Reset password</a>
    </p>
    <div class="warning">
      <p>⏱ This link expires in <strong>{{ $expiresIn }}</strong>. If you didn't request a password reset, you can safely ignore this email.</p>
    </div>
    <p style="font-size:13px;color:#757575">For security, this request was received from <strong>{{ $ipAddress }}</strong>. If this wasn't you, please contact support immediately.</p>
  </div>
  <div class="footer">
    <p>{{ $appName }} · <a href="{{ $unsubscribeUrl }}">Unsubscribe</a></p>
  </div>
</div>
</body>
</html>
HTML;
    }

    private function orderConfirmationHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Order Confirmed</title>
<style>
  body{margin:0;padding:0;background:#f6f6f6;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border:1px solid #eee}
  .header{background:#0A0A0A;padding:28px 40px;display:flex;align-items:center;justify-content:space-between}
  .logo{color:#fff;font-size:18px;font-weight:700;letter-spacing:-0.02em}
  .badge{background:#06A763;color:#fff;font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;letter-spacing:0.04em;text-transform:uppercase}
  .body{padding:40px}
  .body h1{margin:0 0 4px;font-size:22px;font-weight:700;color:#0A0A0A}
  .body p{margin:0 0 16px;font-size:15px;line-height:1.6;color:#454545}
  .order-box{background:#f6f6f6;border-radius:6px;padding:20px 24px;margin:20px 0}
  .order-row{display:flex;justify-content:space-between;font-size:14px;padding:6px 0;border-bottom:1px solid #eee}
  .order-row:last-child{border-bottom:none;font-weight:700;font-size:15px;padding-top:12px}
  .order-label{color:#757575}
  .order-value{color:#0A0A0A;font-weight:500}
  .btn{display:inline-block;background:#0A0A0A;color:#fff;text-decoration:none;padding:13px 28px;font-size:14px;font-weight:600;border-radius:4px;margin:8px 0}
  .footer{padding:24px 40px;border-top:1px solid #eee}
  .footer p{margin:0;font-size:12px;color:#c9c9c9;line-height:1.5}
  .footer a{color:#757575;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <span class="logo">{{ $appName }}</span>
    <span class="badge">Confirmed</span>
  </div>
  <div class="body">
    <h1>Order confirmed!</h1>
    <p>Hi {{ $name }}, thank you for your order. We'll send you a shipping confirmation once your order is on its way.</p>
    <div class="order-box">
      <div class="order-row">
        <span class="order-label">Order number</span>
        <span class="order-value">#{{ $orderId }}</span>
      </div>
      <div class="order-row">
        <span class="order-label">Date</span>
        <span class="order-value">{{ $orderDate }}</span>
      </div>
      <div class="order-row">
        <span class="order-label">Items</span>
        <span class="order-value">{{ $itemCount }}</span>
      </div>
      <div class="order-row">
        <span class="order-label">Total</span>
        <span class="order-value">{{ $total }}</span>
      </div>
    </div>
    <p>
      <a href="{{ $trackingUrl }}" class="btn">Track your order</a>
    </p>
  </div>
  <div class="footer">
    <p>Questions? Reply to this email or visit our <a href="{{ $supportUrl }}">help center</a>.<br>
    {{ $appName }} · <a href="{{ $unsubscribeUrl }}">Unsubscribe</a></p>
  </div>
</div>
</body>
</html>
HTML;
    }

    private function newsletterHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Newsletter</title>
<style>
  body{margin:0;padding:0;background:#f6f6f6;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
  .wrap{max-width:600px;margin:32px auto;background:#fff;border:1px solid #eee}
  .header{background:#0A0A0A;padding:32px 48px;text-align:center}
  .logo{color:#fff;font-size:20px;font-weight:700;letter-spacing:-0.02em}
  .issue{color:rgba(255,255,255,0.4);font-size:12px;margin-top:4px;letter-spacing:0.06em;text-transform:uppercase}
  .hero{padding:48px 48px 32px;border-bottom:1px solid #eee}
  .hero-label{font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;color:#757575;margin:0 0 12px}
  .hero h1{margin:0 0 16px;font-size:28px;font-weight:700;color:#0A0A0A;line-height:1.2;letter-spacing:-0.02em}
  .hero p{margin:0 0 20px;font-size:16px;line-height:1.6;color:#454545}
  .btn{display:inline-block;background:#0A0A0A;color:#fff;text-decoration:none;padding:13px 28px;font-size:14px;font-weight:600;border-radius:4px}
  .section{padding:32px 48px;border-bottom:1px solid #eee}
  .section h2{margin:0 0 12px;font-size:18px;font-weight:700;color:#0A0A0A;letter-spacing:-0.01em}
  .section p{margin:0 0 16px;font-size:14px;line-height:1.6;color:#454545}
  .section a{color:#0A0A0A;font-weight:600}
  .footer{padding:28px 48px;text-align:center}
  .footer p{margin:0 0 8px;font-size:12px;color:#c9c9c9;line-height:1.5}
  .footer a{color:#757575;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <div class="logo">{{ $appName }}</div>
    <div class="issue">{{ $issueLabel }}</div>
  </div>
  <div class="hero">
    <p class="hero-label">Featured</p>
    <h1>{{ $heroTitle }}</h1>
    <p>{{ $heroBody }}</p>
    <a href="{{ $heroUrl }}" class="btn">{{ $heroCta }}</a>
  </div>
  <div class="section">
    <h2>{{ $section1Title }}</h2>
    <p>{{ $section1Body }}</p>
    <a href="{{ $section1Url }}">Read more →</a>
  </div>
  <div class="section">
    <h2>{{ $section2Title }}</h2>
    <p>{{ $section2Body }}</p>
    <a href="{{ $section2Url }}">Read more →</a>
  </div>
  <div class="footer">
    <p>You're receiving this because you subscribed to {{ $appName }} updates.</p>
    <p><a href="{{ $unsubscribeUrl }}">Unsubscribe</a> · <a href="{{ $preferencesUrl }}">Manage preferences</a></p>
  </div>
</div>
</body>
</html>
HTML;
    }

    private function promoHtml(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Special Offer</title>
<style>
  body{margin:0;padding:0;background:#f6f6f6;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif}
  .wrap{max-width:560px;margin:32px auto;background:#fff;border:1px solid #eee}
  .header{background:#0A0A0A;padding:28px 40px;text-align:center}
  .logo{color:#fff;font-size:18px;font-weight:700;letter-spacing:-0.02em}
  .hero{background:#0A0A0A;padding:48px 40px;text-align:center}
  .hero-tag{display:inline-block;background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.7);font-size:11px;font-weight:700;letter-spacing:0.1em;text-transform:uppercase;padding:5px 14px;border-radius:20px;margin-bottom:20px}
  .hero h1{margin:0 0 12px;font-size:36px;font-weight:700;color:#fff;letter-spacing:-0.02em;line-height:1.1}
  .hero p{margin:0;font-size:16px;color:rgba(255,255,255,0.6);line-height:1.5}
  .offer{padding:36px 40px;text-align:center;border-bottom:1px solid #eee}
  .offer p{margin:0 0 20px;font-size:15px;line-height:1.6;color:#454545}
  .code-box{background:#f6f6f6;border:2px dashed #eee;border-radius:8px;padding:20px;margin:20px 0;text-align:center}
  .code-label{font-size:12px;color:#757575;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:8px}
  .code{font-family:'JetBrains Mono',monospace;font-size:24px;font-weight:700;color:#0A0A0A;letter-spacing:0.1em}
  .btn{display:inline-block;background:#0A0A0A;color:#fff;text-decoration:none;padding:14px 32px;font-size:15px;font-weight:700;border-radius:4px}
  .expiry{font-size:12px;color:#c9c9c9;margin-top:16px}
  .footer{padding:24px 40px;border-top:1px solid #eee;text-align:center}
  .footer p{margin:0;font-size:12px;color:#c9c9c9;line-height:1.5}
  .footer a{color:#757575;text-decoration:none}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <span class="logo">{{ $appName }}</span>
  </div>
  <div class="hero">
    <div class="hero-tag">Limited time offer</div>
    <h1>{{ $discountPercent }}% off</h1>
    <p>{{ $heroSubtitle }}</p>
  </div>
  <div class="offer">
    <p>Hi {{ $name }}, we're giving you an exclusive discount as a thank you for being with us.</p>
    <div class="code-box">
      <div class="code-label">Your promo code</div>
      <div class="code">{{ $promoCode }}</div>
    </div>
    <a href="{{ $shopUrl }}" class="btn">Shop now</a>
    <p class="expiry">Offer expires {{ $expiryDate }}</p>
  </div>
  <div class="footer">
    <p>{{ $appName }} · <a href="{{ $unsubscribeUrl }}">Unsubscribe</a></p>
  </div>
</div>
</body>
</html>
HTML;
    }
}
