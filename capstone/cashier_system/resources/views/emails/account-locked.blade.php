<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Locked</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; padding: 0; margin: 0;">

    <div style="
        max-width: 480px;
        margin: 40px auto;
        background: #ffffff;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    ">
        
        <h2 style="text-align: center; color: #b30000; margin-bottom: 20px;">
            PUPTeC Security Notice
        </h2>

        <p style="font-size: 16px; color: #333;">
            Hello,
        </p>

        <p style="font-size: 16px; color: #555;">
            Your account has been temporarily locked due to multiple unsuccessful login attempts.
        </p>

        <!-- Message Box -->
        <div style="
            font-size: 18px;
            font-weight: bold;
            background: #f9ecec;
            border: 2px solid #b30000;
            color: #b30000;
            padding: 15px 20px;
            border-radius: 10px;
            text-align: center;
            margin: 25px 0;
        ">
            Login access is disabled for your security.
        </div>

        <p style="font-size: 15px; color: #555;">
            To regain access, please reset your password using the “Forgot Password” option on the login page.
        </p>

        <p style="font-size: 15px; color: #777;">
            If this was not you, we strongly recommend updating your password immediately once access is restored.
        </p>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">

        <p style="text-align: center; font-size: 14px; color: #aaa;">
            &copy; {{ date('Y') }} PUPTeC. All rights reserved.
        </p>
    </div>

</body>
</html>
