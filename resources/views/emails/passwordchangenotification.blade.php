<!DOCTYPE html>
<html>
<head>
<title>Password Change Notification!</title>
</head>
<body>
<p style="text-align: justify">
    Dear <strong>{{$user['name']}}</strong>,<br>
    You are receiving this email because you have not change the password for the <strong>BIL PF/GF</strong>
    of your account associated with <strong>{{$user['email']}}.</strong>
    <p>To change your password,click on <a class="btn btn-primary" href="{{url('/ppfsystem/login')}}"><strong>Change Password.</strong></a>
    Therefore,you are request to change the password, if not you will not able to login.</p>
    <br>
</p>
<p>
    Thank you,<br>
    Bhutan Insurance Limited,<br>
    Providing Security, Building Confidence.
</p>
</body>
</html>