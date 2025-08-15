<!DOCTYPE html>
<html>
<head>
    <title>OTP Code</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 400px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 32px 24px;
            text-align: center;
        }
        .otp {
            font-size: 2em;
            color: #0078d4;
            letter-spacing: 4px;
            margin: 16px 0;
        }
        .footer {
            margin-top: 24px;
            font-size: 0.95em;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Welcome to ChatZone!</h2>
        <p>Please use the following OTP code to verify your account:</p>
        <div class="otp">{{ $otp }}</div>
        <p class="footer">If you did not request this code, please ignore this email.</p>
    </div>
</body>
</html>
