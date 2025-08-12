<p>Hello {{ $user->name ?? 'User' }},</p>

<p>You requested to reset your password. Please use the OTP below to proceed:</p>

<h2 style="color: #2d3748;">{{ $otpCode }}</h2>

<p>This OTP will expire in <strong>5 minutes</strong>.</p>

<p>If you did not request this, please ignore this email.</p>
