<!DOCTYPE html>
<html>
<body style="font-family: Georgia, serif; background: #0a0a1e; color: #fff; padding: 40px;">
    <h2 style="color: #d8b5ff;">✨ Welcome to The Master Magical Key</h2>
    <p>Hi {{ $user->name }},</p>
    <p>Your purchase was successful! An account has been created for you so you can access your products anytime.</p>
    <p><strong>Your login details:</strong></p>
    <p>Email: {{ $user->email }}<br>
    Temporary Password: <strong>{{ $tempPassword }}</strong></p>
    <p><a href="{{ $loginUrl }}" style="color: #d8b5ff;">Click here to log in and access your purchase →</a></p>
    <p style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">
        Please change your password after your first login.
    </p>
    <p>With love,<br>The Master Magical Key ✨🔑</p>
</body>
</html>