<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $greeting ?? 'Notifikasi' }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0fdf4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #059669, #10b981);
            padding: 32px 40px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .body {
            padding: 32px 40px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 16px;
        }
        .message {
            font-size: 15px;
            line-height: 1.7;
            color: #4b5563;
            margin-bottom: 24px;
        }
        .action-button {
            display: inline-block;
            background: #059669;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: background 0.2s;
        }
        .action-button:hover {
            background: #047857;
        }
        .footer {
            padding: 24px 40px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CoFund</h1>
        </div>
        <div class="body">
            <div class="greeting">{{ $greeting ?? 'Halo!' }}</div>
            <div class="message">{!! nl2br(e($messageContent ?? 'Ini adalah email otomatis.')) !!}</div>
            @if(isset($actionText) && isset($actionUrl))
                <div style="text-align: center; margin-top: 24px;">
                    <a href="{{ $actionUrl }}" class="action-button">{{ $actionText }}</a>
                </div>
            @endif
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} CoFund. Semua hak dilindungi.</p>
            <p style="margin-top: 4px;">Email ini dikirim secara otomatis. Mohon tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>
