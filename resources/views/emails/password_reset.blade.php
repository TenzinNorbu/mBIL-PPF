<!DOCTYPE html>
<html>
<head>
    <title>BIL : Password Reset</title>
</head>
<body>
<h3>{{ $title }}</h3>
<p style="text-align: justify">
    Dear User, <br>
    Your OTP (One Time Password) against the email <strong><i>{{ $user_email }}</i></strong> for the [ password reset ] is
    <strong>{{ $otp }}</strong>. Please use the OTP to to change your password.
    <br><br>
</p>

<p>
    Thank you,<br>Bhutan Insurance Limited
</p>
</body>
</html>
