<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test email from {{ $appName }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f6f6f6; font-family: Inter, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border: 1px solid #eeeeee; }
        .header { background: #0A0A0A; padding: 28px 40px; }
        .header-logo { display: inline-flex; align-items: center; gap: 10px; text-decoration: none; }
        .header-logo-icon { width: 24px; height: 24px; background: #ffffff; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; }
        .header-logo-text { color: #ffffff; font-size: 16px; font-weight: 700; letter-spacing: -0.02em; }
        .body { padding: 40px; }
        .body h1 { margin: 0 0 8px; font-size: 22px; font-weight: 700; color: #0A0A0A; letter-spacing: -0.01em; }
        .body p { margin: 0 0 16px; font-size: 15px; line-height: 1.6; color: #454545; }
        .check-list { margin: 24px 0; padding: 20px 24px; background: #f6f6f6; border-left: 3px solid #0A0A0A; }
        .check-list p { margin: 0 0 8px; font-size: 13px; color: #757575; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; }
        .check-list ul { margin: 0; padding: 0 0 0 16px; }
        .check-list li { font-size: 14px; color: #454545; margin-bottom: 6px; line-height: 1.5; }
        .footer { padding: 24px 40px; border-top: 1px solid #eeeeee; }
        .footer p { margin: 0; font-size: 12px; color: #c9c9c9; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <div class="header-logo">
                <div class="header-logo-icon">
                    <svg width="14" height="14" viewBox="0 0 16 16" fill="#0A0A0A">
                        <path d="M2 2h12v2H2V2zm0 4h8v2H2V6zm0 4h10v2H2v-2z"/>
                    </svg>
                </div>
                <span class="header-logo-text">{{ $appName }}</span>
            </div>
        </div>

        <div class="body">
            <h1>Your mail config works.</h1>
            <p>Hi {{ $toName }},</p>
            <p>
                This is a test email sent from <strong>{{ $appName }}</strong> to confirm that your mail configuration is set up correctly and emails are being delivered.
            </p>

            <div class="check-list">
                <p>What this confirms</p>
                <ul>
                    <li>Your SMTP / mail transport is reachable</li>
                    <li>The from address and credentials are valid</li>
                    <li>Emails can be delivered to external inboxes</li>
                </ul>
            </div>

            <p>
                If you received this, you're good to go. You can now send transactional emails via the API or create a campaign from the dashboard.
            </p>
            <p style="color: #757575; font-size: 13px;">
                Sent at {{ now()->format('D, d M Y H:i:s T') }}
            </p>
        </div>

        <div class="footer">
            <p>
                This is an automated test email from your {{ $appName }} instance.<br>
                You can safely ignore or delete this message.
            </p>
        </div>
    </div>
</body>
</html>
