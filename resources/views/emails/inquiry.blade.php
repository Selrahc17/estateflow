<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #1f2937; background: #f9fafb; margin: 0; padding: 0; }
        .wrap { max-width: 560px; margin: 32px auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
        .header { background: #4f46e5; padding: 24px 32px; }
        .header h1 { color: white; font-size: 18px; margin: 0; }
        .header p { color: #c7d2fe; font-size: 12px; margin: 4px 0 0; }
        .body { padding: 28px 32px; }
        .field { margin-bottom: 16px; }
        .label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; margin-bottom: 4px; }
        .value { font-size: 14px; color: #111827; background: #f3f4f6; padding: 10px 14px; border-radius: 8px; }
        .message-value { white-space: pre-wrap; }
        .footer { background: #f9fafb; padding: 16px 32px; text-align: center; font-size: 11px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>📬 New Inquiry Received</h1>
        <p>EstateFlow — Public Contact Form</p>
    </div>
    <div class="body">
        <div class="field">
            <div class="label">From</div>
            <div class="value">{{ $name }}</div>
        </div>
        <div class="field">
            <div class="label">Email</div>
            <div class="value">{{ $email }}</div>
        </div>
        @if(!empty($phone))
        <div class="field">
            <div class="label">Phone</div>
            <div class="value">{{ $phone }}</div>
        </div>
        @endif
        <div class="field">
            <div class="label">Subject</div>
            <div class="value">{{ $subject }}</div>
        </div>
        <div class="field">
            <div class="label">Message</div>
            <div class="value message-value">{{ $message }}</div>
        </div>
    </div>
    <div class="footer">
        Sent via EstateFlow public inquiry form &nbsp;|&nbsp; {{ now()->format('F j, Y g:i A') }}
    </div>
</div>
</body>
</html>
