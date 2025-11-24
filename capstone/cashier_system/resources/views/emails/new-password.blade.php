<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; padding: 0; margin: 0;">

    <div style="max-width: 480px; margin: 40px auto; background: #ffffff; border-radius: 12px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
        
        <h2 style="text-align: center; color: #b30000; margin-bottom: 20px;">Password Reset Request</h2>

        <p style="font-size: 16px; color: #333;">
            Hello {{ $user->name ?? 'User' }},
        </p>

        <p style="font-size: 16px; color: #555;">
            You requested to reset your password. Please use the OTP below to proceed:
        </p>

        <!-- OTP Box -->
        <div style="
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 6px;
            background: #f9ecec;
            border: 2px solid #b30000;
            color: #b30000;
            padding: 15px 0;
            border-radius: 10px;
            text-align: center;
            margin: 25px 0;
        ">
            {{ $otpCode }}
        </div>

        <p style="font-size: 15px; color: #777;">
            This OTP will expire in <strong>5 minutes</strong>.
        </p>

        <p style="font-size: 15px; color: #777;">
            If you did not request a password reset, you can ignore this email safely.
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">

        <p style="text-align: center; font-size: 14px; color: #aaa;
