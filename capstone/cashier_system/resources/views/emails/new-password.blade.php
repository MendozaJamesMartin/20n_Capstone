<h2>Hello {{ $user->name ?? 'User' }},</h2>

<p>Your password has been reset. Please use the following temporary password to log in:</p>

<p><strong>{{ $newPassword }}</strong></p>

<p>We recommend changing your password immediately after logging in.</p>

<p>— PUP-TAGUIG Electronic Cashier System</p>