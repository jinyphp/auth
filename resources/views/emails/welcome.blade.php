<!-- resources/views/emails/welcome_email.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome Email</title>
</head>
<body>
    <h1>가입 감사합니다.</h1>
    <p>Hello {{ $user->name }},</p>
    <p>Thank you for signing up on our website. We are excited to have you with us.</p>
    <p>If you have any questions or need assistance, feel free to contact us.</p>
    <p>Best regards,<br>Your Website Team</p>
</body>
</html>
