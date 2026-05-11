<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow Notification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f3f4f6; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { background: #4f46e5; padding: 32px 40px; text-align: center; }
        .header-title { color: white; font-size: 22px; font-weight: 700; }
        .header-sub { color: rgba(255,255,255,0.7); font-size: 13px; margin-top: 4px; }
        .body { padding: 40px; }
        .greeting { font-size: 18px; font-weight: 600; color: #111827; margin-bottom: 16px; }
        .content { font-size: 15px; color: #4b5563; line-height: 1.7; margin-bottom: 16px; }
        .btn-wrap { text-align: center; margin: 32px 0; }
        .btn { display: inline-block; background: #4f46e5; color: white; text-decoration: none; padding: 14px 36px; border-radius: 10px; font-weight: 600; font-size: 15px; }
        .divider { border: none; border-top: 1px solid #e5e7eb; margin: 28px 0; }
        .small { font-size: 13px; color: #9ca3af; line-height: 1.6; }
        .footer { background: #f9fafb; padding: 24px 40px; text-align: center; font-size: 13px; color: #9ca3af; border-top: 1px solid #e5e7eb; }
    </style>
</head>
<body>
<div class="wrapper">

    <div class="header">
        <div class="header-title">🏠 EstateFlow</div>
        <div class="header-sub">Real Estate Management Platform</div>
    </div>

    <div class="body">

        @foreach ($introLines as $line)
            @if($loop->first)
                <p class="greeting">{{ $line }}</p>
            @else
                <p class="content">{{ $line }}</p>
            @endif
        @endforeach

        @isset($actionText)
        <div class="btn-wrap">
            <a href="{{ $actionUrl }}" class="btn">{{ $actionText }}</a>
        </div>
        @endisset

        @foreach ($outroLines as $line)
            <p class="content">{{ $line }}</p>
        @endforeach

        <hr class="divider">

        @isset($actionText)
        <p class="small">
            If you're having trouble clicking the "{{ $actionText }}" button, copy and paste the URL below into your web browser:<br>
            <a href="{{ $actionUrl }}" style="color: #6366f1; word-break: break-all;">{{ $actionUrl }}</a>
        </p>
        @endisset

    </div>

    <div class="footer">
        <p>© {{ date('Y') }} EstateFlow. All rights reserved.</p>
        <p>If you did not request this email, no action is required.</p>
    </div>

</div>
</body>
</html>
